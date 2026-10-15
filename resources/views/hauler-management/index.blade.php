<x-app-layout>
    @php
        $in = 'w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700';
        $lb = 'mb-1 block text-sm font-medium text-slate-700';
        $th = 'px-4 py-3 font-medium';
    @endphp

    <x-slot name="header">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Hauler Management</h2>
                <p class="mt-1 text-sm text-slate-600">Keep hauler, truck and driver records current. Drivers are added from the truck form. ATL checks read from here.</p>
            </div>
            <span class="hidden text-sm text-slate-500 sm:block">{{ now()->format('D d M Y') }}</span>
        </div>
    </x-slot>

    @include('operations._boot')

    <div class="py-6" x-data="opsApp(window.OPS_SEED, { tabs: @js($tabs), canManage: @js($canManage) })">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <nav class="flex flex-wrap gap-2 rounded-lg border border-slate-200 bg-white p-2" role="tablist" aria-label="Hauler management sections">
                    <template x-for="t in hTabs()" :key="t[0]">
                        <button type="button" role="tab" @click="setHv(t[0])" :aria-selected="hv === t[0]"
                                :class="hv === t[0] ? 'bg-[#1c3d34] text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100'"
                                class="rounded-lg px-4 py-2 text-sm font-medium"><span x-text="t[1]"></span> <span class="ml-1 text-xs opacity-70" x-text="'(' + t[2] + ')'"></span></button>
                    </template>
                </nav>
                <input type="search" x-model="hq" :placeholder="'Search ' + hv + '...'" aria-label="Search records" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700 sm:w-72">
            </div>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-3">
                    <h3 class="font-semibold text-slate-900" x-text="{ haulers: 'Haulers', trucks: 'Trucks', drivers: 'Drivers' }[hv]"></h3>
                    <button type="button" x-show="canManage && hv !== 'drivers'" @click="openForm({ haulers: 'hauler', trucks: 'truck', drivers: 'driver' }[hv])" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]" x-text="{ haulers: 'Add hauler', trucks: 'Register truck', drivers: 'Add driver' }[hv]"></button>
                </div>

                {{-- Haulers --}}
                <div x-show="hv === 'haulers'" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="{{ $th }}">Hauler</th><th class="{{ $th }}">Contact</th><th class="{{ $th }}">Phone</th><th class="{{ $th }}">Address</th><th class="{{ $th }}">Trucks</th><th class="{{ $th }}"></th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="h in fHaulers" :key="h.id">
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900" x-text="h.name"></td>
                                    <td class="px-4 py-3" x-text="h.contact"></td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs" x-text="h.phone"></td>
                                    <td class="px-4 py-3" x-text="h.addr"></td>
                                    <td class="px-4 py-3" x-text="truckCount(h.id)"></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right"><button type="button" @click="openForm('hauler', h.id, true)" class="font-medium text-slate-700 hover:underline">View</button><button type="button" x-show="canManage" @click="openForm('hauler', h.id)" class="ml-3 font-medium text-emerald-800 hover:underline">Edit</button></td>
                                </tr>
                            </template>
                            <tr x-show="!fHaulers.length"><td colspan="6" class="px-4 py-8 text-center text-slate-500">No haulers match.</td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- Trucks --}}
                <div x-show="hv === 'trucks'" x-cloak class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="{{ $th }}">Plate</th><th class="{{ $th }}">Hauler</th><th class="{{ $th }}">Driver</th><th class="{{ $th }}">Substitute</th><th class="{{ $th }} text-right">Capacity</th><th class="{{ $th }}">Compartments</th><th class="{{ $th }}">Documents</th><th class="{{ $th }}"></th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="t in fTrucks" :key="t.plate">
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono font-semibold text-slate-900" x-text="t.plate"></td>
                                    <td class="px-4 py-3" x-text="hauler(t.h).name"></td>
                                    <td class="px-4 py-3" x-text="t.d ? driver(t.d).name : 'None'"></td>
                                    <td class="px-4 py-3" x-text="t.s ? driver(t.s).name : 'None'"></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right" x-text="fmtL(cap(t))"></td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs" x-text="t.comps.map(c => (c / 1000) + 'k').join(' · ')"></td>
                                    <td class="px-4 py-3"><div class="flex flex-wrap gap-1"><template x-for="c in truckCerts(t)" :key="c.label"><span :class="pill(c.k)" :title="c.t" x-text="c.label"></span></template></div></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right"><button type="button" @click="openForm('truck', t.plate, true)" class="font-medium text-slate-700 hover:underline">View</button><button type="button" x-show="canManage" @click="openForm('truck', t.plate)" class="ml-3 font-medium text-emerald-800 hover:underline">Edit</button></td>
                                </tr>
                            </template>
                            <tr x-show="!fTrucks.length"><td colspan="8" class="px-4 py-8 text-center text-slate-500">No trucks match.</td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- Drivers --}}
                <div x-show="hv === 'drivers'" x-cloak class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="{{ $th }}">Driver</th><th class="{{ $th }}">Hauler</th><th class="{{ $th }}">Truck Plate No.</th><th class="{{ $th }}">Phone</th><th class="{{ $th }}"></th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="x in fDrivers" :key="x.id">
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900" x-text="x.name"></td>
                                    <td class="px-4 py-3" x-text="hauler(x.h).name"></td>
                                    <td class="px-4 py-3 text-xs" x-text="driverRole(x)"></td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs" x-text="x.phone || '-'"></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right"><button type="button" @click="openForm('driver', x.id, true)" class="font-medium text-slate-700 hover:underline">View</button><button type="button" x-show="canManage" @click="openForm('driver', x.id)" class="ml-3 font-medium text-emerald-800 hover:underline">Edit</button></td>
                                </tr>
                            </template>
                            <tr x-show="!fDrivers.length"><td colspan="5" class="px-4 py-8 text-center text-slate-500">No drivers match.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- View / add / edit form --}}
        <div x-show="hf" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 p-4" @click.self="closeForm()">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white shadow-xl" role="dialog" aria-modal="true">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                    <h3 class="font-semibold text-slate-900" x-text="hf ? ((hf.ro ? 'View ' : (hf.id ? 'Edit ' : (hf.kind === 'truck' ? 'Register ' : 'Add '))) + hf.kind) : ''"></h3>
                    <button type="button" @click="closeForm()" class="rounded-lg border border-slate-300 px-3 py-1 text-sm text-slate-700 hover:bg-slate-50">Close</button>
                </div>

                <template x-if="hf">
                    <div class="space-y-4 px-5 py-4">
                        <fieldset :disabled="hf.ro" class="space-y-4">
                            {{-- Hauler --}}
                            <template x-if="hf.kind === 'hauler'">
                                <div class="space-y-3">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div><label class="{{ $lb }}" for="f_name">Company name <span class="text-red-600">*</span></label><input id="f_name" x-model="hf.name" class="{{ $in }}"></div>
                                        <div><label class="{{ $lb }}" for="f_contact">Contact person</label><input id="f_contact" x-model="hf.contact" class="{{ $in }}"></div>
                                        <div><label class="{{ $lb }}" for="f_phone">Contact number</label><input id="f_phone" type="tel" inputmode="numeric" placeholder="09XXXXXXXXX" x-model="hf.phone" @input="hf.phone = (hf.phone || '').replace(/[^0-9]/g, '').slice(0, 11)" class="{{ $in }}"></div>
                                    </div>
                                    <div><label class="{{ $lb }}" for="f_addr">Company address <span class="text-red-600">*</span></label><input id="f_addr" x-model="hf.addr" class="{{ $in }}"></div>
                                </div>
                            </template>

                            {{-- Driver (edit or view only, new drivers are added from the truck form) --}}
                            <template x-if="hf.kind === 'driver'">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div><label class="{{ $lb }}" for="f_dname">Full name</label><input id="f_dname" x-model="hf.name" class="{{ $in }}"></div>
                                    <div><label class="{{ $lb }}">Hauler</label><input :value="hauler(hf.h).name" disabled class="{{ $in }} bg-slate-50"></div>
                                    <div class="sm:col-span-2"><label class="{{ $lb }}">Truck Plate No.</label><input :value="driverRole(driver(hf.id) )" disabled class="{{ $in }} bg-slate-50"></div>
                                    <div><label class="{{ $lb }}" for="f_dphone">Contact number</label><input id="f_dphone" type="tel" inputmode="numeric" placeholder="09XXXXXXXXX" x-model="hf.phone" @input="hf.phone = (hf.phone || '').replace(/[^0-9]/g, '').slice(0, 11)" class="{{ $in }}"></div>
                                </div>
                            </template>

                            {{-- Truck --}}
                            <template x-if="hf.kind === 'truck'">
                                <div class="space-y-4">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div><label class="{{ $lb }}" for="f_plate">Plate number</label><input id="f_plate" x-model="hf.plate" :readonly="!!hf.id" class="{{ $in }}" :class="hf.id ? 'bg-slate-50' : ''"></div>
                                        <div>
                                            <label class="{{ $lb }}" for="f_th">Hauler</label>
                                            <input id="f_th" list="hauler_list" x-model="hf.hname" autocomplete="off" placeholder="Select or type a company name" class="{{ $in }}">
                                            <datalist id="hauler_list"><template x-for="h in d.haulers" :key="h.id"><option :value="h.name"></option></template></datalist>
                                            <span x-show="isNewHauler" x-cloak class="mt-1 inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-300">New hauler</span>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="{{ $lb }}" for="f_td">Driver (full name)</label>
                                            <input id="f_td" x-model="hf.dname" autocomplete="off" placeholder="Full name" class="{{ $in }}">
                                            <p class="mt-1 text-xs text-slate-500">Each driver is assigned to one truck.</p>
                                        </div>
                                    </div>

                                    <div class="rounded-lg border border-slate-200 p-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-medium text-slate-700">Substitute driver <span class="font-normal text-slate-500">(optional)</span></p>
                                            <button type="button" x-show="!hf.ro && !hf.showSub" @click="hf.showSub = true" class="rounded-lg border border-slate-300 px-3 py-1 text-sm text-slate-700 hover:bg-slate-50">Add substitute driver</button>
                                            <button type="button" x-show="!hf.ro && hf.showSub" @click="hf.showSub = false; hf.sname = ''" class="text-sm font-medium text-red-700 hover:underline">Remove</button>
                                        </div>
                                        <div x-show="hf.showSub" x-cloak class="mt-2"><label class="{{ $lb }}" for="f_sd">Substitute driver (full name)</label><input id="f_sd" x-model="hf.sname" autocomplete="off" placeholder="Full name" class="{{ $in }}"></div>
                                        <p x-show="hf.ro && !hf.showSub" class="mt-1 text-sm text-slate-500">None</p>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div><label class="{{ $lb }}" for="f_cap">Total capacity (liters)</label><input id="f_cap" type="number" min="1" step="1" inputmode="numeric" x-model="hf.cap" class="{{ $in }}"></div>
                                            <div><label class="{{ $lb }}" for="f_cn">Number of compartments</label><input id="f_cn" type="number" min="1" max="12" step="1" inputmode="numeric" x-model="hf.cn" @input="setCount()" class="{{ $in }}"></div>
                                        </div>
                                        <div x-show="hf.cl.length" x-cloak>
                                            <p class="{{ $lb }}">Liters per compartment</p>
                                            <div class="grid gap-3 sm:grid-cols-3">
                                                <template x-for="(c, i) in hf.cl" :key="i">
                                                    <div>
                                                        <label class="mb-1 block text-xs text-slate-600" :for="'f_c' + i" x-text="'Compartment ' + (i + 1)"></label>
                                                        <input :id="'f_c' + i" type="number" min="1" step="1" inputmode="numeric" x-model="hf.cl[i]" class="{{ $in }}" :class="compErr(i) ? 'border-red-500' : ''">
                                                        <p class="mt-0.5 text-xs text-red-700" x-text="compErr(i)"></p>
                                                    </div>
                                                </template>
                                            </div>
                                            <p class="mt-2 text-xs" :class="remaining === 0 ? 'text-emerald-800' : remaining < 0 ? 'text-red-700' : 'text-slate-600'" x-text="'Assigned ' + fmtL(assigned) + ' of ' + fmtL(num(hf.cap)) + (remaining > 0 ? ', ' + fmtL(remaining) + ' left to assign.' : remaining < 0 ? ', over by ' + fmtL(-remaining) + '.' : '. Matches the capacity.')"></p>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="{{ $lb }}">Certificates and documents</p>
                                        <div class="space-y-3">
                                            <template x-for="c in DOCS" :key="c[0]">
                                                <div class="grid gap-3 rounded-lg border border-slate-200 p-3 sm:grid-cols-2">
                                                    <div><label class="mb-1 block text-sm text-slate-700" :for="'f_e_' + c[0]" x-text="c[1] + ' expiry date'"></label><input :id="'f_e_' + c[0]" type="date" x-model="hf.docs[c[0]].exp" :min="minExpiry" class="{{ $in }}"></div>
                                                    <div>
                                                        <label class="mb-1 block text-sm text-slate-700" :for="'f_u_' + c[0]" x-text="c[2] + ' file upload'"></label>
                                                        <input :id="'f_u_' + c[0]" type="file" accept=".pdf,.jpg,.jpeg,.png" @change="pickFile(c[0], $event)" class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                                                        <p class="mt-1 text-xs text-slate-500" x-text="hf.docs[c[0]].file ? 'File: ' + hf.docs[c[0]].file : 'No file uploaded'"></p>
                                                        <a x-show="hf.docs[c[0]].url" :href="hf.docs[c[0]].url" target="_blank" class="mt-1 inline-block text-xs font-medium text-emerald-800 hover:underline">View saved file</a>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </fieldset>

                        <p class="text-sm text-red-700" x-text="hErr"></p>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="closeForm()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" x-text="hf.ro ? 'Close' : 'Cancel'"></button>
                            <button type="button" x-show="hf.ro && canManage" @click="hf.ro = false" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">Edit</button>
                            <button type="button" x-show="!hf.ro" @click="saveForm()" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]" x-text="hf.kind === 'truck' ? (hf.id ? 'Save truck' : 'Save truck') : 'Save ' + hf.kind"></button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Confirm new hauler (opens after Save truck when the hauler is new) --}}
        <div x-show="hc" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" role="alertdialog" aria-modal="true" aria-labelledby="nh_title">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white shadow-xl">
                <div class="border-b border-slate-200 px-5 py-3">
                    <h3 id="nh_title" class="font-semibold text-slate-900">Register new hauler</h3>
                    <p class="mt-1 text-sm text-slate-600">This company is not on file yet. Confirm its details to create the hauler and save the truck.</p>
                </div>
                <template x-if="hc">
                    <div class="space-y-3 px-5 py-4">
                        <div><label class="{{ $lb }}" for="nh_name">Company name <span class="text-red-600">*</span></label><input id="nh_name" x-model="hc.name" class="{{ $in }}"></div>
                        <div><label class="{{ $lb }}" for="nh_addr">Company address <span class="text-red-600">*</span></label><input id="nh_addr" x-model="hc.addr" class="{{ $in }}"></div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div><label class="{{ $lb }}" for="nh_contact">Contact person</label><input id="nh_contact" x-model="hc.contact" class="{{ $in }}"></div>
                            <div><label class="{{ $lb }}" for="nh_phone">Contact number</label><input id="nh_phone" type="tel" inputmode="numeric" placeholder="09XXXXXXXXX" x-model="hc.phone" @input="hc.phone = (hc.phone || '').replace(/[^0-9]/g, '').slice(0, 11)" class="{{ $in }}"></div>
                        </div>
                        <p class="text-sm text-red-700" x-text="cErr"></p>
                        <div class="flex flex-wrap justify-end gap-2">
                            <button type="button" @click="hc = null; cErr = ''" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Go back and Edit</button>
                            <button type="button" @click="confirmNewHauler()" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">Create hauler and save truck</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        @include('operations._toast')
    </div>
</x-app-layout>
