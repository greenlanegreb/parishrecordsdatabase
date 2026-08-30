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
            'guest' => $t('role.label_guest', 'Public Visitor'),
            'user' => $t('role.label_user', 'Data Entry User'),
            'admin' => $t('role.label_admin', 'Administrator'),
            'moderator' => $t('role.label_moderator', 'Moderator'),
            default => ($roleName !== '' ? str_replace('_', ' ', $roleName) : $t('roles.unknown', 'Role')),
        };
    }
}
