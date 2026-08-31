<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

/**
 * File-package updates from GitHub zip snapshots. Does not run SQL.
 */
class ReleaseUpdateService
{
    private string $root;
    private string $storage;

    public function __construct(private PDO $pdo)
    {
        $this->root = defined('ROOT_PATH') ? rtrim((string) ROOT_PATH, '/') : dirname(__DIR__, 2);
        $this->storage = $this->root . '/storage';
    }

    public function repo(): string
    {
        if (defined('PRD_UPDATE_REPO') && is_string(PRD_UPDATE_REPO) && PRD_UPDATE_REPO !== '') {
            return PRD_UPDATE_REPO;
        }
        return 'greenlanegreb/parishrecordsdatabase';
    }

    public function defaultRef(): string
    {
        $saved = $this->setting('update_channel');
        if ($saved !== '') {
            return $saved;
        }
        if (defined('PRD_UPDATE_REF') && is_string(PRD_UPDATE_REF) && PRD_UPDATE_REF !== '') {
            return PRD_UPDATE_REF;
        }
        return 'main';
    }

    public function sanitizeRef(string $ref): string
    {
        $ref = trim($ref);
        if ($ref === '') {
            return 'main';
        }
        if (!preg_match('#^[A-Za-z0-9._/-]{1,80}$#', $ref)) {
            return 'main';
        }
        if (str_contains($ref, '..')) {
            return 'main';
        }
        return $ref;
    }

    /**
     * @return array{sha:string,ref:string,date:string,schema:int}
     */
    public function currentPackage(): array
    {
        $file = $this->storage . '/prd_release.json';
        $sha = $this->setting('package_sha');
        $ref = $this->setting('package_ref');
        $date = $this->setting('package_applied_at');
        if (is_file($file)) {
            $raw = @file_get_contents($file);
            $json = is_string($raw) ? json_decode($raw, true) : null;
            if (is_array($json)) {
                $sha = is_string($json['sha'] ?? null) ? $json['sha'] : $sha;
                $ref = is_string($json['ref'] ?? null) ? $json['ref'] : $ref;
                $date = is_string($json['applied_at'] ?? null) ? $json['applied_at'] : $date;
            }
        }
        $gitSha = $this->localGitSha();
        if ($gitSha !== '') {
            $sha = $gitSha;
        }
        $schema = function_exists('get_schema_version') ? (int) get_schema_version($this->pdo) : 0;
        return [
            'sha' => $sha,
            'ref' => $ref !== '' ? $ref : $this->defaultRef(),
            'date' => $date,
            'schema' => $schema,
        ];
    }

    /**
     * Compare recorded package SHA with GitHub for the current channel.
     * Cached for 15 minutes so Settings does not hit GitHub on every click.
     *
     * @return array{state:string,local:string,remote:string,ref:string}
     */
    public function fileStatus(): array
    {
        $pkg = $this->currentPackage();
        $ref = $this->defaultRef();
        $local = is_string($pkg['sha'] ?? null) ? (string) $pkg['sha'] : '';
        $cached = $this->setting('package_check_cache');
        $now = time();
        if ($cached !== '') {
            $json = json_decode($cached, true);
            if (is_array($json)
                && isset($json['at'], $json['ref'], $json['state'])
                && (string) $json['ref'] === $ref
                && (string) ($json['local'] ?? '') === $local
                && (int) $json['at'] > ($now - 900)
            ) {
                return [
                    'state' => (string) $json['state'],
                    'local' => (string) ($json['local'] ?? $local),
                    'remote' => (string) ($json['remote'] ?? ''),
                    'ref' => $ref,
                ];
            }
        }
        if ($local === '') {
            $out = ['state' => 'unknown', 'local' => '', 'remote' => '', 'ref' => $ref];
            $this->saveSetting('package_check_cache', json_encode($out + ['at' => $now]));
            return $out;
        }
        $look = $this->lookupRemote($ref);
        if (!$look['ok']) {
            $out = ['state' => 'error', 'local' => $local, 'remote' => '', 'ref' => $ref];
            $this->saveSetting('package_check_cache', json_encode($out + ['at' => $now]));
            return $out;
        }
        $remote = $look['sha'];
        $state = hash_equals(strtolower($local), strtolower($remote)) ? 'current' : 'behind';
        $out = ['state' => $state, 'local' => $local, 'remote' => $remote, 'ref' => $ref];
        $this->saveSetting('package_check_cache', json_encode($out + ['at' => $now]));
        return $out;
    }

