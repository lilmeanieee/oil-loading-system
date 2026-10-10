<?php

namespace App\Http\Controllers;

use App\Support\DemoData;
use App\Support\Role;
use Illuminate\Http\Request;

class AtlController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Role::can('atl.view'), 403);

        return view('atl.index', ['atls' => DemoData::atls(), 'title' => 'ATL records', 'preset' => $request->query('status', 'All')]);
    }

    public function approvals()
    {
        abort_unless(Role::can('atl.approve'), 403);

        return view('atl.index', ['atls' => DemoData::atls(), 'title' => 'Approval queue', 'preset' => 'Submitted']);
    }

    public function create(Request $request)
    {
        abort_unless(Role::can('atl.create'), 403);

        $prefill = $request->query('edit') ? DemoData::findAtl((int) $request->query('edit')) : null;

        return view('atl.create', [
            'trucks'   => DemoData::trucks(),
            'products' => collect(DemoData::products())->pluck('atl')->all(),
            'prefill'  => $prefill,
            'taken'    => DemoData::taken($prefill['id'] ?? null),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Role::can('atl.create'), 403);

        // TODO (backend): validate, check that the SO number is unused, generate the ATL and control numbers, save.
        $message = $request->input('action') === 'submit'
            ? 'ATL submitted for approval. (Demo only: nothing is stored yet.)'
            : 'Draft saved. (Demo only: nothing is stored yet.)';

        return redirect()->route('dashboard')->with('status', $message);
    }

    public function show(int $id)
    {
        abort_unless(Role::can('atl.view'), 403);

        return view('atl.show', ['atl' => DemoData::findAtl($id) ?? abort(404)]);
    }

    public function approve(int $id)
    {
        abort_unless(Role::can('atl.approve'), 403);

        // TODO (backend): record approver and time, change status to Approved.
        return redirect()->route('atl.show', $id)->with('status', 'ATL approved. (Demo only.)');
    }

    public function decline(Request $request, int $id)
    {
        abort_unless(Role::can('atl.approve'), 403);

        $request->validate(['reason' => 'required|string|min:5']);

        // TODO (backend): save the reason, change status to Declined, let the creator correct and resubmit.
        return redirect()->route('atl.show', $id)->with('status', 'ATL declined with a reason. (Demo only.)');
    }
}
