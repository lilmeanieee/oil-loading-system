<?php

namespace App\Http\Controllers;

use App\Support\DemoData;
use App\Support\Role;

class PwaController extends Controller
{
    /** Tiny public endpoint the page script uses to check that the server can be reached. */
    public function ping()
    {
        return response()->noContent()->header('Cache-Control', 'no-store');
    }

    /** A fresh CSRF token, so saved forms can be sent after the page that made them is long gone. */
    public function csrf()
    {
        return response()->json(['token' => csrf_token()])->header('Cache-Control', 'no-store');
    }

    /** The pages this user is allowed to open. They are saved on the device so they open offline. */
    public function urls()
    {
        $r = fn (string $name, array $params = []) => route($name, $params, false);
        $pages = [$r('dashboard'), $r('profile.edit')];

        if (Role::can('atl.view')) {
            $pages[] = $r('atl.index');
        }
        if (Role::can('atl.create')) {
            $pages[] = $r('atl.create');
        }
        if (Role::can('atl.approve')) {
            $pages[] = $r('approvals.index');
        }
        if (Role::can('trucks.view')) {
            $pages[] = $r('trucks.index');
        }
        if (Role::can('documents.view')) {
            $pages[] = $r('documents.index');
        }
        if (Role::can('loading.manage')) {
            $pages[] = $r('loading.index');
        }

        if (Role::can('atl.view')) {
            foreach (DemoData::atls() as $a) {
                $pages[] = $r('atl.show', ['id' => $a['id']]);
                if (Role::can('atl.create') && in_array($a['status'], ['Draft', 'Declined'])) {
                    $pages[] = $r('atl.create', ['edit' => $a['id']]);
                }
                if (in_array($a['status'], ['Approved', 'Loading', 'Completed'])) {
                    if (Role::can('loading.manage')) {
                        $pages[] = $r('loading-tickets.create', ['atl' => $a['id']]);
                    }
                    if (Role::can('documents.issue')) {
                        $pages[] = $r('releases.create', ['atl' => $a['id']]);
                    }
                }
            }
        }

        return response()->json(['pages' => array_values(array_unique($pages))])->header('Cache-Control', 'no-store');
    }
}
