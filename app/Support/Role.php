<?php

namespace App\Support;
use Illuminate\Support\Facades\Auth;

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
            ['Approvals', 'approvals.index', 'approvals.*', 'check'],
            ['ATL Records', 'atl.index', 'atl.index', 'records'],
            ['Hauler Management', 'haulers.index', 'haulers.*|trucks.*|drivers.*', 'hauler'],
            ['Reports', 'reports.index', 'reports.*', 'chart'],
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

    /** The signed-in person's own role (from users.role). Falls back to the lowest-privilege role. */
    public static function actual(): string
    {
        $role = Auth::user()?->role ?? '';

        return ($role === self::DEVELOPER || array_key_exists($role, self::LABELS)) ? $role : 'atl_user';
    }

    public static function isDeveloper(): bool
    {
        return self::actual() === self::DEVELOPER;
    }

    /** Only Developers can switch the interface they are viewing. */
    public static function canSwitch(): bool
    {
        return self::isDeveloper();
    }

    /** The interface being shown. For a Developer this is the role chosen under "Viewing as". */
    public static function current(): string
    {
        $actual = self::actual();

        if ($actual !== self::DEVELOPER) {
            return $actual;
        }

        $view = session('view_as', 'operations');

        return in_array($view, self::VIEWABLE, true) ? $view : 'operations';
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