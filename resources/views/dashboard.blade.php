<x-app-layout>
    @php
        $titles = [
            'atl_user'   => ['My Dashboard', 'Track your ATL submissions'],
            'operations' => ['Operations overview', 'ATLs, approvals and loading'],
            'admin'      => ['Admin Dashboard', 'System overview and management'],
            'owner'      => ['Management overview', 'Records and reports (view only)'],
        ];
        [$pageTitle, $pageSub] = $titles[$role];
    @endphp

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900">{{ $pageTitle }}</h2>
        <p class="mt-1 text-sm text-slate-600">{{ $pageSub }}</p>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            @include('dashboard.'.($role === 'owner' ? 'staff' : $role))

        </div>
    </div>
</x-app-layout>