    /**
     * @return array{ok:bool,sha:string,message:string}
     */
    public function lookupRemote(string $ref): array
    {
        $ref = $this->sanitizeRef($ref);
        $repo = $this->repo();
        $url = 'https://api.github.com/repos/' . $repo . '/commits/' . rawurlencode($ref);
        $body = $this->httpGet($url);
        if ($body === null) {
            return ['ok' => false, 'sha' => '', 'message' => 'Could not reach GitHub to check for a package.'];
        }
        $json = json_decode($body, true);
        if (!is_array($json) || !isset($json['sha']) || !is_string($json['sha'])) {
            return ['ok' => false, 'sha' => '', 'message' => 'GitHub did not return a package for that name.'];
        }
        return ['ok' => true, 'sha' => $json['sha'], 'message' => ''];
    }

    /**
     * Download zip for $ref and extract over the project. SQL is not run.
     *
     * @return array{ok:bool,sha:string,files:int,message:string}
     */
    public function applyPackage(string $ref): array
    {
        if (!class_exists(\ZipArchive::class)) {
            return ['ok' => false, 'sha' => '', 'files' => 0, 'message' => 'This host cannot unpack zip files (PHP ZipArchive missing). Use File Manager instead.'];
        }
        $ref = $this->sanitizeRef($ref);
        $look = $this->lookupRemote($ref);
        if (!$look['ok']) {
            return ['ok' => false, 'sha' => '', 'files' => 0, 'message' => $look['message']];
        }
        $sha = $look['sha'];
        $repo = $this->repo();
        $zipUrl = 'https://github.com/' . $repo . '/archive/' . rawurlencode($sha) . '.zip';
        $tmpZip = $this->storage . '/tmp_prd_update_' . bin2hex(random_bytes(4)) . '.zip';
        if (!is_dir($this->storage) && !@mkdir($this->storage, 0750, true) && !is_dir($this->storage)) {
            return ['ok' => false, 'sha' => $sha, 'files' => 0, 'message' => 'Could not create the storage folder.'];
        }
        $bin = $this->httpGet($zipUrl);
        if ($bin === null || strlen($bin) < 100) {
            return ['ok' => false, 'sha' => $sha, 'files' => 0, 'message' => 'Could not download the file package from GitHub.'];
        }
        if (@file_put_contents($tmpZip, $bin) === false) {
            return ['ok' => false, 'sha' => $sha, 'files' => 0, 'message' => 'Could not save the downloaded package.'];
        }
        $zip = new \ZipArchive();
        if ($zip->open($tmpZip) !== true) {
            @unlink($tmpZip);
            return ['ok' => false, 'sha' => $sha, 'files' => 0, 'message' => 'The downloaded package could not be opened.'];
        }
        $copied = 0;
        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (!is_string($name) || $name === '' || str_ends_with($name, '/')) {
                    continue;
                }
                $rel = $this->stripFirstSegment($name);
                if ($rel === '' || $this->isDenied($rel)) {
                    continue;
                }
                if (str_contains($rel, '..') || str_starts_with($rel, '/')) {
                    continue;
                }
                $dest = $this->root . '/' . $rel;
                $destRealParent = dirname($dest);
                if (!is_dir($destRealParent) && !@mkdir($destRealParent, 0755, true) && !is_dir($destRealParent)) {
                    continue;
                }
                $stream = $zip->getStream($name);
                if ($stream === false) {
                    continue;
                }
                $out = @fopen($dest, 'wb');
                if ($out === false) {
                    fclose($stream);
                    continue;
                }
                stream_copy_to_stream($stream, $out);
                fclose($stream);
                fclose($out);
                $copied++;
            }
        } finally {
            $zip->close();
            @unlink($tmpZip);
        }

        $this->stamp($ref, $sha);
        return [
            'ok' => true,
            'sha' => $sha,
            'files' => $copied,
            'message' => 'File package applied. Database updates are a separate step.',
        ];
    }

    /**
     * SHA of this folder if it is a Git checkout. Empty for FTP/zip-only installs.
     */
    public function localGitSha(): string
    {
        $headFile = $this->root . '/.git/HEAD';
        if (!is_file($headFile)) {
            return '';
        }
        $head = trim((string) @file_get_contents($headFile));
        if ($head === '') {
            return '';
        }
        if (preg_match('#^ref:\s*(.+)$#', $head, $m) === 1) {
            $refPath = $this->root . '/.git/' . str_replace('\\', '/', trim($m[1]));
            if (str_contains($refPath, '..') || !is_file($refPath)) {
                return '';
            }
            $sha = strtolower(trim((string) @file_get_contents($refPath)));
            return preg_match('/^[a-f0-9]{40}$/', $sha) === 1 ? $sha : '';
        }
        $sha = strtolower($head);
        return preg_match('/^[a-f0-9]{40}$/', $sha) === 1 ? $sha : '';
    }

    public function setChannel(string $ref): void
    {
        $this->saveSetting('update_channel', $this->sanitizeRef($ref));
    }

    public function stamp(string $ref, string $sha): void
    {
        $ref = $this->sanitizeRef($ref);
        $sha = preg_replace('/[^a-fA-F0-9]/', '', $sha) ?? '';
        $now = gmdate('Y-m-d H:i:s');
        $payload = [
            'repo' => $this->repo(),
            'ref' => $ref,
            'sha' => $sha,
            'applied_at' => $now,
        ];
        if (!is_dir($this->storage)) {
            @mkdir($this->storage, 0750, true);
        }
        @file_put_contents(
            $this->storage . '/prd_release.json',
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        $this->saveSetting('update_channel', $ref);
        $this->saveSetting('package_ref', $ref);
        $this->saveSetting('package_sha', $sha);
        $this->saveSetting('package_applied_at', $now);
        $this->saveSetting('package_check_cache', '');
    }

    public function backupDir(): string
    {
        return $this->storage . '/backups';
    }

    public function ensureBackupDir(): void
    {
        $dir = $this->backupDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        $deny = $dir . '/.htaccess';
        if (!is_file($deny)) {
            @file_put_contents($deny, "Require all denied\nDeny from all\n");
        }
        $denyRoot = $this->storage . '/.htaccess';
        if (!is_file($denyRoot)) {
            @file_put_contents($denyRoot, "Require all denied\nDeny from all\n");
        }
    }

    public function archiveFilename(string $sha): string
    {
        $short = $sha !== '' ? substr($sha, 0, 7) : 'unknown';
        $short = preg_replace('/[^a-fA-F0-9]/', '', $short) ?: 'unknown';
        return 'prd-backup-' . gmdate('Y-m-d_Hi') . 'Z-' . $short . '.sql';
    }

    /**
     * @return list<string> newest first, basename only
     */
    public function listArchives(): array
    {
        $dir = $this->backupDir();
        if (!is_dir($dir)) {
            return [];
        }
        $files = glob($dir . '/prd-backup-*.sql') ?: [];
        rsort($files, SORT_STRING);
        return array_map('basename', $files);
    }

    public function pruneArchives(int $keep = 2): void
    {
        $dir = $this->backupDir();
        $files = glob($dir . '/prd-backup-*.sql') ?: [];
        rsort($files, SORT_STRING);
        foreach (array_slice($files, $keep) as $old) {
            @unlink($old);
        }
    }

    public function archivePath(string $basename): ?string
    {
        $base = basename($basename);
        if (!preg_match('/^prd-backup-[A-Za-z0-9._-]+\.sql$/', $base)) {
            return null;
        }
        $path = $this->backupDir() . '/' . $base;
        return is_file($path) ? $path : null;
    }

    private function isDenied(string $rel): bool
    {
        $rel = str_replace('\\', '/', $rel);
        $deniedExact = [
            'config.local.php',
            'db/INSTALL_LOCK',
            'db/ALLOW_EMERGENCY_MIGRATE',
            'storage/prd_release.json',
            'storage/gh_instance.json',
        ];
        if (in_array($rel, $deniedExact, true)) {
            return true;
        }
        $deniedPref = ['storage/backups/', 'storage/tmp', 'logs/', '.git/'];
        foreach ($deniedPref as $p) {
            if ($rel === rtrim($p, '/') || str_starts_with($rel, $p)) {
                return true;
            }
        }
        return false;
    }

    private function stripFirstSegment(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        $pos = strpos($name, '/');
        if ($pos === false) {
            return '';
        }
        return substr($name, $pos + 1);
    }

    private function httpGet(string $url): ?string
    {
        $headers = [
            'User-Agent: pRD-updater',
            'Accept: application/vnd.github+json',
        ];
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return null;
            }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTPHEADER => $headers,
            ]);
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (!is_string($body) || $code >= 400) {
                return null;
            }
            return $body;
        }
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => 60,
                'follow_location' => 1,
            ],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        return is_string($body) ? $body : null;
    }

    private function setting(string $key): string
    {
        try {
            $stmt = $this->pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1');
            $stmt->execute([$key]);
            $v = $stmt->fetchColumn();
            return is_string($v) ? $v : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function saveSetting(string $key, string $value): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            $stmt->execute([$key, $value]);
        } catch (\Throwable $e) {
            // site_settings shape can vary; file stamp is enough
        }
    }
}
