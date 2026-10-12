<?php

namespace App\Support;

/**
 * DEMO ONLY. Lets the front-end preview each role's menu, pages and buttons.
 * The backend developer replaces this with real roles and Laravel policies or gates.
 * If the users table gets a "role" column, current() already reads it.
 */
class Role
{
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
        'developer'  => 'DEVELOPER PREVIEW',
    ];

    private const MATRIX = [
        'atl_user'   => ['atl.create', 'atl.view', 'trucks.view', 'documents.view'],
        'operations' => ['atl.view', 'atl.approve', 'trucks.manage', 'trucks.view', 'drivers.manage', 'drivers.view', 'haulers.manage', 'haulers.view', 'loading.manage', 'documents.view', 'documents.issue', 'documents.reprint', 'reports.view'],
        'admin'      => ['atl.view', 'trucks.manage', 'trucks.view', 'drivers.manage', 'drivers.view', 'haulers.manage', 'haulers.view', 'products.manage', 'users.manage', 'reports.view', 'audit.view', 'settings.manage'],
        'owner'      => ['atl.view', 'trucks.view', 'documents.view', 'reports.view', 'audit.view'],
        'developer' => [
            'atl.create',
            'atl.view',
            'atl.approve',
            'trucks.manage',
            'trucks.view',
            'drivers.manage',
            'drivers.view',
            'haulers.manage',
            'haulers.view',
            'loading.manage',
            'documents.view',
            'documents.issue',
            'documents.reprint',
            'products.manage',
            'users.manage',
            'reports.view',
            'audit.view',
            'settings.manage',
        ],
    ];

    /** [label, route name, active route pattern, icon] */
    private const MENU = [
        'atl_user' => [
            ['Dashboard', 'dashboard', 'dashboard', 'dashboard'],
            ['Create ATL', 'atl.create', 'atl.create', 'create'],
            ['My ATL Records', 'atl.mine', 'atl.mine', 'records'],
            ['Drafts', 'atl.drafts', 'atl.drafts', 'drafts'],
            ['Returned ATLs', 'atl.returned', 'atl.returned', 'returned'],
            ['Documents', 'documents.index', 'documents.*', 'document'],
            ['My Profile', 'account.show', 'account.*', 'user'],
        ],
        'operations' => [
            ['Dashboard', 'dashboard', 'dashboard', 'dashboard'],
            ['Approvals', 'approvals.index', 'approvals.*', 'check'],
            ['ATL Records', 'atl.index', 'atl.index', 'records'],
            ['Truck Management', 'trucks.index', 'trucks.*', 'truck'],
            ['Driver Management', 'drivers.index', 'drivers.*', 'driver'],
            ['Hauler Management', 'haulers.index', 'haulers.*', 'hauler'],
            ['Reports', 'reports.index', 'reports.*', 'chart'],
        ],
        'admin' => [
            ['Dashboard', 'dashboard', 'dashboard', 'dashboard'],
            ['User Management', 'users.index', 'users.*', 'users'],
            ['Truck Management', 'trucks.index', 'trucks.*', 'truck'],
            ['Driver Management', 'drivers.index', 'drivers.*', 'driver'],
            ['Hauler Management', 'haulers.index', 'haulers.*', 'hauler'],
            ['Product Master', 'products.index', 'products.*', 'product'],
            ['ATL Records', 'atl.index', 'atl.index', 'records'],
            ['Reports', 'reports.index', 'reports.*', 'chart'],
            ['Audit Trail', 'audit.index', 'audit.*', 'shield'],
            ['Settings', 'settings.index', 'settings.*', 'cog'],
        ],
        'owner' => [
            ['Dashboard', 'dashboard', 'dashboard', 'dashboard'],
            ['ATL Records', 'atl.index', 'atl.index', 'records'],
            ['Documents', 'documents.index', 'documents.*', 'document'],
            ['Truck Management', 'trucks.index', 'trucks.*', 'truck'],
            ['Reports', 'reports.index', 'reports.*', 'chart'],
            ['Audit Trail', 'audit.index', 'audit.*', 'shield'],
        ],
        'developer' => [
            ['Dashboard', 'dashboard', 'dashboard', 'dashboard'],
            ['Create ATL', 'atl.create', 'atl.create', 'create'],
            ['My ATL Records', 'atl.mine', 'atl.mine', 'records'],
            ['ATL Records', 'atl.index', 'atl.index', 'records'],
            ['Drafts', 'atl.drafts', 'atl.drafts', 'drafts'],
            ['Returned ATLs', 'atl.returned', 'atl.returned', 'returned'],
            ['Approvals', 'approvals.index', 'approvals.*', 'check'],
            ['Loading', 'loading.index', 'loading.*', 'truck'],
            ['Documents', 'documents.index', 'documents.*', 'document'],
            ['Truck Management', 'trucks.index', 'trucks.*', 'truck'],
            ['Driver Management', 'drivers.index', 'drivers.*', 'driver'],
            ['Hauler Management', 'haulers.index', 'haulers.*', 'hauler'],
            ['Product Master', 'products.index', 'products.*', 'product'],
            ['User Management', 'users.index', 'users.*', 'users'],
            ['Reports', 'reports.index', 'reports.*', 'chart'],
            ['Audit Trail', 'audit.index', 'audit.*', 'shield'],
            ['Settings', 'settings.index', 'settings.*', 'cog'],
            ['My Profile', 'account.show', 'account.*', 'user'],
        ],
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

    public static function menu(): array
    {
        return self::MENU[self::current()];
    }

    public static function portal(): string
    {
        return self::PORTAL[self::current()];
    }
}
