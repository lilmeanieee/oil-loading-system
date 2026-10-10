<?php

namespace App\Http\Controllers;

use App\Support\DemoData;
use App\Support\Role;

class DashboardController extends Controller
{
    public function index()
    {
        $role = Role::current();

        // Admin / IT has no access to ATL records, so they see truck master information only.
        if ($role === 'admin') {
            $trucks = collect(DemoData::trucks());
            $attention = $trucks->filter(fn ($t) => collect($t['cert_list'])->contains(fn ($c) => $c['status'] !== 'Valid'))->values()->all();

            return view('dashboard', [
                'truckStats' => [
                    'Trucks registered'         => $trucks->count(),
                    'Usable trucks'             => $trucks->where('eligible', true)->count(),
                    'Certificates expiring soon' => $trucks->flatMap(fn ($t) => $t['cert_list'])->where('status', 'Expiring soon')->count(),
                    'Expired certificates'      => $trucks->flatMap(fn ($t) => $t['cert_list'])->where('status', 'Expired')->count(),
                ],
                'attention' => $attention,
            ]);
        }

        $atls = collect(DemoData::atls());

        $cardsByRole = [
            'atl_user'   => ['Draft', 'Submitted', 'Declined', 'Approved'],
            'operations' => ['Submitted', 'Approved', 'Loading', 'Completed'],
            'owner'      => ['Submitted', 'Approved', 'Loading', 'Completed'],
        ];

        $activity = $atls
            ->flatMap(fn ($a) => collect($a['timeline'])->map(fn ($t) => $t + ['atl' => $a['atl_no'], 'atl_id' => $a['id']]))
            ->sortByDesc('at')->take(6)->values()->all();

        return view('dashboard', [
            'atls'     => $atls->sortByDesc('id')->take(5)->values()->all(),
            'counts'   => $atls->countBy('status'),
            'cards'    => $cardsByRole[$role],
            'activity' => $activity,
        ]);
    }
}
