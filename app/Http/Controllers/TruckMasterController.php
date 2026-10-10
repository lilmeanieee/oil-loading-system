<?php

namespace App\Http\Controllers;

use App\Support\DemoData;
use App\Support\Role;

class TruckMasterController extends Controller
{
    public function index()
    {
        abort_unless(Role::can('trucks.view'), 403);

        return view('trucks.index', ['trucks' => DemoData::trucks()]);
    }
}
