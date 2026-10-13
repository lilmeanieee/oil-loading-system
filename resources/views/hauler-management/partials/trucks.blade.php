@php
        $in = 'w-full rounded border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500';
        $lb = 'mb-1 block text-sm font-medium text-slate-700';
        $badge = ['Valid' => 'bg-emerald-100 text-emerald-800', 'Expiring soon' => 'bg-amber-100 text-amber-800', 'Expired' => 'bg-red-100 text-red-800'];
        $canManage = \App\Support\Role::can('trucks.manage');
    @endphp

    <div class="py-6" x-data="truckForm()">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="max-w-2xl text-sm text-slate-600">The master list of trucks. A truck with an expired OR, CR, or calibration certificate cannot be used on a submitted ATL.</p>
                @if ($canManage)
                    <button type="button" @click="adding = !adding" class="rounded bg-amber-500 px-4 py-2 text-sm font-medium text-slate-900 hover:bg-amber-400" x-text="adding ? 'Back to the truck list' : 'Add truck'"></button>
                @endif
            </div>

            @if ($canManage)
            <form x-show="adding" x-cloak method="POST" action="#" enctype="multipart/form-data" novalidate class="space-y-6 rounded border border-slate-200 bg-white p-5" @submit.prevent="save()">
                @csrf
                <div>
                    <h3 class="mb-3 font-semibold text-slate-900">Truck</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="{{ $lb }}" for="t_plate">Plate number</label><input id="t_plate" class="{{ $in }}" required></div>
                        <div x-data="{ q: '', known: @js(collect($haulers)->pluck('name')->values()), asking: false, created: false,
                                      get exists() { return this.known.some(k => k.toLowerCase() === this.q.trim().toLowerCase()) },
                                      check() { this.created = false; this.asking = this.q.trim() !== '' && !this.exists },
                                      confirmNew() { this.known.push(this.q.trim()); this.asking = false; this.created = true } }">
                            <label class="{{ $lb }}" for="t_hauler">Truck owner or hauling company</label>
                            <input id="t_hauler" list="hauler_list" x-model="q" @change="check()" autocomplete="off" placeholder="Search or type a company name" class="{{ $in }}" required>
                            <datalist id="hauler_list"><template x-for="k in known" :key="k"><option :value="k"></option></template></datalist>
                            <div x-show="asking" x-cloak class="mt-2 rounded border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">
                                <p>"<span x-text="q.trim()"></span>" is not in the Haulers list. Create it as a new hauler and link it to this truck?</p>
                                <div class="mt-2 flex gap-2">
                                    <button type="button" @click="confirmNew()" class="rounded bg-slate-900 px-3 py-1 text-white hover:bg-slate-700">Yes, create hauler</button>
                                    <button type="button" @click="q = ''; asking = false" class="rounded border border-slate-300 bg-white px-3 py-1 text-slate-700 hover:bg-slate-50">No, choose another</button>
                                </div>
                            </div>
                            <p x-show="created" x-cloak class="mt-1 text-sm text-emerald-800">New hauler will be added to the Haulers tab when the truck is saved. (Demo only.)</p>
                        </div>
                        <div x-data="{ list: @js(collect($drivers)->map(fn ($x) => ['name' => $x['name'], 'default_for' => $x['default_for'] ?? ''])->values()),
                                      sel: '', adding: false, nd: { name: '', license: '', expiry: '', contact: '' }, reassign: false,
                                      get current() { return this.list.find(x => x.name === this.sel) },
                                      pick() { this.adding = (this.sel === '__new'); this.reassign = false },
                                      saveNew() { if (!this.nd.name.trim() || !this.nd.license.trim() || !this.nd.expiry) return; this.list.push({ name: this.nd.name.trim(), default_for: '' }); this.sel = this.nd.name.trim(); this.adding = false; this.nd = { name: '', license: '', expiry: '', contact: '' } } }">
                            <label class="{{ $lb }}" for="t_driver">Default driver</label>
                            <select id="t_driver" x-model="sel" @change="pick()" class="{{ $in }}" required>
                                <option value="">Select a driver</option>
                                <template x-for="x in list" :key="x.name"><option :value="x.name" x-text="x.name"></option></template>
                                <option value="__new">+ Add New Driver</option>
                            </select>
                            <div x-show="current && current.default_for" x-cloak class="mt-2 rounded border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">
                                <p>This driver is already the default driver of <span class="font-medium" x-text="current && current.default_for"></span>.</p>
                                <label class="mt-1 flex items-center gap-2"><input type="checkbox" x-model="reassign" class="rounded border-slate-300"> Reassign the driver to this truck (the other truck will have no default driver)</label>
                            </div>
                            <div x-show="adding" x-cloak class="mt-2 grid gap-2 rounded border border-slate-200 bg-slate-50 p-3 sm:grid-cols-2">
                                <input x-model="nd.name" placeholder="Full name" aria-label="New driver name" class="{{ $in }}">
                                <input x-model="nd.contact" placeholder="Contact number" aria-label="New driver contact" class="{{ $in }}">
                                <input x-model="nd.license" placeholder="License number" aria-label="New driver license number" class="{{ $in }}">
                                <input x-model="nd.expiry" type="date" aria-label="License expiry date" class="{{ $in }}">
                                <div class="flex gap-2 sm:col-span-2">
                                    <button type="button" @click="saveNew()" class="rounded bg-slate-900 px-3 py-1 text-sm text-white hover:bg-slate-700">Save driver and select</button>
                                    <button type="button" @click="adding = false; sel = ''" class="rounded border border-slate-300 bg-white px-3 py-1 text-sm text-slate-700 hover:bg-slate-50">Cancel</button>
                                </div>
                                <p class="text-xs text-slate-500 sm:col-span-2">The new driver is saved to the Drivers tab and set as this truck's default driver. (Demo only.)</p>
                            </div>
                        </div>
                        <div><label class="{{ $lb }}" for="t_subs">Authorized substitute drivers (one per line)</label><textarea id="t_subs" rows="2" class="{{ $in }}"></textarea></div>
                        <div><label class="{{ $lb }}" for="t_total">Total capacity (liters)</label><input id="t_total" type="number" min="0" x-model="total" class="{{ $in }}" required></div>
                        <div>
                            <label class="{{ $lb }}" for="t_count">Number of compartments</label>
                            <input id="t_count" type="number" min="1" max="12" x-model="count" @input="setCount()" placeholder="Example: 6" class="{{ $in }}">
                            <p x-show="touched && countError" x-cloak class="mt-1 text-sm text-red-700" x-text="countError"></p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="mb-1 font-semibold text-slate-900">Compartment capacities</h3>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                        <template x-for="(c, i) in comps" :key="i">
                            <div><label class="mb-1 block text-xs text-slate-600" x-text="`Compartment ${i + 1}`"></label><input type="number" min="0" x-model="c.cap" class="{{ $in }}"></div>
                        </template>
                    </div>
                    <p x-show="comps.length" x-cloak class="mt-2 text-sm text-slate-700">Sum of compartments: <span x-text="fmt(sum)"></span> liters</p>
                    <p x-show="mismatch" x-cloak class="mt-1 rounded bg-amber-50 px-3 py-2 text-sm text-amber-800">The compartments add up to <span x-text="fmt(sum)"></span> L but the total capacity is <span x-text="fmt(total)"></span> L. Fix one of them before saving.</p>
                </div>

                <div>
                    <h3 class="mb-3 font-semibold text-slate-900">Certificates and documents</h3>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-slate-800">Official Receipt (OR)</p>
                            <input class="{{ $in }}" placeholder="OR number" aria-label="OR number">
                            <div><label class="{{ $lb }}">Expiry date</label><input type="date" class="{{ $in }}"></div>
                            <input type="file" class="block w-full text-sm text-slate-600" aria-label="OR file">
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-slate-800">Certificate of Registration (CR)</p>
                            <input class="{{ $in }}" placeholder="CR number" aria-label="CR number">
                            <div><label class="{{ $lb }}">Expiry date</label><input type="date" class="{{ $in }}"></div>
                            <input type="file" class="block w-full text-sm text-slate-600" aria-label="CR file">
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-slate-800">Calibration certificate</p>
                            <input class="{{ $in }}" placeholder="Certificate number" aria-label="Calibration number">
                            <div><label class="{{ $lb }}">Date issued</label><input type="date" x-model="calIssued" @change="setExpiry()" class="{{ $in }}"></div>
                            <div><label class="{{ $lb }}">Expiry date (one year after issue)</label><input type="date" x-model="calExpiry" class="{{ $in }}"></div>
                            <input type="file" class="block w-full text-sm text-slate-600" aria-label="Calibration file">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="adding = false" class="rounded border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Save truck</button>
                </div>
            </form>
            @endif

            <div x-show="!adding" class="overflow-x-auto rounded border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr><th class="px-4 py-3 font-medium">Plate</th><th class="px-4 py-3 font-medium">Hauling company</th><th class="px-4 py-3 font-medium">Default driver</th><th class="px-4 py-3 font-medium">Substitutes</th><th class="px-4 py-3 text-right font-medium">Capacity (L)</th><th class="px-4 py-3 font-medium">Compartments</th><th class="px-4 py-3 font-medium">Certificates</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($trucks as $t)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $t['plate'] }}</td>
                                <td class="px-4 py-3">{{ $t['hauler'] }}</td>
                                <td class="px-4 py-3">{{ $t['driver'] }}</td>
                                <td class="px-4 py-3">{{ $t['substitutes'] ? implode(', ', $t['substitutes']) : 'None' }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($t['capacity']) }}</td>
                                <td class="px-4 py-3">{{ count($t['compartments']) }} ({{ implode(', ', array_map(fn ($c) => number_format($c), $t['compartments'])) }})</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($t['cert_list'] as $c)
                                            <span class="whitespace-nowrap rounded px-2 py-0.5 text-xs font-medium {{ $badge[$c['status']] }}" title="Expires {{ $c['expiry'] }}">{{ $c['name'] }}: {{ $c['status'] }}</span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function truckForm() {
            return {
                adding: false, touched: false, total: '', count: '', comps: [], calIssued: '', calExpiry: '',
                setCount() {
                    const n = parseInt(this.count, 10);
                    if (!(n >= 1 && n <= 12)) { this.comps = []; return; }
                    const next = [];
                    for (let i = 0; i < n; i++) next.push(this.comps[i] || { cap: '' });
                    this.comps = next;
                },
                get countError() {
                    if (this.count === '' || this.count === null) return 'Enter the number of compartments.';
                    const n = parseInt(this.count, 10);
                    return (n >= 1 && n <= 12) ? '' : 'Enter a number from 1 to 12.';
                },
                get sum() { return this.comps.reduce((s, c) => s + (+c.cap || 0), 0) },
                get mismatch() { return this.total !== '' && this.comps.length > 0 && +this.total !== this.sum },
                setExpiry() {
                    if (!this.calIssued) return;
                    const d = new Date(this.calIssued);
                    d.setFullYear(d.getFullYear() + 1);
                    this.calExpiry = d.toISOString().slice(0, 10);
                },
                save() {
                    this.touched = true;
                    if (this.countError || this.mismatch) return;
                    alert('Demo only: the backend will save trucks.');
                    this.adding = false;
                },
                fmt(n) { return Number(n).toLocaleString() }
            }
        }
    </script>
