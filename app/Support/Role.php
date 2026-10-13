<?php

namespace App\Support;
use Illuminate\Support\Facades\Auth;

/**
 * DEMO ONLY. Lets the front-end preview each role's menus and buttons.
 * The backend developer replaces this with real roles and Laravel policies or gates.
 * If the users table gets a "role" column, current() already reads it.
 */
class Role
{
    public const DEVELOPER = 'developer';
    public const LABELS = [
        'atl_user'   => 'ATL User',
        'operations' => 'Operations',
        'admin'      => 'Admin / IT',
        'owner'      => 'Owner',
        'developer'  => 'Developer',
    ];

    private const PORTAL = [
        'atl_user'   => '',
        'operations' => 'OPERATIONS PORTAL',
        'admin'      => 'ADMIN PORTAL',
        'owner'      => 'MANAGEMENT PORTAL',
    ];

    private const MATRIX = [
        'atl_user'   => ['atl.create', 'atl.view', 'trucks.view'],
        'operations' => ['atl.view', 'atl.approve', 'trucks.manage', 'trucks.view', 'loading.manage', 'documents.view', 'documents.issue', 'documents.reprint', 'reports.view'],
        'admin'      => ['trucks.manage', 'trucks.view', 'reports.view', 'audit.view', 'settings.manage'],
        'owner'      => ['atl.view', 'trucks.view', 'documents.view', 'documents.menu', 'reports.view', 'audit.view'],
    ];

    public static function current(): string
    {
        $role = optional(auth()->user())->role ?? session('demo_role', 'operations');

        return array_key_exists($role, self::LABELS) ? $role : 'operations';
    }

    public static function can(string $permission): bool
    {
        return in_array($permission, self::MATRIX[self::current()], true);
    }
}
