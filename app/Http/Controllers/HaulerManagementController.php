<?php

namespace App\Http\Controllers;

use App\Support\DemoData;
use App\Support\Role;
use Illuminate\Http\Request;

/** One Hauler Management page with three tabs: Haulers, Trucks, Drivers. */
class HaulerManagementController extends Controller
{
    public function index(Request $request)
    {
        $tabs = array_values(array_filter(
            ['haulers', 'trucks', 'drivers'],
            fn ($t) => Role::can($t.'.view')
        ));
        abort_if($tabs === [], 403);

        $tab = $request->query('tab');

        return view('hauler-management.index', [
            'tabs'    => $tabs,
            'tab'     => in_array($tab, $tabs, true) ? $tab : $tabs[0],
            'haulers' => DemoData::haulers(),
            'trucks'  => DemoData::trucks(),
            'drivers' => DemoData::drivers(),
        ]);
    }
}
