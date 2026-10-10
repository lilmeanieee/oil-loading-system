<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-800">{{ $title }}</h2>
            @if (\App\Support\Role::can('atl.create'))
                <a href="{{ route('atl.create') }}" class="rounded bg-amber-500 px-4 py-2 text-sm font-medium text-slate-900 hover:bg-amber-400">Create ATL</a>
            @endif
        </div>
    </x-slot>

    @php
        $rows = collect($atls)->map(fn ($a) => [
            's' => strtolower($a['so_no'].' '.$a['atl_no'].' '.$a['control_no'].' '.$a['plate'].' '.$a['authorized'].' '.$a['hauler'].' '.$a['customer'].' '.$a['destination']),
            't' => $a['status'],
            'd' => $a['date'],
        ])->values();
        $in = 'rounded border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500';
    @endphp

    <div class="py-6" x-data="{
        rows: @js($rows), q: '', st: @js($preset), from: '', to: '',
        m(i) { const r = this.rows[i]; return r.s.includes(this.q.toLowerCase().trim()) && (this.st === 'All' || r.t === this.st) && (!this.from || r.d >= this.from) && (!this.to || r.d <= this.to) },
        get n() { return this.rows.filter((r, i) => this.m(i)).length }
    }">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-3 rounded border border-slate-200 bg-white p-4 sm:grid-cols-4">
                <div class="sm:col-span-2">
                    <label for="q" class="mb-1 block text-sm font-medium text-slate-700">Search by SO, ATL, or control number, plate, driver, hauler</label>
                    <input id="q" type="search" x-model="q" class="{{ $in }} w-full">
                </div>
                <div>
                    <label for="st" class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                    <select id="st" x-model="st" class="{{ $in }} w-full"><option>All</option><option>Draft</option><option>Submitted</option><option>Approved</option><option>Declined</option><option>Loading</option><option>Completed</option></select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div><label for="from" class="mb-1 block text-sm font-medium text-slate-700">From</label><input id="from" type="date" x-model="from" class="{{ $in }} w-full"></div>
                    <div><label for="to" class="mb-1 block text-sm font-medium text-slate-700">To</label><input id="to" type="date" x-model="to" class="{{ $in }} w-full"></div>
                </div>
            </div>

            <div class="overflow-x-auto rounded border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            @foreach (['SO number', 'ATL no.', 'Control no.', 'Date', 'Customer', 'Destination', 'Plate', 'Driver', 'Hauler', 'Products', 'Liters', 'Status', 'Created by', ''] as $h)
                                <th class="whitespace-nowrap px-4 py-3 font-medium {{ $h === 'Liters' ? 'text-right' : '' }}">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($atls as $a)
                            <tr x-show="m({{ $loop->index }})">
                                <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $a['so_no'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $a['atl_no'] }}</td>
                                <td class="px-4 py-3">{{ $a['control_no'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ \Carbon\Carbon::parse($a['date'])->format('m/d/Y') }}</td>
                                <td class="px-4 py-3">{{ $a['customer'] }}</td>
                                <td class="px-4 py-3">{{ $a['destination'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $a['plate'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $a['authorized'] }}</td>
                                <td class="px-4 py-3">{{ $a['hauler'] }}</td>
                                <td class="px-4 py-3">{{ collect($a['items'])->pluck('product')->unique()->implode(', ') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format(collect($a['items'])->sum('volume')) }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$a['status']" /></td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $a['creator'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <a class="font-medium text-amber-700 hover:underline" href="{{ route('atl.show', $a['id']) }}">
                                        {{ $a['status'] === 'Submitted' && \App\Support\Role::can('atl.approve') ? 'Review' : 'View' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div x-show="n === 0" x-cloak class="px-4 py-8 text-center text-sm text-slate-600">No ATL matches these filters. Clear the search or choose "All" under Status.</div>
            </div>
        </div>
    </div>
</x-app-layout>
