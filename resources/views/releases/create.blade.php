<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Issue documents for {{ $atl['atl_no'] }}</h2>
    </x-slot>

    @php
        $in = 'w-full rounded border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500';
        $ro = 'w-full rounded border-slate-200 bg-slate-100 text-sm text-slate-600';
        $lb = 'mb-1 block text-sm font-medium text-slate-700';
        $atlInfo = ['so' => $atl['so_no'], 'plate' => $atl['plate'], 'driver' => $atl['authorized'], 'hauler' => $atl['hauler'], 'destination' => $atl['destination']];
        $today = strtoupper(now()->format('F d, Y'));
    @endphp

    <form method="POST" action="{{ route('releases.store') }}" class="py-6" data-offline="queue" data-offline-label="Delivery receipt and withdrawal certificate" data-offline-ref="dr_no" x-data="releaseForm(@js($lines), @js($next), @js($used), @js($signers), @js($atlInfo), @js($today))">
        @csrf
        <input type="hidden" name="atl_id" value="{{ $atl['id'] }}">

        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <p class="rounded border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                The Delivery Receipt and Withdrawal Certificate are issued together, using the liters requested on the ATL. They print onto the pre-printed forms, so only the typed details are printed.
            </p>

            <section class="rounded border border-slate-200 bg-white p-5">
                <h3 class="mb-4 font-semibold text-slate-900">Document numbers</h3>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="{{ $lb }}" for="dr_no">Delivery Receipt number</label>
                        <input id="dr_no" name="dr_no" x-model="drNo" class="{{ $in }}" required>
                        <p x-show="drUsed" x-cloak class="mt-1 text-sm text-red-700">This DR number is already used. Check the number on the paper.</p>
                    </div>
                    <div>
                        <label class="{{ $lb }}" for="wcp_no">Withdrawal Certificate (WCP) number</label>
                        <input id="wcp_no" name="wcp_no" x-model="wcpNo" class="{{ $in }}" required>
                        <p x-show="wcpUsed" x-cloak class="mt-1 text-sm text-red-700">This WCP number is already used. Check the number on the paper.</p>
                    </div>
                    <div><label class="{{ $lb }}">SO number</label><input class="{{ $ro }}" value="{{ $atl['so_no'] }}" readonly></div>
                </div>
                <p class="mt-2 text-xs text-slate-500">These are suggestions. Compare them with the number on the paper in the printer and change them if they differ.</p>
            </section>

            <section class="rounded border border-slate-200 bg-white p-5">
                <h3 class="mb-4 font-semibold text-slate-900">Delivery details</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="{{ $lb }}" for="delivered_to">Delivered to (consignee)</label><input id="delivered_to" name="delivered_to" x-model="deliveredTo" class="{{ $in }}" required></div>
                    <div><label class="{{ $lb }}" for="address">Address</label><input id="address" name="address" x-model="address" class="{{ $in }}"></div>
                    <div><label class="{{ $lb }}" for="destination">Destination</label><input id="destination" name="destination" x-model="destination" class="{{ $in }}"></div>
                    <div><label class="{{ $lb }}" for="plate">Plate number</label><input id="plate" name="plate" x-model="plate" class="{{ $in }}"></div>
                    <div><label class="{{ $lb }}" for="driver">Driver</label><input id="driver" name="driver" x-model="driver" class="{{ $in }}"></div>
                    <div><label class="{{ $lb }}" for="seal">Seal number range</label><input id="seal" name="seal_range" x-model="seal" placeholder="Example: 15427-15440" class="{{ $in }}"></div>
                </div>
            </section>

            <section class="rounded border border-slate-200 bg-white p-5">
                <h3 class="mb-1 font-semibold text-slate-900">Products</h3>
                <p class="mb-4 text-sm text-slate-600">Product names follow the Delivery Receipt and Withdrawal Certificate. "CME 3%" is the default note for diesel and "TAX PAID" for the others. You can edit any of them.</p>
                <div class="space-y-2">
                    <template x-for="(l, i) in lines" :key="i">
                        <div class="grid grid-cols-[1fr_8rem_8rem] items-center gap-3">
                            <input :name="`lines[${i}][name]`" x-model="l.name" class="{{ $in }}" aria-label="Product">
                            <input type="number" min="0" :name="`lines[${i}][liters]`" x-model="l.liters" class="{{ $in }}" aria-label="Liters">
                            <input :name="`lines[${i}][tax]`" x-model="l.tax" class="{{ $in }}" aria-label="Tax column">
                        </div>
                    </template>
                </div>
                <p class="mt-3 text-sm font-medium text-slate-800">Total: <span x-text="fmt(total)"></span> liters</p>
                <div class="mt-4"><label class="{{ $lb }}" for="remarks">Remarks (Withdrawal Certificate)</label><input id="remarks" name="remarks" x-model="remarks" class="{{ $in }}"></div>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="rounded border border-slate-200 bg-white p-5">
                    <h3 class="mb-2 font-semibold text-slate-900">Delivery Receipt: printed text</h3>
                    <pre class="overflow-x-auto whitespace-pre-wrap font-mono text-xs leading-5 text-slate-800" x-text="drText"></pre>
                </div>
                <div class="rounded border border-slate-200 bg-white p-5">
                    <h3 class="mb-2 font-semibold text-slate-900">Withdrawal Certificate: printed text</h3>
                    <pre class="overflow-x-auto whitespace-pre-wrap font-mono text-xs leading-5 text-slate-800" x-text="wcText"></pre>
                </div>
            </section>

            <div class="flex justify-end gap-3">
                <a href="{{ route('atl.show', $atl['id']) }}" class="rounded border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" :disabled="drUsed || wcpUsed || !drNo || !wcpNo" class="rounded bg-amber-500 px-5 py-2 text-sm font-medium text-slate-900 hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50">Issue both documents</button>
            </div>
        </div>
    </form>

    <script>
        function releaseForm(lines, next, used, signers, atl, today) {
            return {
                used, signers, atl, date: today,
                drNo: next.dr, wcpNo: next.wcp,
                deliveredTo: atl.hauler, address: '', destination: atl.destination, plate: atl.plate, driver: atl.driver,
                seal: '', remarks: 'TAX PAID',
                lines: lines.map(l => ({ name: l.name, liters: l.liters, tax: l.tax || 'TAX PAID' })),
                get total() { return this.lines.reduce((s, l) => s + (+l.liters || 0), 0) },
                get drUsed() { return this.used.includes(String(this.drNo).trim()) },
                get wcpUsed() { return this.used.includes(String(this.wcpNo).trim()) },
                get drText() {
                    const rows = this.lines.map(l => `${this.fmt(l.liters)}  LITERS   ${l.name}`);
                    return [this.date, this.deliveredTo, this.address, '', ...rows, '',
                        '* DISCHARGED VALVE DRAW-OFF QUALITY VERIFIED (DOME AND DISCHARGED VALVE)',
                        '* QUANTITY CHECKED (VIA LEVEL GAUGE)', '* TRUCK SEALED PROPERLY',
                        `SEAL#: ${this.seal}      ${this.wcpNo}`, `SO#${this.atl.so}`, this.signers.owner].join('\n');
                },
                get wcText() {
                    const rows = this.lines.map(l => `${l.name}   ${this.fmt(l.liters)}   ${l.tax}`);
                    return ['PA FUEL 118 CORPORATION', this.deliveredTo, this.address, `${this.plate}   ${this.driver}   ${this.destination}`,
                        this.date, '', ...rows, `TOTAL   ${this.fmt(this.total)}`, '', `${this.remarks}   DR#${this.drNo}`, '',
                        `${this.signers.officer}     ${this.signers.owner}`].join('\n');
                },
                fmt(n) { return Number(n).toLocaleString() }
            }
        }
    </script>
</x-app-layout>
