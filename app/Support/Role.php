<?php

namespace App\Support;

/**
 * DEMO ONLY. Lets the front-end preview each role's menu, pages and buttons.
 * A Developer always sees exactly one role's interface at a time, chosen with "Viewing as".
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
    ];

    private const MATRIX = [
        'atl_user'   => ['atl.create', 'atl.view', 'trucks.view', 'documents.view'],
        'operations' => ['atl.view', 'atl.approve', 'trucks.manage', 'trucks.view', 'drivers.manage', 'drivers.view', 'haulers.manage', 'haulers.view', 'loading.manage', 'documents.view', 'documents.issue', 'documents.reprint', 'reports.view'],
        'admin'      => ['atl.view', 'trucks.manage', 'trucks.view', 'drivers.manage', 'drivers.view', 'haulers.manage', 'haulers.view', 'products.manage', 'users.manage', 'reports.view', 'audit.view', 'settings.manage'],
        'owner'      => ['atl.view', 'trucks.view', 'documents.view', 'reports.view', 'audit.view'],
    ];

    /** [label, route name, active route pattern, icon] */
    private const MENU = [
        'atl_user' => [
            ['Dashboard', 'dashboard', 'dashboard', 'dashboard'],
            ['Create ATL', 'atl.create', 'atl.create', 'create'],
            ['ATL Records', 'atl.mine', 'atl.mine|atl.drafts|atl.returned', 'records'],
            ['Documents', 'documents.index', 'documents.*', 'document'],
        ],
        'operations' => [
            ['Dashboard', 'dashboard', 'dashboard', 'dashboard'],
            ['ATL Approvals', 'approvals.index', 'approvals.*', 'check'],
            ['Documents & Release', 'documents.index', 'documents.*', 'document'],
            ['Hauler Management', 'haulers.index', 'haulers.*|trucks.*|drivers.*', 'hauler'],
        ],
        'admin' => [
            ['Dashboard', 'dashboard', 'dashboard', 'dashboard'],
            ['User Management', 'users.index', 'users.*', 'users'],
            ['Hauler Management', 'haulers.index', 'haulers.*|trucks.*|drivers.*', 'hauler'],
            ['Audit Trail', 'audit.index', 'audit.*', 'shield'],
            ['Settings', 'settings.index', 'settings.*', 'cog'],
        ],
        'owner' => [
            ['Dashboard', 'dashboard', 'dashboard', 'dashboard'],
            ['ATL Records', 'atl.index', 'atl.index', 'records'],
            ['Documents', 'documents.index', 'documents.*', 'document'],
            ['Truck Management', 'haulers.index', 'haulers.*|trucks.*|drivers.*', 'truck'],
            ['Reports', 'reports.index', 'reports.*', 'chart'],
            ['Audit Trail', 'audit.index', 'audit.*', 'shield'],
        ],
    ];

    /** Interfaces a Developer can switch between. */
    public const VIEWABLE = ['atl_user', 'operations', 'admin', 'owner'];

    /** The signed-in person's own role. */
    public static function actual(): string
    {
        $role = optional(auth()->user())->role ?? session('demo_role', 'operations');

        return array_key_exists($role, self::LABELS) ? $role : 'operations';
    }

    public static function isDeveloper(): bool
    {
        return self::actual() === 'developer';
    }

    /** Real accounts with a fixed role cannot switch. Developers, and demo mode without a role column, can. */
    public static function canSwitch(): bool
    {
        return self::isDeveloper() || empty(optional(auth()->user())->role);
    }

    public static function setView(string $role): void
    {
        if (self::isDeveloper() && in_array($role, self::VIEWABLE, true)) {
            session(['dev_view' => $role]);
        } elseif (empty(optional(auth()->user())->role) && array_key_exists($role, self::LABELS)) {
            session(['demo_role' => $role]);
            session()->forget('dev_view');
        }
    }

    /** The interface being shown. For a Developer this is the role chosen under "Viewing as" (Admin / IT by default). */
    public static function current(): string
    {
        $actual = self::actual();
        if ($actual !== 'developer') {
            return $actual;
        }

        $view = session('dev_view', 'admin');

        return in_array($view, self::VIEWABLE, true) ? $view : 'admin';
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
