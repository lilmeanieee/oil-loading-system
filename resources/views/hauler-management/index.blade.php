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
                <p class="mt-1 text-sm text-slate-600">Keep hauler, truck and driver records current. ATL checks read from here.</p>
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
                    <button type="button" x-show="canManage" @click="openForm({ haulers: 'hauler', trucks: 'truck', drivers: 'driver' }[hv])" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]" x-text="{ haulers: 'Add hauler', trucks: 'Register truck', drivers: 'Add driver' }[hv]"></button>
                </div>

                {{-- Haulers --}}
                <div x-show="hv === 'haulers'" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="{{ $th }}">Hauler</th><th class="{{ $th }}">Contact</th><th class="{{ $th }}">Phone</th><th class="{{ $th }}">Address</th><th class="{{ $th }}">Trucks</th><th class="{{ $th }}">Status</th><th class="{{ $th }}"></th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="h in fHaulers" :key="h.id">
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900" x-text="h.name"></td>
                                    <td class="px-4 py-3" x-text="h.contact"></td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs" x-text="h.phone"></td>
                                    <td class="px-4 py-3" x-text="h.addr"></td>
                                    <td class="px-4 py-3" x-text="truckCount(h.id)"></td>
                                    <td class="px-4 py-3"><span :class="pill(h.active ? 'ok' : '')" x-text="h.active ? 'Active' : 'Inactive'"></span></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right"><button type="button" x-show="canManage" @click="openForm('hauler', h.id)" class="font-medium text-emerald-800 hover:underline">Edit</button></td>
                                </tr>
                            </template>
                            <tr x-show="!fHaulers.length"><td colspan="7" class="px-4 py-8 text-center text-slate-500">No haulers match.</td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- Trucks --}}
                <div x-show="hv === 'trucks'" x-cloak class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="{{ $th }}">Plate</th><th class="{{ $th }}">Hauler</th><th class="{{ $th }}">Default driver</th><th class="{{ $th }} text-right">Capacity</th><th class="{{ $th }}">Compartments</th><th class="{{ $th }}">Certificates</th><th class="{{ $th }}"></th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="t in fTrucks" :key="t.plate">
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono font-semibold text-slate-900" x-text="t.plate"></td>
                                    <td class="px-4 py-3" x-text="hauler(t.h).name"></td>
                                    <td class="px-4 py-3" x-text="t.d ? driver(t.d).name : 'None'"></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right" x-text="fmtL(cap(t))"></td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs" x-text="t.comps.map(c => (c / 1000) + 'k').join(' · ')"></td>
                                    <td class="px-4 py-3"><div class="flex flex-wrap gap-1"><template x-for="c in truckCerts(t)" :key="c.label"><span :class="pill(c.k)" :title="c.t" x-text="c.label"></span></template></div></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right"><button type="button" x-show="canManage" @click="openForm('truck', t.plate)" class="font-medium text-emerald-800 hover:underline">Edit</button></td>
                                </tr>
                            </template>
                            <tr x-show="!fTrucks.length"><td colspan="7" class="px-4 py-8 text-center text-slate-500">No trucks match.</td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- Drivers --}}
                <div x-show="hv === 'drivers'" x-cloak class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="{{ $th }}">Driver</th><th class="{{ $th }}">Hauler</th><th class="{{ $th }}">Licence no.</th><th class="{{ $th }}">Licence expiry</th><th class="{{ $th }}">Phone</th><th class="{{ $th }}"></th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="x in fDrivers" :key="x.id">
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900" x-text="x.name"></td>
                                    <td class="px-4 py-3" x-text="hauler(x.h).name"></td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs" x-text="x.lic"></td>
                                    <td class="px-4 py-3"><span :class="pill(certState(x.exp)[0])" x-text="certState(x.exp)[1]"></span></td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs" x-text="x.phone"></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right"><button type="button" x-show="canManage" @click="openForm('driver', x.id)" class="font-medium text-emerald-800 hover:underline">Edit</button></td>
                                </tr>
                            </template>
                            <tr x-show="!fDrivers.length"><td colspan="6" class="px-4 py-8 text-center text-slate-500">No drivers match.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- Add / edit form --}}
        <div x-show="hf" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 p-4" @click.self="hf = null">
            <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-lg bg-white shadow-xl" role="dialog" aria-modal="true">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                    <h3 class="font-semibold text-slate-900" x-text="hf ? ((hf.id ? 'Edit ' : (hf.kind === 'truck' ? 'Register ' : 'Add ')) + hf.kind) : ''"></h3>
                    <button type="button" @click="hf = null" class="rounded-lg border border-slate-300 px-3 py-1 text-sm text-slate-700 hover:bg-slate-50">Close</button>
                </div>

                <template x-if="hf">
                    <div class="space-y-4 px-5 py-4">
                        <template x-if="hf.kind === 'hauler'">
                            <div class="space-y-3">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div><label class="{{ $lb }}" for="f_name">Company name</label><input id="f_name" x-model="hf.name" class="{{ $in }}"></div>
                                    <div><label class="{{ $lb }}" for="f_contact">Contact person</label><input id="f_contact" x-model="hf.contact" class="{{ $in }}"></div>
                                    <div><label class="{{ $lb }}" for="f_phone">Contact number</label><input id="f_phone" type="tel" x-model="hf.phone" class="{{ $in }}"></div>
                                    <div><label class="{{ $lb }}" for="f_active">Status</label><select id="f_active" x-model="hf.active" class="{{ $in }}"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                                </div>
                                <div><label class="{{ $lb }}" for="f_addr">Company address</label><input id="f_addr" x-model="hf.addr" class="{{ $in }}"></div>
                            </div>
                        </template>

                        <template x-if="hf.kind === 'driver'">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div><label class="{{ $lb }}" for="f_dname">Full name</label><input id="f_dname" x-model="hf.name" class="{{ $in }}"></div>
                                <div><label class="{{ $lb }}" for="f_dh">Hauler</label><select id="f_dh" x-model="hf.h" class="{{ $in }}"><template x-for="h in d.haulers" :key="h.id"><option :value="h.id" :selected="h.id === hf.h" x-text="h.name"></option></template></select></div>
                                <div><label class="{{ $lb }}" for="f_lic">Licence number</label><input id="f_lic" x-model="hf.lic" class="{{ $in }}"></div>
                                <div><label class="{{ $lb }}" for="f_exp">Licence expiry</label><input id="f_exp" type="date" x-model="hf.exp" class="{{ $in }}"></div>
                                <div><label class="{{ $lb }}" for="f_dphone">Contact number</label><input id="f_dphone" type="tel" x-model="hf.phone" class="{{ $in }}"></div>
                            </div>
                        </template>

                        <template x-if="hf.kind === 'truck'">
                            <div class="space-y-3">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div><label class="{{ $lb }}" for="f_plate">Plate number</label><input id="f_plate" x-model="hf.plate" :readonly="!!hf.id" class="{{ $in }}" :class="hf.id ? 'bg-slate-50' : ''"></div>
                                    <div><label class="{{ $lb }}" for="f_th">Hauler</label><select id="f_th" x-model="hf.h" x-on:change="truckHaulerChanged()" class="{{ $in }}"><template x-for="h in d.haulers" :key="h.id"><option :value="h.id" :selected="h.id === hf.h" x-text="h.name"></option></template></select></div>
                                    <div class="sm:col-span-2"><label class="{{ $lb }}" for="f_td">Default driver</label>
                                        <select id="f_td" x-model="hf.dr" class="{{ $in }}"><option value="">No default driver</option><template x-for="x in driverOpts(hf.h)" :key="x.id"><option :value="x.id" :selected="x.id === hf.dr" x-text="x.name"></option></template></select>
                                        <p class="mt-1 text-xs text-slate-500">Only drivers of the selected hauler are listed. To add a new driver, use the Drivers tab first.</p>
                                    </div>
                                </div>
                                <div x-show="assignedElsewhere" x-cloak class="rounded border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">
                                    <p>This driver is already the default driver of <b x-text="assignedElsewhere && assignedElsewhere.plate"></b>.</p>
                                    <label class="mt-1 flex items-center gap-2"><input type="checkbox" x-model="hf.reassign" class="rounded border-slate-300 text-[#1c3d34] focus:ring-emerald-700"> Move the driver to this truck (the other truck will have no default driver)</label>
                                </div>
                                <div><label class="{{ $lb }}" for="f_comps">Compartments (liters, comma separated)</label><input id="f_comps" x-model="hf.comps" class="{{ $in }}"><p class="mt-1 text-xs text-slate-500" x-text="capNote"></p></div>
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <div><label class="{{ $lb }}" for="f_cal">Calibration expiry</label><input id="f_cal" type="date" x-model="hf.cal" class="{{ $in }}"></div>
                                    <div><label class="{{ $lb }}" for="f_lto">LTO registration expiry</label><input id="f_lto" type="date" x-model="hf.lto" class="{{ $in }}"></div>
                                    <div><label class="{{ $lb }}" for="f_saf">Safety permit expiry</label><input id="f_saf" type="date" x-model="hf.saf" class="{{ $in }}"></div>
                                </div>
                            </div>
                        </template>

                        <p class="text-sm text-red-700" x-text="hErr"></p>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="hf = null" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</button>
                            <button type="button" @click="saveForm()" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]" x-text="'Save ' + hf.kind"></button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        @include('operations._toast')
    </div>
</x-app-layout>
