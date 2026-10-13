<?php

namespace App\Http\Controllers;

use App\Support\OpsData;
use App\Support\Role;

/** Operations pages: ATL Approvals and Documents & Release. */
class OperationsController extends Controller
{
    public function approvals()
    {
        abort_unless(Role::can('atl.approve'), 403);

        return view('operations.approvals', ['opsSeed' => OpsData::seed(optional(auth()->user())->name)]);
    }

    public function documents()
    {
        abort_unless(Role::can('documents.issue'), 403);

        return view('operations.documents', ['opsSeed' => OpsData::seed(optional(auth()->user())->name)]);
    }
}
