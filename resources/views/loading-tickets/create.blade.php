<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Create loading ticket for {{ $atl['atl_no'] }}</h2>
    </x-slot>

    @php
        $in = 'w-full rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700';
        $ro = 'w-full rounded border-slate-200 bg-slate-100 text-sm text-slate-600';
        $lb = 'mb-1 block text-sm font-medium text-slate-700';
        $cell = 'w-20 rounded border-slate-300 px-1 py-1 text-center text-sm focus:border-emerald-700 focus:ring-emerald-700';
        $safety = ['Properly parked', 'Wheel chokes', 'Grounding wire', 'Valves closed', 'Fire extinguisher'];
        $docs = ['Order slip and/or ATL', 'Calibration certificate', 'Fire permit'];
        $ppe = ['Safety shoes', 'Hard hat', 'Gloves (cotton and nitrile)', 'Long-sleeve shirt or jacket', 'Fall protection harness', 'Respirator'];
    @endphp

    <form method="POST" action="{{ route('loading-tickets.store') }}" class="py-6" data-offline="queue" data-offline-label="Loading ticket" data-offline-ref="plate" x-data="ltForm(@js($atl), @js($trucks), @js($codes))">
        @csrf
        <input type="hidden" name="atl_id" value="{{ $atl['id'] }}">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="rounded border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                Type the actual liters loaded in each compartment, then confirm them.
            </div>

            <section class="rounded border border-slate-200 bg-white p-5">
                <h3 class="mb-4 font-semibold text-slate-900">Ticket and truck</h3>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label class="{{ $lb }}">Loading ticket number (automatic)</label><input class="{{ $ro }}" value="Assigned when saved" readonly></div>
                    <div><label class="{{ $lb }}">Date (automatic)</label><input class="{{ $ro }}" value="{{ now()->format('M. d, Y') }}" readonly></div>
                    <div><label class="{{ $lb }}">Authority to load</label><input class="{{ $ro }}" value="{{ $atl['atl_no'] }} (control {{ $atl['control_no'] }})" readonly></div>

                    <div>
                        <label class="{{ $lb }}" for="plate">Plate number (from truck master)</label>
                        <input id="plate" name="plate" list="plates" x-model="plate" @change="pick()" class="{{ $in }}" required autocomplete="off">
                        <datalist id="plates">@foreach ($trucks as $t)<option value="{{ $t['plate'] }}"></option>@endforeach</datalist>
                    </div>
                    <div><label class="{{ $lb }}" for="account">Name of account</label><input id="account" name="account" x-model="account" class="{{ $in }}" required></div>
                    <div><label class="{{ $lb }}" for="hauler">Name of hauler</label><input id="hauler" name="hauler" x-model="hauler" class="{{ $in }}" required></div>
                    <div><label class="{{ $lb }}" for="driver">Name of driver</label><input id="driver" name="driver" x-model="driver" class="{{ $in }}" required></div>
                    <div><label class="{{ $lb }}" for="ccn">CCN</label><input id="ccn" name="ccn" class="{{ $in }}"></div>
                    <div><label class="{{ $lb }}" for="ic_no">IC number</label><input id="ic_no" name="ic_no" class="{{ $in }}"></div>
                </div>
            </section>

            <section class="rounded border border-slate-200 bg-white p-5">
                <h3 class="mb-1 font-semibold text-slate-900">Compartments</h3>
                <p class="mb-4 text-sm text-slate-600">Filled in from the ATL plan. Choose X for a compartment that is not used.</p>
                <div class="overflow-x-auto">
                    <table class="min-w-[1000px] text-sm">
                        <tbody>
                            <tr>
                                <th class="pe-3 text-left font-medium text-slate-600">Compartment</th>
                                <template x-for="(c, i) in comps" :key="i"><th class="px-1 pb-1 text-center font-medium text-slate-600" x-text="i + 1"></th></template>
                            </tr>
                            <tr>
                                <th class="pe-3 text-left font-medium text-slate-600">Capacity</th>
                                <template x-for="(c, i) in comps" :key="i"><td class="px-1 pb-1"><input type="number" min="0" :name="`comps[${i}][cap]`" x-model="c.cap" class="{{ $cell }}" aria-label="Capacity"></td></template>
                            </tr>
                            <tr>
                                <th class="pe-3 text-left font-medium text-slate-600">Product</th>
                                <template x-for="(c, i) in comps" :key="i">
                                    <td class="px-1 pb-1">
                                        <select :name="`comps[${i}][product]`" x-model="c.product" @change="setX(c)" class="{{ $cell }}" aria-label="Product">
                                            <option>R95</option><option>R91</option><option>ADO</option><option>X</option>
                                        </select>
                                    </td>
                                </template>
                            </tr>
                            <tr>
                                <th class="pe-3 text-left font-medium text-slate-600">Planned liters</th>
                                <template x-for="(c, i) in comps" :key="i"><td class="px-1 pb-1"><input type="number" :name="`comps[${i}][vol]`" :value="c.vol" readonly class="{{ $cell }} bg-slate-100" aria-label="Planned liters"></td></template>
                            </tr>
                            <tr>
                                <th class="pe-3 text-left font-medium text-slate-600">Actual liters</th>
                                <template x-for="(c, i) in comps" :key="i"><td class="px-1 pb-1"><input type="number" min="0" :name="`comps[${i}][act]`" x-model="c.act" :disabled="c.product === 'X'" class="{{ $cell }} disabled:bg-slate-100" aria-label="Actual liters"></td></template>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <dl class="mt-5 grid max-w-md grid-cols-[1fr_auto] gap-y-1 text-sm">
                    <dt class="text-slate-600">Unleaded R91 (actual)</dt><dd class="text-right" x-text="fmt(act('R91'))"></dd>
                    <dt class="text-slate-600">Premium R95 (actual)</dt><dd class="text-right" x-text="fmt(act('R95'))"></dd>
                    <dt class="text-slate-600">Biodiesel ADO (actual)</dt><dd class="text-right" x-text="fmt(act('ADO'))"></dd>
                    <dt class="border-t border-slate-200 pt-1 font-medium text-slate-900">Total actual volume</dt><dd class="border-t border-slate-200 pt-1 text-right font-medium text-slate-900" x-text="fmt(actTotal)"></dd>
                </dl>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded border border-slate-200 bg-white p-5">
                    <h3 class="mb-4 font-semibold text-slate-900">Seals</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="{{ $lb }}" for="comp_seals">Compartment seals</label><input id="comp_seals" type="number" min="0" name="comp_seals" x-model="compSeals" class="{{ $in }}"></div>
                        <div><label class="{{ $lb }}" for="valve_box">Valve box seals</label><input id="valve_box" type="number" min="0" name="valve_box" x-model="valveBox" class="{{ $in }}"></div>
                        <div><label class="{{ $lb }}" for="seal_start">Seal number, start</label><input id="seal_start" name="seal_start" class="{{ $in }}"></div>
                        <div><label class="{{ $lb }}" for="seal_end">Seal number, end</label><input id="seal_end" name="seal_end" class="{{ $in }}"></div>
                        <div><label class="{{ $lb }}">Total seals</label><input class="{{ $ro }}" :value="sealsTotal" readonly></div>
                        <div><label class="{{ $lb }}" for="ethanol">Ethanol</label><input id="ethanol" name="ethanol" class="{{ $in }}"></div>
                    </div>
                </div>

                <div class="rounded border border-slate-200 bg-white p-5">
                    <h3 class="mb-2 font-semibold text-slate-900">Loading safety checklist</h3>
                    <table class="w-full">
                        <thead><tr class="text-left text-xs text-slate-500"><th class="font-normal"></th><th class="px-3 text-center font-normal">Yes</th><th class="px-3 text-center font-normal">No</th></tr></thead>
                        <tbody>@foreach ($safety as $i => $s)<x-yes-no :name="'safety['.$i.']'" :label="$s" />@endforeach</tbody>
                    </table>
                    <div class="mt-3"><label class="{{ $lb }}" for="checked_by">Checked by (CCM/RJC)</label><input id="checked_by" name="checked_by" class="{{ $in }}"></div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded border border-slate-200 bg-white p-5">
                    <h3 class="mb-2 font-semibold text-slate-900">Documents the driver must have</h3>
                    <table class="w-full">
                        <thead><tr class="text-left text-xs text-slate-500"><th class="font-normal"></th><th class="px-3 text-center font-normal">Yes</th><th class="px-3 text-center font-normal">No</th></tr></thead>
                        <tbody>@foreach ($docs as $i => $d)<x-yes-no :name="'docs['.$i.']'" :label="$d" />@endforeach</tbody>
                    </table>
                </div>
                <div class="rounded border border-slate-200 bg-white p-5">
                    <h3 class="mb-2 font-semibold text-slate-900">Personal protective equipment</h3>
                    <table class="w-full">
                        <thead><tr class="text-left text-xs text-slate-500"><th class="font-normal"></th><th class="px-3 text-center font-normal">Yes</th><th class="px-3 text-center font-normal">No</th></tr></thead>
                        <tbody>@foreach ($ppe as $i => $p)<x-yes-no :name="'ppe['.$i.']'" :label="$p" />@endforeach</tbody>
                    </table>
                </div>
            </section>

            <section class="rounded border border-slate-200 bg-white p-5">
                <h3 class="mb-4 font-semibold text-slate-900">Signatures</h3>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div><label class="{{ $lb }}">Prepared by (operating assistant)</label><input class="{{ $ro }}" value="{{ Auth::user()->name }}" readonly></div>
                    <div><label class="{{ $lb }}">Loaded by (driver)</label><input class="{{ $ro }}" :value="driver" readonly></div>
                    <div><label class="{{ $lb }}">Accepted by (driver)</label><input class="{{ $ro }}" :value="driver" readonly></div>
                    <div></div>
                    <div><label class="{{ $lb }}" for="insp1">Inspected by, operator or OA</label><input id="insp1" name="inspected[]" class="{{ $in }}"></div>
                    <div><label class="{{ $lb }}" for="insp2">Inspected by, operator or OA</label><input id="insp2" name="inspected[]" class="{{ $in }}"></div>
                    <div><label class="{{ $lb }}" for="insp3">Inspected by, TS</label><input id="insp3" name="inspected[]" class="{{ $in }}"></div>
                </div>
            </section>

            <div class="flex justify-end gap-3">
                <a href="{{ route('dashboard') }}" class="rounded border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" name="action" value="save" class="rounded border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Save ticket</button>
                <button type="submit" name="action" value="confirm" class="rounded bg-[#1c3d34] px-5 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">Confirm actual liters</button>
            </div>
        </div>
    </form>

    <script>
        function ltForm(atl, trucks, codes) {
            const blank = () => ({ cap: '', product: 'X', vol: 0, act: '' });
            const pad = list => list.concat(Array.from({ length: Math.max(0, 12 - list.length) }, blank)).slice(0, 12);
            const fromPlan = (atl.plan || []).map(p => ({ cap: p.liters, product: codes[p.product] || 'X', vol: p.liters, act: '' }));
            return {
                trucks, plate: atl.plate || '', account: atl.hauler || '', hauler: atl.hauler || '', driver: atl.authorized || '',
                comps: pad(fromPlan), compSeals: '', valveBox: 1,
                pick() {
                    const t = this.trucks.find(t => t.plate === this.plate);
                    if (!t) return;
                    this.hauler = t.hauler; this.driver = t.driver;
                    if (!(atl.plan || []).length) this.comps = pad(t.compartments.map(c => ({ cap: c, product: 'X', vol: 0, act: '' })));
                },
                setX(c) { if (c.product === 'X') c.vol = 0; else if (!c.vol) c.vol = c.cap; },
                act(code) { return this.comps.filter(c => c.product === code).reduce((s, c) => s + (+c.act || 0), 0) },
                get actTotal() { return this.act('R95') + this.act('R91') + this.act('ADO') },
                get sealsTotal() { return (+this.compSeals || 0) + (+this.valveBox || 0) },
                fmt(n) { return Number(n).toLocaleString() }
            }
        }
    </script>
</x-app-layout>
