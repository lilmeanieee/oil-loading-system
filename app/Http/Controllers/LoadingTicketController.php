<?php

namespace App\Http\Controllers;

use App\Support\DemoData;
use App\Support\Role;
use Illuminate\Http\Request;

class LoadingTicketController extends Controller
{
    public function index()
    {
        abort_unless(Role::can('loading.manage'), 403);

        $atls = collect(DemoData::atls())->filter(fn ($a) => in_array($a['status'], ['Approved', 'Loading', 'Completed']))->values()->all();

        return view('loading.index', ['atls' => $atls]);
    }

    public function create(int $atl)
    {
        abort_unless(Role::can('loading.manage'), 403);

        $atl = DemoData::findAtl($atl) ?? abort(404);
        abort_unless(in_array($atl['status'], ['Approved', 'Loading', 'Completed']), 404);

        return view('loading-tickets.create', [
            'atl'    => $atl,
            'trucks' => DemoData::trucks(),
            'codes'  => collect(DemoData::products())->pluck('ticket', 'atl')->all(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Role::can('loading.manage'), 403);

        // TODO (backend): save the ticket. On "confirm", save the actual liters and set the ATL to Completed.
        $message = $request->input('action') === 'confirm'
            ? 'Actual liters confirmed. (Demo only.)'
            : 'Loading ticket saved. (Demo only.)';

        return redirect()->route('loading.index')->with('status', $message);
    }
}
