<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">{{ $prefill ? 'Edit '.$prefill['atl_no'] : 'Create Authority to Load' }}</h2>
    </x-slot>

    @php
        $in = 'w-full rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700';
        $lb = 'mb-1 block text-sm font-medium text-slate-700';
        $year = now()->format('Y');
        $startSo = $prefill['so_no'] ?? now()->format('Y-m-');
        $startControl = $prefill['control_no'] ?? $year.'-';
        $startAtl = $prefill['atl_no'] ?? 'ATL#';
    @endphp

    <form method="POST" action="{{ route('atl.store') }}" class="py-6" data-offline="queue" data-offline-label="ATL" data-offline-ref="so_no"
          x-data="atlForm(@js($trucks), @js($products), @js($prefill), @js($taken), @js([$startSo, $startControl, $startAtl]))">
        @csrf
        @if ($prefill)<input type="hidden" name="atl_id" value="{{ $prefill['id'] }}">@endif

        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if ($prefill && $prefill['status'] === 'Declined')
                <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><strong>Declined by Operations.</strong> {{ $prefill['decline_reason'] }}</div>
            @endif

            <section class="rounded border border-slate-200 bg-white p-5">
                <h3 class="mb-1 font-semibold text-slate-900">Document information</h3>
                <p class="mb-4 text-sm text-slate-600">The year and month are filled in for you. Type the remaining digits. You can still edit any of these numbers.</p>
                <div class="grid gap-4 sm:grid-cols-4">
                    <div>
                        <label class="{{ $lb }}" for="so_no">SO number</label>
                        <input id="so_no" name="so_no" x-model="soNo" placeholder="2026-10-0000" class="{{ $in }}" required>
                        <p class="mt-1 text-xs text-slate-500">Format YYYY-MM-####</p>
                    </div>
                    <div>
                        <label class="{{ $lb }}" for="control_no">Control number</label>
                        <input id="control_no" name="control_no" x-model="controlNo" placeholder="{{ $year }}-000" class="{{ $in }}" required>
                        <p class="mt-1 text-xs text-slate-500">Format YYYY-###</p>
                    </div>
                    <div>
                        <label class="{{ $lb }}" for="atl_no">ATL number</label>
                        <input id="atl_no" name="atl_no" x-model="atlNo" placeholder="ATL#000" class="{{ $in }}" required>
                        <p class="mt-1 text-xs text-slate-500">Format ATL#000</p>
                    </div>
                    <div>
                        <label class="{{ $lb }}">Date (automatic)</label>
                        <input class="w-full rounded border-slate-200 bg-slate-100 text-sm text-slate-600" value="{{ now()->format('m/d/Y') }}" readonly>
                    </div>
                </div>
            </section>

            <section class="rounded border border-slate-200 bg-white p-5">
                <h3 class="mb-4 font-semibold text-slate-900">Customer and destination</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="{{ $lb }}" for="customer">Customer (company buying the product)</label><input id="customer" name="customer" value="{{ $prefill['customer'] ?? '' }}" class="{{ $in }}" required></div>
                    <div><label class="{{ $lb }}" for="customer_address">Customer address</label><input id="customer_address" name="customer_address" value="{{ $prefill['customer_address'] ?? '' }}" class="{{ $in }}" required></div>
                    <div><label class="{{ $lb }}" for="attention">Attention (oil depot)</label><input id="attention" name="attention" value="{{ $prefill['attention'] ?? 'PA FUEL 118 CORPORATION' }}" class="{{ $in }}" required></div>
                    <div><label class="{{ $lb }}" for="address">Depot address</label><input id="address" name="address" value="{{ $prefill['address'] ?? '' }}" class="{{ $in }}"></div>
                    <div><label class="{{ $lb }}" for="to">To</label><input id="to" name="to" value="{{ $prefill['to'] ?? 'Depot Manager' }}" class="{{ $in }}"></div>
                    <div><label class="{{ $lb }}" for="thru">Thru</label><input id="thru" name="thru" value="{{ $prefill['thru'] ?? 'Dispatchers' }}" class="{{ $in }}"></div>
                    <div>
                        <label class="{{ $lb }}" for="destination">Destination</label>
                        <input id="destination" name="destination" value="{{ $prefill['destination'] ?? '' }}" class="{{ $in }}" required>
                        <p class="mt-1 text-xs text-slate-500">Where the truck will deliver the fuel.</p>
                    </div>
                    <div><label class="{{ $lb }}" for="total_seals">Total seals</label><input id="total_seals" name="total_seals" value="{{ $prefill['total_seals'] ?? '' }}" class="{{ $in }}" placeholder="Example: 15 PCS"></div>
                </div>
            </section>

            <section class="rounded border border-slate-200 bg-white p-5">
                <h3 class="mb-4 font-semibold text-slate-900">Truck and driver</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $lb }}" for="plate">Plate number</label>
                        <input id="plate" name="plate" list="plates" x-model="plate" @change="pick()" class="{{ $in }}" required autocomplete="off">
                        <datalist id="plates">@foreach ($trucks as $t)<option value="{{ $t['plate'] }}"></option>@endforeach</datalist>
                        <p class="mt-1 text-xs text-slate-500">Choose a truck from the truck master.</p>
                    </div>
                    <div><label class="{{ $lb }}">Hauling company</label><input class="w-full rounded border-slate-200 bg-slate-100 text-sm text-slate-600" :value="hauler" readonly></div>

                    <fieldset class="sm:col-span-2">
                        <legend class="{{ $lb }}">Driver</legend>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-sm text-slate-800">
                                <input type="radio" value="default" x-model="driverMode" :disabled="!truck" class="text-emerald-800 focus:ring-emerald-700">
                                <span>Default driver: <strong x-text="truck ? truck.driver : 'choose a truck first'"></strong></span>
                            </label>
                            <label class="flex items-center gap-2 text-sm text-slate-800">
                                <input type="radio" value="sub" x-model="driverMode" @change="if (!subDriver && truck) subDriver = truck.substitutes[0]" :disabled="!truck || truck.substitutes.length === 0" class="text-emerald-800 focus:ring-emerald-700">
                                <span>Sub-driver (the default driver is not available)</span>
                            </label>
                            <select x-show="driverMode === 'sub'" x-cloak x-model="subDriver" class="{{ $in }} max-w-sm" aria-label="Sub-driver">
                                <template x-for="d in (truck ? truck.substitutes : [])" :key="d"><option :value="d" :selected="d === subDriver" x-text="d"></option></template>
                            </select>
                            <p x-show="truck && truck.substitutes.length === 0" x-cloak class="text-xs text-slate-500">No sub-driver is registered for this truck. Ask Operations to add one in the truck master.</p>
                        </div>
                        <input type="hidden" name="authorized" :value="driver">
                    </fieldset>

                    <div><label class="{{ $lb }}">Truck capacity (liters)</label><input class="w-full rounded border-slate-200 bg-slate-100 text-sm text-slate-600" :value="truck ? fmt(capacity) : ''" readonly></div>
                </div>

                <div x-show="truck" x-cloak class="mt-4 rounded border p-3 text-sm" :class="truck && truck.eligible ? 'border-slate-200 bg-slate-50' : 'border-red-200 bg-red-50'">
                    <p class="font-medium text-slate-800">Certificates</p>
                    <ul class="mt-1 space-y-0.5">
                        <template x-for="c in (truck ? truck.cert_list : [])" :key="c.name">
                            <li><span x-text="c.name"></span>: valid until <span x-text="c.expiry"></span> (<span class="font-medium" x-text="c.status"></span>)</li>
                        </template>
                    </ul>
                    <p x-show="truck && !truck.eligible" class="mt-2 font-medium text-red-800">This truck has an expired certificate. You can save a draft, but you cannot submit until Operations updates the truck master.</p>
                </div>
            </section>

            <section class="rounded border border-slate-200 bg-white p-5">
                <h3 class="mb-1 font-semibold text-slate-900">Products</h3>
                <p class="mb-4 text-sm text-slate-600">One SO can cover several products. The compartment allocation below fills itself from these liters.</p>
                <div class="space-y-2">
                    <template x-for="(row, i) in items" :key="i">
                        <div class="grid grid-cols-[1fr_9rem_auto] items-center gap-3">
                            <select :name="`items[${i}][product]`" x-model="row.product" @change="$nextTick(() => onItems())" class="{{ $in }}" aria-label="Product">
                                <option value="">Choose a product</option>
                                <template x-for="p in products" :key="p"><option :value="p" x-text="p"></option></template>
                            </select>
                            <input type="number" min="0" step="500" :name="`items[${i}][volume]`" x-model="row.volume" @input="$nextTick(() => onItems())" placeholder="Liters" class="{{ $in }}" aria-label="Requested liters">
                            <button type="button" @click="items.splice(i, 1); $nextTick(() => onItems())" x-show="items.length > 1" class="text-sm text-red-700 hover:underline">Remove</button>
                        </div>
                    </template>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <button type="button" @click="items.push({ product: '', volume: '' })" class="text-sm font-medium text-emerald-800 hover:underline">Add product</button>
                    <p class="text-sm font-medium text-slate-800">Total requested: <span x-text="fmt(totalRequested)"></span> liters</p>
                </div>
            </section>

            <section class="rounded border border-slate-200 bg-white p-5">
                <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="font-semibold text-slate-900">Compartment allocation</h3>
                    <button type="button" @click="manual = false; autoAlloc()" x-show="truck" x-cloak class="text-sm font-medium text-emerald-800 hover:underline">Assign automatically again</button>
                </div>
                <p class="mb-4 text-sm text-slate-600">
                    <span x-show="!manual">Filled in automatically from the product liters. You can change any compartment.</span>
                    <span x-show="manual" x-cloak>You changed this by hand, so it will not change by itself. Use "Assign automatically again" to refill it.</span>
                </p>
                <p x-show="!truck" class="text-sm text-slate-500">Choose a truck above to see its compartments.</p>
                <div class="space-y-2" x-show="truck" x-cloak>
                    <template x-for="(a, i) in alloc" :key="i">
                        <div class="grid grid-cols-[5.5rem_1fr_9rem] items-center gap-3">
                            <span class="text-sm text-slate-700" x-text="`#${i + 1} (${fmt(a.cap)} L)`"></span>
                            <select :name="`alloc[${i}][product]`" x-model="a.product" @change="manual = true" class="{{ $in }}" aria-label="Compartment product">
                                <option value="">Not used</option>
                                <template x-for="p in products" :key="p"><option :value="p" x-text="p"></option></template>
                            </select>
                            <input type="number" min="0" step="500" :name="`alloc[${i}][liters]`" x-model="a.liters" @input="manual = true" :max="a.cap" placeholder="Liters" class="{{ $in }}" aria-label="Compartment liters">
                        </div>
                    </template>
                </div>

                <div x-show="summary.length" x-cloak class="mt-5 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-slate-600"><tr><th class="pb-1 pe-4 font-medium">Product</th><th class="pb-1 pe-4 text-right font-medium">Requested</th><th class="pb-1 pe-4 text-right font-medium">Allocated</th><th class="pb-1 font-medium">Check</th></tr></thead>
                        <tbody>
                            <template x-for="s in summary" :key="s.product">
                                <tr><td class="pe-4 py-0.5" x-text="s.product"></td><td class="pe-4 py-0.5 text-right" x-text="fmt(s.req)"></td><td class="pe-4 py-0.5 text-right" x-text="fmt(s.alloc)"></td><td class="py-0.5" x-text="s.req === s.alloc ? 'Matches' : 'Does not match'"></td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="remarks.length" x-cloak class="mt-4 rounded bg-slate-50 p-3 text-sm">
                    <p class="font-medium text-slate-800">Remarks (generated from the allocation)</p>
                    <ul class="mt-1"><template x-for="r in remarks" :key="r"><li x-text="r"></li></template></ul>
                </div>
            </section>

            <section x-show="preview" x-cloak class="space-y-1 rounded border border-amber-300 bg-amber-50 p-5 text-sm text-slate-800">
                <h3 class="font-semibold text-slate-900">Preview</h3>
                <p>SO <strong x-text="soNo"></strong>, control <span x-text="controlNo"></span>, <span x-text="atlNo"></span></p>
                <p>Truck <span x-text="plate || 'not chosen'"></span>, driver <span x-text="driver || 'not chosen'"></span></p>
                <p>Total requested: <span x-text="fmt(totalRequested)"></span> liters</p>
                <ul><template x-for="r in remarks" :key="r"><li x-text="r"></li></template></ul>
            </section>

            <div x-show="errors.length" x-cloak class="rounded border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-medium">Fix these before submitting for approval (you can still save a draft):</p>
                <ul class="mt-1 list-disc ps-5"><template x-for="e in errors" :key="e"><li x-text="e"></li></template></ul>
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('dashboard') }}" class="rounded border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="button" @click="preview = !preview" class="rounded border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" x-text="preview ? 'Hide preview' : 'Preview ATL'"></button>
                <button type="submit" name="action" value="draft" formnovalidate class="rounded border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Save draft</button>
                <button type="submit" name="action" value="submit" :disabled="errors.length > 0" class="rounded bg-[#1c3d34] px-5 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c] disabled:cursor-not-allowed disabled:opacity-50">Submit for approval</button>
            </div>
        </div>
    </form>

    <script>
        function atlForm(trucks, products, prefill, taken, start) {
            const p = prefill || {};
            return {
                trucks, products, taken, preview: false, manual: false,
                soNo: start[0], controlNo: start[1], atlNo: start[2],
                plate: p.plate || '', driverMode: 'default', subDriver: '',
                items: (p.items && p.items.length) ? p.items.map(i => ({ product: i.product, volume: i.volume })) : [{ product: '', volume: '' }],
                alloc: [],
                init() { if (this.plate) this.pick(true); },
                get truck() { return this.trucks.find(t => t.plate === this.plate) || null },
                get hauler() { return this.truck ? this.truck.hauler : '' },
                get capacity() { return this.truck ? this.truck.capacity : 0 },
                get driver() { return this.driverMode === 'sub' ? this.subDriver : (this.truck ? this.truck.driver : '') },
                pick(keep) {
                    const t = this.truck;
                    if (!t) { this.alloc = []; return; }
                    if (keep && p.authorized && p.authorized !== t.driver && t.substitutes.includes(p.authorized)) {
                        this.driverMode = 'sub'; this.subDriver = p.authorized;
                    } else { this.driverMode = 'default'; this.subDriver = ''; }
                    const prev = (keep && t.plate === p.plate) ? (p.plan || []) : [];
                    if (prev.length) {
                        this.alloc = t.compartments.map((cap, i) => {
                            const old = prev.find(x => x.comp === i + 1);
                            return { cap, product: old ? old.product : '', liters: old ? old.liters : '' };
                        });
                        this.manual = true;
                    } else { this.manual = false; this.autoAlloc(); }
                },
                onItems() { if (!this.manual) this.autoAlloc(); },
                // Fills the compartments from the product liters: first looks for compartments that add up exactly, otherwise fills in order.
                autoAlloc() {
                    const t = this.truck;
                    if (!t) return;
                    const caps = t.compartments;
                    const totals = {};
                    this.items.forEach(i => { if (i.product && +i.volume > 0) totals[i.product] = (totals[i.product] || 0) + (+i.volume); });
                    const free = caps.map((c, idx) => idx);
                    const alloc = caps.map(cap => ({ cap, product: '', liters: '' }));
                    const exact = (target) => {
                        const res = [];
                        const go = (k, rem) => {
                            if (rem === 0) return true;
                            if (rem < 0 || k >= free.length) return false;
                            res.push(free[k]);
                            if (go(k + 1, rem - caps[free[k]])) return true;
                            res.pop();
                            return go(k + 1, rem);
                        };
                        return go(0, target) ? [...res] : null;
                    };
                    Object.entries(totals).forEach(([product, vol]) => {
                        let chosen = exact(vol);
                        if (!chosen) {
                            chosen = []; let need = vol;
                            for (const idx of free) { if (need <= 0) break; chosen.push(idx); need -= caps[idx]; }
                        }
                        let rem = vol;
                        chosen.forEach(idx => { const put = Math.min(rem, caps[idx]); alloc[idx].product = product; alloc[idx].liters = put; rem -= put; });
                        chosen.forEach(idx => free.splice(free.indexOf(idx), 1));
                    });
                    this.alloc = alloc;
                },
                get totalRequested() { return this.items.reduce((s, i) => s + (+i.volume || 0), 0) },
                get summary() {
                    const m = {};
                    this.items.forEach(i => { if (i.product) { m[i.product] = m[i.product] || { product: i.product, req: 0, alloc: 0 }; m[i.product].req += +i.volume || 0; } });
                    this.alloc.forEach(a => { if (a.product) { m[a.product] = m[a.product] || { product: a.product, req: 0, alloc: 0 }; m[a.product].alloc += +a.liters || 0; } });
                    return Object.values(m);
                },
                get remarks() {
                    return this.alloc.map((a, i) => (a.product && +a.liters) ? `#${i + 1} ${a.liters} - ${a.product}` : null).filter(Boolean);
                },
                get errors() {
                    const e = [];
                    if (!/^\d{4}-\d{2}-\d{4}$/.test(this.soNo)) e.push('SO number must look like 2026-10-0001.');
                    else if (this.taken.so.includes(this.soNo)) e.push('This SO number is already used on another ATL.');
                    if (!/^\d{4}-\d{3}$/.test(this.controlNo)) e.push('Control number must look like 2026-001.');
                    else if (this.taken.control.includes(this.controlNo)) e.push('This control number is already used.');
                    if (!/^ATL#\d{3}$/.test(this.atlNo)) e.push('ATL number must look like ATL#001.');
                    else if (this.taken.atl.includes(this.atlNo)) e.push('This ATL number is already used.');
                    if (!this.truck) e.push('Choose a truck from the truck master.');
                    else this.truck.issues.forEach(x => e.push('Truck cannot be used: ' + x + '.'));
                    if (this.driverMode === 'sub' && !this.subDriver) e.push('Choose a sub-driver.');
                    if (!this.items.some(i => i.product && +i.volume > 0)) e.push('Add at least one product with its liters.');
                    if (this.truck && this.totalRequested > this.capacity) e.push('Total requested liters are more than the truck capacity.');
                    this.alloc.forEach((a, i) => {
                        if (+a.liters > a.cap) e.push(`Compartment ${i + 1} is over its capacity of ${a.cap} L.`);
                        if (+a.liters > 0 && !a.product) e.push(`Compartment ${i + 1} needs a product.`);
                    });
                    this.summary.forEach(s => { if (s.req !== s.alloc) e.push(`${s.product}: requested ${s.req} L but allocated ${s.alloc} L.`); });
                    return e;
                },
                fmt(n) { return Number(n).toLocaleString() }
            }
        }
    </script>
</x-app-layout>
