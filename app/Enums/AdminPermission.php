<?php

namespace App\Enums;

/**
 * Permissions that can be granted to roles from the admin portal.
 * System maintenance is deliberately absent: it belongs to the super admin only.
 */
enum AdminPermission: string
{
    case AdminAccess = 'admin.access';
    case NoticesManage = 'notices.manage';
    case PagesManage = 'pages.manage';
    case MenusManage = 'menus.manage';
    case SettingsManage = 'settings.manage';
    case UsersManage = 'users.manage';
    case RolesManage = 'roles.manage';

    public function label(): string
    {
        return match ($this) {
            self::AdminAccess => 'অ্যাডমিন প্যানেলে প্রবেশ ও ড্যাশবোর্ড',
            self::NoticesManage => 'নোটিশ ও নোটিশ ক্যাটাগরি',
            self::PagesManage => 'পেজ ও পেজের সেকশন (HTML সহ)',
            self::MenusManage => 'মেনু ও ফুটার লিংক',
            self::SettingsManage => 'লোগো ও সাইট সেটিংস',
            self::UsersManage => 'ইউজার ব্যবস্থাপনা',
            self::RolesManage => 'রোল ও পারমিশন',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
