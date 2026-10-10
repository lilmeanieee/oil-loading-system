<x-app-layout>
    @php
        $role = \App\Support\Role::current();
        $hints = ['Draft' => 'Not yet submitted', 'Submitted' => 'Waiting for Operations', 'Declined' => 'Needs correction', 'Approved' => 'Ready for loading and documents', 'Loading' => 'At the depot', 'Completed' => 'Finished'];
        $greeting = ['atl_user' => 'Your ATLs', 'operations' => 'Operations overview', 'admin' => 'System overview', 'owner' => 'Management overview'][$role];
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-800">{{ $greeting }}</h2>
            @if (\App\Support\Role::can('atl.create'))
                <a href="{{ route('atl.create') }}" class="rounded bg-amber-500 px-4 py-2 text-sm font-medium text-slate-900 hover:bg-amber-400">Create ATL</a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            @if ($role === 'admin')
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach ($truckStats as $label => $n)
                        <div class="rounded border border-slate-200 bg-white p-4">
                            <div class="text-2xl font-semibold text-slate-900">{{ $n }}</div>
                            <div class="text-sm font-medium text-slate-700">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="overflow-x-auto rounded border border-slate-200 bg-white">
                    <div class="flex items-center justify-between px-4 pt-4">
                        <h3 class="font-semibold text-slate-900">Trucks that need attention</h3>
                        <a href="{{ route('trucks.index') }}" class="text-sm font-medium text-amber-700 hover:underline">Open truck master</a>
                    </div>
                    <table class="mt-2 min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-2 font-medium">Plate</th><th class="px-4 py-2 font-medium">Hauling company</th><th class="px-4 py-2 font-medium">Certificates to check</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($attention as $t)
                                <tr>
                                    <td class="px-4 py-2 font-medium">{{ $t['plate'] }}</td>
                                    <td class="px-4 py-2">{{ $t['hauler'] }}</td>
                                    <td class="px-4 py-2">
                                        @foreach (array_filter($t['cert_list'], fn ($c) => $c['status'] !== 'Valid') as $c)
                                            <span class="me-2">{{ $c['name'] }}: {{ $c['status'] }} ({{ $c['expiry'] }})</span>
                                        @endforeach
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-slate-600">All truck certificates are valid.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach ($cards as $s)
                        <a href="{{ route('atl.index', ['status' => $s]) }}" class="rounded border border-slate-200 bg-white p-4 hover:border-amber-500">
                            <div class="text-2xl font-semibold text-slate-900">{{ $counts[$s] ?? 0 }}</div>
                            <div class="text-sm font-medium text-slate-700">{{ $s }}</div>
                            <div class="text-xs text-slate-500">{{ $hints[$s] }}</div>
                        </a>
                    @endforeach
                </div>

                <div class="grid gap-5 lg:grid-cols-3">
                    <div class="overflow-x-auto rounded border border-slate-200 bg-white lg:col-span-2">
                        <h3 class="px-4 pt-4 font-semibold text-slate-900">Recent ATLs</h3>
                        <table class="mt-2 min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-2 font-medium">ATL no.</th><th class="px-4 py-2 font-medium">SO number</th><th class="px-4 py-2 font-medium">Plate</th><th class="px-4 py-2 font-medium">Driver</th><th class="px-4 py-2 font-medium">Status</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($atls as $a)
                                    <tr>
                                        <td class="px-4 py-2 font-medium"><a class="underline decoration-amber-500 underline-offset-2" href="{{ route('atl.show', $a['id']) }}">{{ $a['atl_no'] }}</a></td>
                                        <td class="px-4 py-2">{{ $a['so_no'] }}</td>
                                        <td class="px-4 py-2">{{ $a['plate'] }}</td>
                                        <td class="px-4 py-2">{{ $a['authorized'] }}</td>
                                        <td class="px-4 py-2"><x-status-badge :status="$a['status']" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="rounded border border-slate-200 bg-white p-4">
                        <h3 class="font-semibold text-slate-900">Recent activity</h3>
                        <ul class="mt-3 space-y-3 text-sm">
                            @foreach ($activity as $t)
                                <li>
                                    <p class="text-slate-800">{{ $t['label'] }} on <a class="underline decoration-amber-500 underline-offset-2" href="{{ route('atl.show', $t['atl_id']) }}">{{ $t['atl'] }}</a></p>
                                    <p class="text-xs text-slate-500">{{ $t['by'] }}, {{ \Carbon\Carbon::parse($t['at'])->format('M d, g:i A') }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
