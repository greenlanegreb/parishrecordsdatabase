<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

class AppearanceService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function fontKeys(): array
    {
        return ['bootstrap', 'sans', 'system', 'verdana', 'georgia', 'serif', 'trebuchet'];
    }

    public function fontStack(string $key): string
    {
        return match ($key) {
            'sans' => 'Calibri, Segoe UI, Helvetica Neue, Arial, sans-serif',
            'system' => 'system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif',
            'verdana' => 'Verdana, Geneva, sans-serif',
            'georgia' => 'Georgia, Times New Roman, serif',
            'serif' => 'Palatino, Palatino Linotype, Times New Roman, serif',
            'trebuchet' => 'Trebuchet MS, Lucida Grande, sans-serif',
            default => 'system-ui, -apple-system, Segoe UI, Roboto, Helvetica Neue, Noto Sans, Liberation Sans, Arial, sans-serif',
        };
    }

    public function defaults(): array
    {
        return [
            'colors' => [
                'page_bg' => '#f8f9fa',
                'text' => '#212529',
                'link' => '#0d6efd',
                'header_bg' => '#ffffff',
                'header_text' => '#212529',
                'button' => '#0d6efd',
                'button_text' => '#ffffff',
                'quiet_button' => '#6c757d',
                'border' => '#dee2e6',
                'success' => '#198754',
                'warning' => '#ffc107',
                'danger' => '#dc3545',
                'footer_bg' => '#212529',
                'footer_text' => '#f8f9fa',
                'footer_link' => '#9ec5fe',
            ],
            'font' => 'bootstrap',
            'type_scale' => 'md',
            'letter_spacing' => 'default',
            'strapline' => '',
            'logo_file' => '',
            'logo_height' => 'regular',
            'logo_valign' => 'center',
            'nav_order' => ['search', 'volunteer', 'feedback', 'leaderboard', 'data_entry', 'similar', 'moderation', 'admin'],
            'nav_custom' => [],
            'footer_custom' => [],
        ];
    }

    public function load(): array
    {
        $d = $this->defaults();
        $d['colors'] = array_merge($d['colors'], $this->json('appearance_colors', []));
        $font = $this->val('appearance_font', $d['font']);
        $d['font'] = in_array($font, $this->fontKeys(), true) ? $font : 'bootstrap';
        $scale = $this->val('appearance_type_scale', $d['type_scale']);
        $d['type_scale'] = in_array($scale, ['sm', 'md', 'lg'], true) ? $scale : 'md';
        $space = $this->val('appearance_letter_spacing', $d['letter_spacing']);
        $d['letter_spacing'] = in_array($space, ['default', 'roomy', 'roomier'], true) ? $space : 'default';
        $d['strapline'] = $this->val('appearance_strapline', '');
        $d['logo_file'] = $this->val('appearance_logo', '');
        $lh = $this->val('appearance_logo_height', $d['logo_height']);
        $d['logo_height'] = in_array($lh, ['compact', 'regular', 'tall'], true) ? $lh : 'regular';
        $lv = $this->val('appearance_logo_valign', $d['logo_valign']);
        $d['logo_valign'] = in_array($lv, ['top', 'center', 'bottom'], true) ? $lv : 'center';
        $order = $this->json('appearance_nav_order', $d['nav_order']);
        $d['nav_order'] = $this->sanitizeOrder(is_array($order) ? $order : $d['nav_order']);
        $custom = $this->json('appearance_nav_custom', []);
        $d['nav_custom'] = $this->sanitizeCustom(is_array($custom) ? $custom : []);
        $footerCustom = $this->json('appearance_footer_custom', []);
        $d['footer_custom'] = $this->sanitizeCustom(is_array($footerCustom) ? $footerCustom : []);
        return $d;
    }

    public function save(array $post, ?array $file): array
    {
        $d = $this->defaults();
        $colors = [];
        foreach ($d['colors'] as $key => $fallback) {
            $raw = isset($post['color'][$key]) && is_string($post['color'][$key]) ? trim($post['color'][$key]) : $fallback;
            $colors[$key] = preg_match('/^#[0-9A-Fa-f]{6}$/', $raw) === 1 ? $raw : $fallback;
        }
        $this->put('appearance_colors', json_encode($colors));
        $font = isset($post['font']) && is_string($post['font']) ? $post['font'] : 'system';
        $this->put('appearance_font', in_array($font, $this->fontKeys(), true) ? $font : 'bootstrap');
        $scale = isset($post['type_scale']) && is_string($post['type_scale']) ? $post['type_scale'] : 'md';
        $this->put('appearance_type_scale', in_array($scale, ['sm', 'md', 'lg'], true) ? $scale : 'md');
        $space = isset($post['letter_spacing']) && is_string($post['letter_spacing']) ? $post['letter_spacing'] : 'default';
        $this->put('appearance_letter_spacing', in_array($space, ['default', 'roomy', 'roomier'], true) ? $space : 'default');
        $strap = isset($post['strapline']) && is_string($post['strapline']) ? trim($post['strapline']) : '';
        if (function_exists('mb_substr')) {
            $strap = mb_substr($strap, 0, 120);
        } else {
            $strap = substr($strap, 0, 120);
        }
        $this->put('appearance_strapline', $strap);
        $lh = isset($post['logo_height']) && is_string($post['logo_height']) ? $post['logo_height'] : 'regular';
        $this->put('appearance_logo_height', in_array($lh, ['compact', 'regular', 'tall'], true) ? $lh : 'regular');
        $lv = isset($post['logo_valign']) && is_string($post['logo_valign']) ? $post['logo_valign'] : 'center';
        $this->put('appearance_logo_valign', in_array($lv, ['top', 'center', 'bottom'], true) ? $lv : 'center');

        if (!empty($post['remove_logo'])) {
            $this->deleteLogo();
            $this->put('appearance_logo', '');
        } elseif (is_array($file) && isset($file['tmp_name']) && is_uploaded_file((string) $file['tmp_name'])) {
            $stored = $this->storeLogo($file);
            if ($stored !== '') {
                $this->put('appearance_logo', $stored);
            }
        }

        $orderIn = $d['nav_order'];
        if (isset($post['nav_pos']) && is_array($post['nav_pos'])) {
            $pairs = [];
            foreach ($post['nav_pos'] as $key => $pos) {
                if (!is_string($key) && !is_int($key)) {
                    continue;
                }
                $pairs[(string) $key] = (int) $pos;
            }
            asort($pairs, SORT_NUMERIC);
            $orderIn = array_keys($pairs);
        } elseif (isset($post['nav_order']) && is_array($post['nav_order'])) {
            $orderIn = $post['nav_order'];
        }
        $cleanOrder = [];
        foreach ($orderIn as $key) {
            if (!is_string($key)) {
                continue;
            }
            $key = trim($key);
            if ($key === '' || in_array($key, $cleanOrder, true)) {
                continue;
            }
            $cleanOrder[] = $key;
        }
        foreach ($d['nav_order'] as $key) {
            if (!in_array($key, $cleanOrder, true)) {
                $cleanOrder[] = $key;
            }
        }
        $this->put('appearance_nav_order', json_encode($this->sanitizeOrder($cleanOrder)));

        $custom = [];
        $labels = isset($post['custom_label']) && is_array($post['custom_label']) ? $post['custom_label'] : [];
        $urls = isset($post['custom_url']) && is_array($post['custom_url']) ? $post['custom_url'] : [];
        $who = isset($post['custom_who']) && is_array($post['custom_who']) ? $post['custom_who'] : [];
        $blank = isset($post['custom_blank']) && is_array($post['custom_blank']) ? $post['custom_blank'] : [];
        $ids = isset($post['custom_id']) && is_array($post['custom_id']) ? $post['custom_id'] : [];
        $parents = isset($post['custom_parent']) && is_array($post['custom_parent']) ? $post['custom_parent'] : [];
        foreach ($labels as $i => $label) {
            if (!is_string($label) || trim($label) === '') {
                continue;
            }
            $url = isset($urls[$i]) && is_string($urls[$i]) ? trim($urls[$i]) : '';
            $safe = $this->safeUrl($url);
            if ($safe === '' && $url !== '#') {
                continue;
            }
            if ($url === '#') {
                $safe = '#';
            }
            $vis = isset($who[$i]) && is_string($who[$i]) ? $who[$i] : 'everyone';
            if ($vis !== 'everyone' && $vis !== 'logged_in' && !preg_match('/^role:\d+$/', $vis)) {
                $vis = 'everyone';
            }
            $cid = isset($ids[$i]) && is_string($ids[$i]) && preg_match('/^c[a-z0-9]+$/', $ids[$i]) ? $ids[$i] : ('c' . ($i + 1));
            $parent = isset($parents[$i]) && is_string($parents[$i]) ? $parents[$i] : '';
            if ($parent === $cid) {
                $parent = '';
            }
            $custom[] = [
                'id' => $cid,
                'label' => function_exists('mb_substr') ? mb_substr(trim($label), 0, 60) : substr(trim($label), 0, 60),
                'url' => $safe,
                'who' => $vis,
                'blank' => !empty($blank[$i]),
                'parent' => preg_match('/^c[a-z0-9]+$/', $parent) === 1 ? $parent : '',
            ];
        }
        $this->put('appearance_nav_custom', json_encode($custom));
        $this->put('appearance_footer_custom', json_encode($this->parseCustomFromPost($post, 'footer_')));
        return $this->load();
    }

    public function reset(): void
    {
        $this->deleteLogo();
        foreach ([
            'appearance_colors', 'appearance_font', 'appearance_type_scale', 'appearance_letter_spacing',
            'appearance_strapline', 'appearance_logo', 'appearance_logo_height', 'appearance_logo_valign',
            'appearance_nav_order', 'appearance_nav_custom', 'appearance_footer_custom',
        ] as $k) {
            $this->put($k, '');
        }
    }

    public function cssVariables(): string
    {
        $a = $this->load();
        $c = $a['colors'];
        $font = $this->fontStack((string) ($a['font'] ?? 'bootstrap'));
        $scale = match ($a['type_scale']) {
            'sm' => '15px',
            'lg' => '18px',
            default => '16px',
        };
        $lines = [
            '--prd-page-bg:' . $c['page_bg'],
            '--prd-text:' . $c['text'],
            '--prd-link:' . $c['link'],
            '--prd-header-bg:' . $c['header_bg'],
            '--prd-header-text:' . $c['header_text'],
            '--prd-button:' . $c['button'],
            '--prd-button-text:' . $c['button_text'],
            '--prd-quiet:' . $c['quiet_button'],
            '--prd-border:' . $c['border'],
            '--prd-success:' . $c['success'],
            '--prd-warning:' . $c['warning'],
            '--prd-danger:' . $c['danger'],
            '--prd-footer-bg:' . ($c['footer_bg'] ?? '#212529'),
            '--prd-footer-text:' . ($c['footer_text'] ?? '#f8f9fa'),
            '--prd-footer-link:' . ($c['footer_link'] ?? '#9ec5fe'),
            '--prd-font:' . $font,
            '--prd-font-size:' . $scale,
            '--prd-letter-spacing:' . match ($a['letter_spacing'] ?? 'default') {
                'roomy' => '0.06em',
                'roomier' => '0.12em',
                default => 'normal',
            },
        ];
        return implode(';', $lines);
    }

    public function logoPath(): ?string
    {
        $name = $this->val('appearance_logo', '');
        if ($name === '' || !preg_match('/^logo\.(png|jpe?g|svg|webp)$/i', $name)) {
            return null;
        }
        $path = $this->brandingDir() . '/' . $name;
        return is_file($path) ? $path : null;
    }

    public function logoMime(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }

    /**
     * @param array<string, bool> $flags visibility computed in nav.php
     * @return list<array{key:string,label:string,href:string,active:bool,blank?:bool,kind:string}>
     */
    public function visibleNavItems(array $flags, string $baseUrl, string $currentRoute): array
    {
        $a = $this->load();
        $built = [];
        $add = static function (string $key, bool $show, string $label, string $href, bool $active) use (&$built): void {
            if ($show) {
                $built[$key] = ['key' => $key, 'label' => $label, 'href' => $href, 'active' => $active, 'kind' => 'core'];
            }
        };
        $add('search', !empty($flags['search']), __('nav.search'), $baseUrl . '/', ($currentRoute === '/' || $currentRoute === '/index.php'));
        $add('volunteer', !empty($flags['volunteer']), __('nav.volunteer'), $baseUrl . '/volunteer', $currentRoute === '/volunteer');
        $add('feedback', !empty($flags['feedback']), __('nav.feedback'), $baseUrl . '/feedback', $currentRoute === '/feedback');
        $add('leaderboard', !empty($flags['leaderboard']), __('nav.leaderboard'), $baseUrl . '/leaderboard', $currentRoute === '/leaderboard');
        $add('data_entry', !empty($flags['data_entry']), __('nav.data_entry'), $baseUrl . '/data-entry', $currentRoute === '/data-entry');
        $add('similar', !empty($flags['similar']), (__('nav.similar_records') !== 'nav.similar_records' ? __('nav.similar_records') : 'Similar records'), $baseUrl . '/admin/duplicates', str_starts_with($currentRoute, '/admin/duplicates'));
        $add('moderation', !empty($flags['moderation']), __('nav.moderation'), $baseUrl . '/admin/moderation', str_starts_with($currentRoute, '/admin/moderation'));
        $add('admin', !empty($flags['admin']), __('nav.admin'), '#', str_starts_with($currentRoute, '/admin') && !str_starts_with($currentRoute, '/admin/moderation') && !str_starts_with($currentRoute, '/admin/gh-feedback'));

        foreach ($a['nav_custom'] as $c) {
            $who = $c['who'] ?? 'everyone';
            $ok = $this->whoMaySee($who, $flags);
            if (!$ok) {
                continue;
            }
            $id = 'custom:' . (string) ($c['id'] ?? $c['label']);
            $built[$id] = [
                'key' => $id,
                'label' => (string) $c['label'],
                'href' => (string) $c['url'],
                'active' => false,
                'blank' => !empty($c['blank']),
                'kind' => 'custom',
                'parent' => (string) ($c['parent'] ?? ''),
                'children' => [],
            ];
        }
        foreach ($built as $id => $item) {
            $parent = $item['parent'] ?? '';
            if ($parent !== '' && isset($built['custom:' . $parent])) {
                $built['custom:' . $parent]['children'][] = $item;
                $built['custom:' . $parent]['kind'] = 'menu';
                unset($built[$id]);
            } elseif ($parent !== '' && isset($built[$parent])) {
                $built[$parent]['children'][] = $item;
                $built[$parent]['kind'] = 'menu';
                unset($built[$id]);
            }
        }

        $out = [];
        foreach ($a['nav_order'] as $key) {
            if (isset($built[$key])) {
                $out[] = $built[$key];
                unset($built[$key]);
            }
        }
        foreach ($built as $item) {
            $out[] = $item;
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $flags
     * @return list<array{label:string,href:string,blank:bool,kind:string,children?:list}>
     */
    public function visibleFooterLinks(array $flags): array
    {
        $a = $this->load();
        $built = [];
        foreach ($a['footer_custom'] as $c) {
            if (!$this->whoMaySee((string) ($c['who'] ?? 'everyone'), $flags)) {
                continue;
            }
            $id = (string) ($c['id'] ?? '');
            $built[$id] = [
                'label' => (string) $c['label'],
                'href' => (string) $c['url'],
                'blank' => !empty($c['blank']),
                'kind' => 'custom',
                'parent' => (string) ($c['parent'] ?? ''),
                'children' => [],
            ];
        }
        foreach ($built as $id => $item) {
            $parent = $item['parent'] ?? '';
            if ($parent !== '' && isset($built[$parent]) && $parent !== $id) {
                $built[$parent]['children'][] = $item;
                $built[$parent]['kind'] = 'menu';
            }
        }
        $out = [];
        foreach ($built as $id => $item) {
            $parent = $item['parent'] ?? '';
            if ($parent === '' || !isset($built[$parent]) || $parent === $id) {
                $out[] = $item;
            }
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $post
     * @return list<array<string, mixed>>
     */
    private function parseCustomFromPost(array $post, string $prefix): array
    {
        $labels = isset($post[$prefix . 'label']) && is_array($post[$prefix . 'label']) ? $post[$prefix . 'label'] : [];
        $urls = isset($post[$prefix . 'url']) && is_array($post[$prefix . 'url']) ? $post[$prefix . 'url'] : [];
        $who = isset($post[$prefix . 'who']) && is_array($post[$prefix . 'who']) ? $post[$prefix . 'who'] : [];
        $blank = isset($post[$prefix . 'blank']) && is_array($post[$prefix . 'blank']) ? $post[$prefix . 'blank'] : [];
        $ids = isset($post[$prefix . 'id']) && is_array($post[$prefix . 'id']) ? $post[$prefix . 'id'] : [];
        $parents = isset($post[$prefix . 'parent']) && is_array($post[$prefix . 'parent']) ? $post[$prefix . 'parent'] : [];
        $custom = [];
        foreach ($labels as $i => $label) {
            if (!is_string($label) || trim($label) === '') {
                continue;
            }
            $url = isset($urls[$i]) && is_string($urls[$i]) ? trim($urls[$i]) : '';
            $safe = $this->safeUrl($url);
            if ($safe === '' && $url !== '#') {
                continue;
            }
            if ($url === '#') {
                $safe = '#';
            }
            $vis = isset($who[$i]) && is_string($who[$i]) ? $who[$i] : 'everyone';
            if ($vis !== 'everyone' && $vis !== 'logged_in' && !preg_match('/^role:\d+$/', $vis)) {
                $vis = 'everyone';
            }
            $cid = isset($ids[$i]) && is_string($ids[$i]) && preg_match('/^c[a-z0-9]+$/', $ids[$i]) ? $ids[$i] : ('c' . ($i + 1));
            $parent = isset($parents[$i]) && is_string($parents[$i]) ? $parents[$i] : '';
            if ($parent === $cid) {
                $parent = '';
            }
            $custom[] = [
                'id' => $cid,
                'label' => function_exists('mb_substr') ? mb_substr(trim($label), 0, 60) : substr(trim($label), 0, 60),
                'url' => $safe,
                'who' => $vis,
                'blank' => !empty($blank[$i]),
                'parent' => preg_match('/^c[a-z0-9]+$/', $parent) === 1 ? $parent : '',
            ];
        }
        return $custom;
    }

    public function catalogKeys(): array
    {
        return $this->defaults()['nav_order'];
    }

    public function logoMaxPx(): int
    {
        return match ($this->load()['logo_height'] ?? 'regular') {
            'compact' => 40,
            'tall' => 72,
            default => 56,
        };
    }

    public function logoAlignItems(): string
    {
        return match ($this->load()['logo_valign'] ?? 'center') {
            'top' => 'flex-start',
            'bottom' => 'flex-end',
            default => 'center',
        };
    }

    public function logoUrl(string $baseUrl): string
    {
        $path = $this->logoPath();
        $v = $path ? (string) filemtime($path) : (string) time();
        return rtrim($baseUrl, '/') . '/branding/logo?v=' . rawurlencode($v);
    }

    /** @return list<array{id:int,role_name:string}> */
    public function roles(): array
    {
        try {
            $stmt = $this->pdo->query('SELECT id, role_name FROM roles ORDER BY id ASC');
            return $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @param array<string, mixed> $flags
     */
    private function whoMaySee(string $who, array $flags): bool
    {
        if ($who === 'everyone') {
            return true;
        }
        if ($who === 'logged_in') {
            return !empty($flags['logged_in']);
        }
        if ($who === 'admin') {
            return !empty($flags['admin_user']);
        }
        if (preg_match('/^role:(\d+)$/', $who, $m) === 1) {
            $ids = $flags['role_ids'] ?? [];
            return is_array($ids) && in_array((int) $m[1], array_map('intval', $ids), true);
        }
        return false;
    }

    private function brandingDir(): string
    {
        $root = defined('ROOT_PATH') ? rtrim((string) ROOT_PATH, '/') : dirname(__DIR__, 2);
        return $root . '/storage/branding';
    }

    private function storeLogo(array $file): string
    {
        $tmp = (string) $file['tmp_name'];
        $size = isset($file['size']) ? (int) $file['size'] : 0;
        if ($size < 1 || $size > 8_000_000) {
            return '';
        }
        $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
        $mime = $finfo ? (string) finfo_file($finfo, $tmp) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        $map = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/svg+xml' => 'svg',
            'image/webp' => 'webp',
        ];
        if (!isset($map[$mime])) {
            return '';
        }
        $dir = $this->brandingDir();
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            return '';
        }
        $this->deleteLogo();
        $name = 'logo.' . $map[$mime];
        if (!@move_uploaded_file($tmp, $dir . '/' . $name)) {
            return '';
        }
        @chmod($dir . '/' . $name, 0640);
        return $name;
    }

    private function deleteLogo(): void
    {
        $dir = $this->brandingDir();
        foreach (glob($dir . '/logo.*') ?: [] as $f) {
            @unlink($f);
        }
    }

    private function sanitizeOrder(array $order): array
    {
        $allowed = $this->defaults()['nav_order'];
        $out = [];
        foreach ($order as $key) {
            if (!is_string($key)) {
                continue;
            }
            if (str_starts_with($key, 'custom:')) {
                $out[] = $key;
                continue;
            }
            if (in_array($key, $allowed, true) && !in_array($key, $out, true)) {
                $out[] = $key;
            }
        }
        foreach ($allowed as $key) {
            if (!in_array($key, $out, true)) {
                $out[] = $key;
            }
        }
        return $out;
    }

    private function sanitizeCustom(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $label = isset($row['label']) && is_string($row['label']) ? trim($row['label']) : '';
            $url = isset($row['url']) && is_string($row['url']) ? $this->safeUrl($row['url']) : '';
            if ($label === '' || $url === '') {
                continue;
            }
            $out[] = [
                'id' => isset($row['id']) && is_string($row['id']) ? preg_replace('/[^a-z0-9]/', '', $row['id']) : ('c' . (count($out) + 1)),
                'label' => $label,
                'url' => $url,
                'who' => (function () use ($row) {
                    $w = isset($row['who']) && is_string($row['who']) ? $row['who'] : 'everyone';
                    if ($w === 'everyone' || $w === 'logged_in' || $w === 'admin' || preg_match('/^role:\d+$/', $w)) {
                        return $w;
                    }
                    return 'everyone';
                })(),
                'blank' => !empty($row['blank']),
                'parent' => isset($row['parent']) && is_string($row['parent']) && preg_match('/^c[a-z0-9]+$/', $row['parent']) ? $row['parent'] : '',
            ];
        }
        return $out;
    }

    private function safeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (str_starts_with($url, '/')) {
            return $url;
        }
        $parts = parse_url($url);
        if (!is_array($parts) || !isset($parts['scheme'])) {
            return '';
        }
        $scheme = strtolower((string) $parts['scheme']);
        if (!in_array($scheme, ['http', 'https', 'mailto'], true)) {
            return '';
        }
        return $url;
    }

    private function val(string $key, string $default): string
    {
        try {
            $stmt = $this->pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1');
            $stmt->execute([$key]);
            $v = $stmt->fetchColumn();
            return is_string($v) && $v !== '' ? $v : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    private function json(string $key, $default)
    {
        $raw = $this->val($key, '');
        if ($raw === '') {
            return $default;
        }
        $j = json_decode($raw, true);
        return is_array($j) || $j !== null ? $j : $default;
    }

    private function put(string $key, string $value): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            $stmt->execute([$key, $value]);
        } catch (\Throwable $e) {
        }
    }
}
