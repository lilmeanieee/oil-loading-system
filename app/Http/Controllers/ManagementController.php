<?php

namespace App\Http\Controllers;

use App\Support\DemoData;
use App\Support\Role;
use Illuminate\Http\Request;

/** Admin / IT screens. Every action only redirects for now (demo). */
class ManagementController extends Controller
{
    private function allow(string $permission): void
    {
        abort_unless(Role::can($permission), 403);
    }

    public function users()
    {
        $this->allow('users.manage');

        return view('admin.users', ['users' => DemoData::users(), 'registrations' => DemoData::registrations()]);
    }

    public function approveUser(Request $request, int $id)
    {
        $this->allow('users.manage');
        $request->validate(['role' => 'required|in:atl_user,operations,admin,owner']);

        // TODO (backend): activate the account with the chosen role and email the user.
        return back()->with('status', 'Account approved and activated. (Demo only.)');
    }

    public function rejectUser(Request $request, int $id)
    {
        $this->allow('users.manage');
        $request->validate(['reason' => 'required|string|min:5']);

        // TODO (backend): reject with the reason and email the user.
        return back()->with('status', 'Registration rejected with a reason. (Demo only.)');
    }

    public function toggleUser(int $id)
    {
        $this->allow('users.manage');

        return back()->with('status', 'Account status changed. (Demo only.)');
    }

    public function changeRole(Request $request, int $id)
    {
        $this->allow('users.manage');
        $request->validate(['role' => 'required|in:atl_user,operations,admin,owner']);

        return back()->with('status', 'Role changed. (Demo only.)');
    }

    public function drivers()
    {
        $this->allow('drivers.view');

        return view('admin.drivers', ['drivers' => DemoData::drivers()]);
    }

    public function haulers()
    {
        $this->allow('haulers.view');

        return view('admin.haulers', ['haulers' => DemoData::haulers()]);
    }

    public function products()
    {
        $this->allow('products.manage');

        return view('admin.products', ['products' => DemoData::products()]);
    }

    public function reports()
    {
        $this->allow('reports.view');

        return view('admin.reports', ['atls' => DemoData::atls(), 'activity' => DemoData::documentActivity()]);
    }

    public function audit()
    {
        $this->allow('audit.view');

        return view('admin.audit', ['events' => DemoData::audit()]);
    }

    public function settings()
    {
        $this->allow('settings.manage');

        return view('admin.settings', ['devices' => DemoData::devices()]);
    }
}
