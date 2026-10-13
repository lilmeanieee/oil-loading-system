<?php

namespace App\Http\Controllers;

use App\Support\OpsData;
use App\Support\Role;

/** One Hauler Management page (Haulers, Trucks, Drivers tabs) shared by Admin and Operations. */
class HaulerManagementController extends Controller
{
    public function index()
    {
        $tabs = array_values(array_filter(
            ['haulers', 'trucks', 'drivers'],
            fn ($t) => Role::can($t.'.view')
        ));
        abort_if($tabs === [], 403);

        return view('hauler-management.index', [
            'tabs'      => $tabs,
            'canManage' => Role::can('trucks.manage') || Role::can('drivers.manage') || Role::can('haulers.manage'),
            'opsSeed'   => OpsData::seed(optional(auth()->user())->name),
        ]);
    }
}
