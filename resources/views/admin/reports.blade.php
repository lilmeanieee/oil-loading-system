<x-app-layout>
    @php
        $in = 'rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700';
        $tab = 'border-b-2 px-4 py-2 text-sm font-medium';
        $byStatus = collect($atls)->countBy('status');
        $liters = collect($atls)->flatMap(fn ($a) => $a['items'])->groupBy('product')->map(fn ($rows) => $rows->sum('volume'));
        $rows = collect($atls)->map(fn ($a) => ['t' => $a['status'], 'd' => $a['date']])->values();
    @endphp

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900">Reports</h2>
        <p class="mt-1 text-sm text-slate-600">ATL summaries and document activity</p>
    </x-slot>

    <div class="py-6" x-data="{
        tab: 'summary', rows: @js($rows), st: 'All', from: '', to: '',
        m(i) { const r = this.rows[i]; return (this.st === 'All' || r.t === this.st) && (!this.from || r.d >= this.from) && (!this.to || r.d <= this.to) },
        exportCsv() {
            const lines = [...document.querySelectorAll('#register-table tr')].filter(r => r.offsetParent !== null);
            const csv = lines.map(r => [...r.children].map(c => '&quot;' + c.innerText.trim().replace(/&quot;/g, '&quot;&quot;') + '&quot;').join(',')).join('\n');
            const a = document.createElement('a');
            a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
            a.download = 'atl-register.csv'; a.click(); URL.revokeObjectURL(a.href);
        }
    }">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap gap-2 border-b border-slate-200 print:hidden" role="tablist">
                @foreach (['summary' => 'ATL summary', 'register' => 'ATL register', 'documents' => 'Document activity'] as $key => $label)
                    <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'" class="{{ $tab }}" :class="tab === '{{ $key }}' ? 'border-[#1c3d34] text-slate-900' : 'border-transparent text-slate-500'">{{ $label }}</button>
                @endforeach
            </div>

            <section x-show="tab === 'summary'" class="space-y-5">
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-6">
                    @foreach (['Draft', 'Submitted', 'Approved', 'Declined', 'Loading', 'Completed'] as $s)
                        <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm text-slate-600">{{ $s }}</p><p class="mt-1 text-2xl font-semibold text-slate-900">{{ $byStatus[$s] ?? 0 }}</p></div>
                    @endforeach
                </div>
                <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                    <h3 class="px-4 pt-4 font-semibold text-slate-900">Requested liters by product</h3>
                    <table class="mt-2 min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-2 font-medium">Product</th><th class="px-4 py-2 text-right font-medium">Liters on all ATLs</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">@foreach ($liters as $product => $l)<tr><td class="px-4 py-2">{{ $product }}</td><td class="px-4 py-2 text-right">{{ number_format($l) }}</td></tr>@endforeach</tbody>
                    </table>
                </div>
            </section>

            <section x-show="tab === 'register'" x-cloak class="space-y-4">
                <div class="flex flex-wrap items-end gap-3 print:hidden">
                    <div><label for="r_st" class="mb-1 block text-sm font-medium text-slate-700">Status</label><select id="r_st" x-model="st" class="{{ $in }}"><option>All</option><option>Draft</option><option>Submitted</option><option>Approved</option><option>Declined</option><option>Loading</option><option>Completed</option></select></div>
                    <div><label for="r_from" class="mb-1 block text-sm font-medium text-slate-700">From</label><input id="r_from" type="date" x-model="from" class="{{ $in }}"></div>
                    <div><label for="r_to" class="mb-1 block text-sm font-medium text-slate-700">To</label><input id="r_to" type="date" x-model="to" class="{{ $in }}"></div>
                    <div class="ms-auto flex gap-2">
                        <button type="button" @click="exportCsv()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Export CSV</button>
                        <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Print</button>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                    <table id="register-table" class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3 font-medium">ATL no.</th><th class="px-4 py-3 font-medium">SO number</th><th class="px-4 py-3 font-medium">Date</th><th class="px-4 py-3 font-medium">Plate</th><th class="px-4 py-3 font-medium">Destination</th><th class="px-4 py-3 text-right font-medium">Liters</th><th class="px-4 py-3 font-medium">Status</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($atls as $a)
                                <tr x-show="m({{ $loop->index }})">
                                    <td class="px-4 py-2">{{ $a['atl_no'] }}</td><td class="px-4 py-2">{{ $a['so_no'] }}</td><td class="px-4 py-2">{{ \Carbon\Carbon::parse($a['date'])->format('m/d/Y') }}</td><td class="px-4 py-2">{{ $a['plate'] }}</td><td class="px-4 py-2">{{ $a['destination'] }}</td><td class="px-4 py-2 text-right">{{ number_format(collect($a['items'])->sum('volume')) }}</td><td class="px-4 py-2">{{ $a['status'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section x-show="tab === 'documents'" x-cloak class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3 font-medium">When</th><th class="px-4 py-3 font-medium">Document</th><th class="px-4 py-3 font-medium">Number</th><th class="px-4 py-3 font-medium">ATL</th><th class="px-4 py-3 font-medium">Event</th><th class="px-4 py-3 font-medium">By</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($activity as $e)
                            <tr><td class="whitespace-nowrap px-4 py-2">{{ \Carbon\Carbon::parse($e['at'])->format('M d, g:i A') }}</td><td class="px-4 py-2">{{ $e['doc'] }}</td><td class="px-4 py-2">{{ $e['no'] }}</td><td class="px-4 py-2">{{ $e['atl'] }}</td><td class="px-4 py-2">{{ $e['event'] }}</td><td class="px-4 py-2">{{ $e['user'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</x-app-layout>
