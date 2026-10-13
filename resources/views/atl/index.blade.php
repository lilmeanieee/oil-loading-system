<x-app-layout>
    @php
        $canCreate = \App\Support\Role::can('atl.create');
        $canApprove = \App\Support\Role::can('atl.approve');
        $rows = collect($atls)->map(fn ($a) => [
            's' => strtolower($a['so_no'].' '.$a['atl_no'].' '.$a['control_no'].' '.$a['plate'].' '.$a['authorized'].' '.$a['hauler'].' '.$a['customer'].' '.$a['destination']),
            't' => $a['status'],
            'd' => $a['date'],
        ])->values();
        $in = 'rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700';
        $groups = ['All' => null, 'Drafts' => ['Draft'], 'Pending' => ['Submitted'], 'Approved' => ['Approved', 'Loading', 'Completed'], 'Returned' => ['Declined']];
        $startTab = ['Draft' => 'Drafts', 'Submitted' => 'Pending', 'Approved' => 'Approved', 'Loading' => 'Approved', 'Completed' => 'Approved', 'Declined' => 'Returned'][$preset] ?? 'All';
        $tabCounts = collect($groups)->map(fn ($g) => $g === null ? count($atls) : collect($atls)->whereIn('status', $g)->count());
        $heads = ['SO number', 'ATL no.', 'Control no.', 'Date', 'Customer', 'Destination', 'Plate', 'Driver', 'Hauler', 'Products', 'Liters', 'Status', 'Created by', "Operations' remarks", ''];
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">{{ $title }}</h2>
                @if (in_array($mode, ['mine', 'drafts', 'returned']))<p class="mt-1 text-sm text-slate-600">View ATLs created by the currently logged-in user.</p>@endif
            </div>
            @if ($canCreate)
                <a href="{{ route('atl.create') }}" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">+ Create ATL</a>
            @endif
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        rows: @js($rows), q: '', tab: @js($startTab), groups: @js($groups), from: '', to: '',
        m(i) { const r = this.rows[i]; return r.s.includes(this.q.toLowerCase().trim()) && (!this.groups[this.tab] || this.groups[this.tab].includes(r.t)) && (!this.from || r.d >= this.from) && (!this.to || r.d <= this.to) },
        get n() { return this.rows.filter((r, i) => this.m(i)).length }
    }">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            <nav class="flex flex-wrap gap-2 rounded-lg border border-slate-200 bg-white p-3" role="tablist" aria-label="ATL record status">
                @foreach ($groups as $label => $g)
                    <button type="button" role="tab" @click="tab = '{{ $label }}'" :aria-selected="tab === '{{ $label }}'"
                            :class="tab === '{{ $label }}' ? 'bg-[#1c3d34] text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100'"
                            class="flex-1 rounded-lg px-4 py-2 text-sm font-medium">{{ $label === 'All' ? 'All Records' : $label }} <span class="ml-1 text-xs opacity-70">({{ $tabCounts[$label] }})</span></button>
                @endforeach
            </nav>

            <div class="grid gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:grid-cols-4">
                <div class="sm:col-span-2">
                    <label for="q" class="mb-1 block text-sm font-medium text-slate-700">Search ATL number, control number, SO number, plate, driver or hauler</label>
                    <input id="q" type="search" x-model="q" class="{{ $in }} w-full">
                </div>
                <div class="grid grid-cols-2 gap-2 sm:col-span-2">
                        <div><label for="from" class="mb-1 block text-sm font-medium text-slate-700">From</label><input id="from" type="date" x-model="from" class="{{ $in }} w-full"></div>
                        <div><label for="to" class="mb-1 block text-sm font-medium text-slate-700">To</label><input id="to" type="date" x-model="to" class="{{ $in }} w-full"></div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>@foreach ($heads as $h)<th class="whitespace-nowrap px-4 py-3 font-medium {{ $h === 'Liters' ? 'text-right' : '' }}">{{ $h }}</th>@endforeach</tr>
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
                                <td class="min-w-[14rem] px-4 py-3 text-slate-700">{{ $a['decline_reason'] ?? '' }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if (in_array($a['status'], ['Draft', 'Declined']) && $canCreate)
                                        <a class="font-medium text-emerald-800 hover:underline" href="{{ route('atl.create', ['edit' => $a['id']]) }}">{{ $a['status'] === 'Draft' ? 'Continue editing' : 'Correct and resubmit' }}</a>
                                    @else
                                        <a class="font-medium text-emerald-800 hover:underline" href="{{ route('atl.show', $a['id']) }}">{{ $a['status'] === 'Submitted' && $canApprove ? 'Review' : 'View' }}</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div x-show="n === 0" x-cloak class="px-4 py-8 text-center text-sm text-slate-600">Nothing matches. Clear the search or switch to "All Records".</div>
            </div>

            @if (in_array($mode, ['drafts', 'mine']))
                <p class="text-sm text-slate-500">Drafts and submissions saved while offline appear here after they sync. See the offline sync button at the bottom right.</p>
            @endif
        </div>
    </div>
</x-app-layout>
