<?php

namespace App\Http\Controllers;

use App\Support\DemoData;
use App\Support\Role;

class DashboardController extends Controller
{
    public const ME = 'Ma\'am Jhona'; // demo: the ATL User who "owns" the sample ATLs

    public function index()
    {
        $role = Role::current();
        $atls = collect(DemoData::atls());
        $data = ['role' => $role, 'notes' => DemoData::notifications($role)];

        if ($role === 'atl_user') {
            $mine = $atls->where('creator', self::ME);
            $data += [
                'counts' => [
                    'drafts'   => $mine->where('status', 'Draft')->count(),
                    'pending'  => $mine->where('status', 'Submitted')->count(),
                    'approved' => $mine->whereIn('status', ['Approved', 'Loading', 'Completed'])->count(),
                    'returned' => $mine->where('status', 'Declined')->count(),
                ],
                'recent' => $mine->sortByDesc('id')->take(4)->values()->all(),
            ];
        } else {
            $data += $role === 'admin' ? $this->adminData($atls) : $this->staffData($atls);
        }

        if ($role === 'operations') {
            $data['opsSeed'] = \App\Support\OpsData::seed(optional(auth()->user())->name);
        }

        return view('dashboard', $data);
    }

    private function adminData($atls): array
    {
        $users = collect(DemoData::users());
        $trucks = collect(DemoData::trucks());

        return [
            'stats' => [
                'Total users'  => $users->count(),
                'Active users' => $users->where('status', 'Active')->count(),
                'Total ATLs'   => $atls->count(),
                'Sync issues'  => collect(DemoData::devices())->where('state', 'Conflict')->count(),
            ],
            'registrations' => DemoData::registrations(),
            'devices'       => DemoData::devices(),
            'audit'         => array_slice(DemoData::audit(), 0, 4),
            'recentAtls'    => $atls->sortByDesc('id')->take(4)->values()->all(),
            'alerts'        => $trucks->filter(fn ($t) => collect($t['cert_list'])->contains(fn ($c) => $c['status'] !== 'Valid'))->values()->all(),
            'driverAlerts'  => collect(DemoData::drivers())->filter(fn ($d) => DemoData::certStatus($d['license_expiry']) !== 'Valid')->values()->all(),
            'lastLogins'    => $users->sortByDesc('last_login')->take(4)->values()->all(),
        ];
    }

    private function staffData($atls): array
    {
        $cards = ['Submitted', 'Approved', 'Loading', 'Completed'];

        $activity = $atls
            ->flatMap(fn ($a) => collect($a['timeline'])->map(
                fn ($t) => $t + ['atl' => $a['atl_no'], 'atl_id' => $a['id']]
            ))
            ->sortByDesc('at')
            ->take(6)
            ->values()
            ->all();

        return [
            'cards'    => $cards,
            'counts'   => $atls->countBy('status'),
            'atls'     => $atls->sortByDesc('id')->take(5)->values()->all(),
            'activity' => $activity,
        ];
    }
}
