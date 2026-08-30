<?php
declare(strict_types=1);

if (!function_exists('role_display_label')) {
    /**
     * Friendly label for a stored role_name (guest/user stay as keys in the DB).
     */
    function role_display_label(string $roleName): string
    {
        $key = strtolower(trim($roleName));
        $t = static function (string $k, string $fallback): string {
            if (!function_exists('__')) {
                return $fallback;
            }
            $v = __($k);
            return ($v !== $k) ? $v : $fallback;
        };
        return match ($key) {
            'guest' => $t('roles.public_visitor', 'Public visitor'),
            'user' => $t('roles.data_entry_user', 'Data entry user'),
            'admin' => $t('roles.administrator', 'Administrator'),
            'moderator' => $t('roles.moderator', 'Moderator'),
            default => ($roleName !== '' ? str_replace('_', ' ', $roleName) : $t('roles.unknown', 'Role')),
        };
    }
}
