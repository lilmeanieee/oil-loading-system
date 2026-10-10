<x-app-layout>
    @php
        $role = \App\Support\Role::class;
        $btn = 'rounded px-4 py-2 text-sm font-medium';
        $total = collect($atl['items'])->sum('volume');
        $lines = max(count($atl['items']), count($atl['plan']));
    @endphp

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 print:hidden">
            <h2 class="text-xl font-semibold text-slate-800">{{ $atl['atl_no'] }} <span class="ms-2 align-middle"><x-status-badge :status="$atl['status']" /></span></h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('atl.index') }}" class="{{ $btn }} border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">Back</a>
                <button type="button" onclick="window.print()" class="{{ $btn }} border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">Print</button>
                @if (in_array($atl['status'], ['Draft', 'Declined']) && $role::can('atl.create'))
                    <a href="{{ route('atl.create', ['edit' => $atl['id']]) }}" class="{{ $btn }} bg-amber-500 text-slate-900 hover:bg-amber-400">{{ $atl['status'] === 'Draft' ? 'Continue editing' : 'Correct and resubmit' }}</a>
                @endif
                @if (in_array($atl['status'], ['Approved', 'Loading']) && $role::can('loading.manage'))
                    <a href="{{ route('loading-tickets.create', $atl['id']) }}" class="{{ $btn }} bg-amber-500 text-slate-900 hover:bg-amber-400">{{ $atl['status'] === 'Approved' ? 'Create loading ticket' : 'Open loading ticket' }}</a>
                @endif
                @if ($atl['status'] === 'Approved' && ! $atl['docs_issued'] && $role::can('documents.issue'))
                    <a href="{{ route('releases.create', $atl['id']) }}" class="{{ $btn }} border border-amber-500 bg-white text-slate-900 hover:bg-amber-50">Issue documents</a>
                @endif
                @if ($atl['docs_issued'] && $role::can('documents.view'))
                    <a href="{{ route('documents.index') }}" class="{{ $btn }} border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">View issued documents</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 print:hidden">{{ session('status') }}</div>
            @endif

            @if ($atl['status'] === 'Declined')
                <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 print:hidden"><strong>Declined.</strong> {{ $atl['decline_reason'] }}</div>
            @endif

            @if ($atl['status'] === 'Submitted' && $role::can('atl.approve'))
                <div class="rounded border border-slate-200 bg-white p-4 print:hidden" x-data="{ declining: false }">
                    <p class="mb-3 text-sm text-slate-700">This ATL is waiting for your decision.</p>
                    <div class="flex gap-2" x-show="!declining">
                        <form method="POST" action="{{ route('atl.approve', $atl['id']) }}">
                            @csrf
                            <button type="submit" class="{{ $btn }} bg-emerald-600 text-white hover:bg-emerald-700">Approve ATL</button>
                        </form>
                        <button type="button" @click="declining = true" class="{{ $btn }} border border-red-300 bg-white text-red-700 hover:bg-red-50">Decline</button>
                    </div>
                    <form method="POST" action="{{ route('atl.decline', $atl['id']) }}" x-show="declining" x-cloak class="space-y-2">
                        @csrf
                        <label for="reason" class="block text-sm font-medium text-slate-700">Reason for declining (the creator will see this)</label>
                        <textarea id="reason" name="reason" rows="3" required minlength="5" class="w-full rounded border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                        @error('reason')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                        <div class="flex gap-2">
                            <button type="submit" class="{{ $btn }} bg-red-600 text-white hover:bg-red-700">Decline ATL</button>
                            <button type="button" @click="declining = false" class="{{ $btn }} border border-slate-300 bg-white text-slate-700">Cancel</button>
                        </div>
                    </form>
                </div>
            @endif

            <article class="space-y-5 rounded border border-slate-200 bg-white p-6 text-sm text-slate-800">
                <header class="text-center">
                    <p class="text-lg font-bold">{{ $atl['customer'] }}</p>
                    <p class="text-xs">{{ $atl['customer_address'] }}</p>
                    <h3 class="mt-2 font-semibold">AUTHORITY TO LOAD</h3>
                </header>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p>Attention: <strong>{{ $atl['attention'] }}</strong></p>
                        <p>Address: {{ $atl['address'] ?: 'Not given' }}</p>
                        <p>To: {{ $atl['to'] }}. Thru: {{ $atl['thru'] }}.</p>
                    </div>
                    <dl class="grid grid-cols-[auto_auto] gap-x-4 gap-y-1">
                        <dt class="text-slate-500">Date</dt><dd>{{ \Carbon\Carbon::parse($atl['date'])->format('m/d/Y') }}</dd>
                        <dt class="text-slate-500">Control number</dt><dd>{{ $atl['control_no'] }}</dd>
                        <dt class="text-slate-500">ATL number</dt><dd>{{ $atl['atl_no'] }}</dd>
                        <dt class="text-slate-500">SO number</dt><dd>{{ $atl['so_no'] }}</dd>
                    </dl>
                </div>

                <p>This is to authorize <strong>{{ $atl['authorized'] }}</strong> to withdraw the following products from your depot or terminal warehouse.</p>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-slate-300">
                        <thead class="bg-slate-50 text-left">
                            <tr>
                                <th class="border border-slate-300 px-3 py-2">SO no.</th>
                                <th class="border border-slate-300 px-3 py-2">Product name</th>
                                <th class="border border-slate-300 px-3 py-2 text-right">Volume, liters</th>
                                <th class="border border-slate-300 px-3 py-2">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < $lines; $i++)
                                @php $it = $atl['items'][$i] ?? null; $c = $atl['plan'][$i] ?? null; @endphp
                                <tr>
                                    <td class="border border-slate-300 px-3 py-2">{{ $it ? $atl['so_no'] : '' }}</td>
                                    <td class="border border-slate-300 px-3 py-2">{{ $it['product'] ?? '' }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-right">{{ $it ? number_format($it['volume']) : '' }}</td>
                                    <td class="border border-slate-300 px-3 py-2">{{ $c ? '#'.$c['comp'].' '.$c['liters'].' - '.$c['product'] : '' }}</td>
                                </tr>
                            @endfor
                            <tr class="font-medium">
                                <td colspan="2" class="border border-slate-300 px-3 py-2 text-right">Total</td>
                                <td class="border border-slate-300 px-3 py-2 text-right">{{ number_format($total) }}</td>
                                <td class="border border-slate-300 px-3 py-2"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <dl class="grid max-w-md grid-cols-[10rem_1fr] gap-y-1">
                    <dt class="text-slate-500">Hauling company</dt><dd>{{ $atl['hauler'] }}</dd>
                    <dt class="text-slate-500">Plate number</dt><dd>{{ $atl['plate'] }}</dd>
                    <dt class="text-slate-500">Capacity</dt><dd>{{ $atl['capacity'] }}</dd>
                    <dt class="text-slate-500">Total seals</dt><dd>{{ $atl['total_seals'] ?: 'Not given' }}</dd>
                    <dt class="text-slate-500">Destination</dt><dd>{{ $atl['destination'] }}</dd>
                </dl>

                <div class="grid gap-6 pt-6 sm:grid-cols-2">
                    <div><div class="border-b border-slate-400 pb-1">{{ $atl['creator'] }}</div><p class="mt-1 text-xs text-slate-500">Issued by</p></div>
                    <div><div class="border-b border-slate-400 pb-1">{{ $atl['authorized'] }}</div><p class="mt-1 text-xs text-slate-500">Issued to (signature over printed name)</p></div>
                </div>
            </article>

            <section class="rounded border border-slate-200 bg-white p-5 print:hidden">
                <h3 class="mb-3 font-semibold text-slate-900">History</h3>
                <ol class="space-y-3 border-s border-slate-200 ps-4 text-sm">
                    @foreach ($atl['timeline'] as $t)
                        <li>
                            <p class="font-medium text-slate-800">{{ $t['label'] }}</p>
                            <p class="text-xs text-slate-500">{{ $t['by'] }}, {{ \Carbon\Carbon::parse($t['at'])->format('M d, Y g:i A') }}</p>
                            @if ($t['note'])<p class="text-slate-600">{{ $t['note'] }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>
    </div>
</x-app-layout>
