<?php

namespace App\Http\Controllers;

use App\Support\DemoData;
use App\Support\OpsData;
use App\Support\Role;
use Illuminate\Http\Request;

class AtlController extends Controller
{
    private function lists(string $title, string $preset, string $mode)
    {
        abort_unless(Role::can('atl.view'), 403);

        if (Role::current() === 'atl_user') {
            // The ATL User's own records page (reads the shared demo store in the browser).
            $tabs = ['Draft' => 'draft', 'Submitted' => 'pending', 'Approved' => 'approved', 'Declined' => 'declined'];

            return view('atl.records', [
                'opsSeed' => OpsData::seed(optional(auth()->user())->name),
                'tab'     => $tabs[$preset] ?? ($mode === 'mine' ? 'draft' : 'all'),
            ]);
        }

        $atls = collect(DemoData::atls());

        return view('atl.index', ['atls' => $atls->values()->all(), 'title' => $title, 'preset' => $preset, 'mode' => $mode]);
    }

    public function index(Request $request)
    {
        return $this->lists('ATL records', $request->query('status', 'All'), 'all');
    }

    public function mine(Request $request)
    {
        return $this->lists('ATL Records', $request->query('status', 'All'), 'mine');
    }

    public function drafts()
    {
        return $this->lists('ATL Records', 'Draft', 'drafts');
    }

    public function returned()
    {
        return $this->lists('ATL Records', 'Declined', 'returned');
    }

    public function approvals()
    {
        abort_unless(Role::can('atl.approve'), 403);

        return view('atl.index', ['atls' => DemoData::atls(), 'title' => 'Approval queue', 'preset' => 'Submitted', 'mode' => 'all']);
    }

    public function create(Request $request)
    {
        abort_unless(Role::can('atl.create'), 403);

        // The create form reads trucks, drivers and haulers from the shared demo store in the browser.
        return view('atl.create', ['opsSeed' => OpsData::seed(optional(auth()->user())->name)]);
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
