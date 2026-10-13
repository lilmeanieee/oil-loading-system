<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Documents &amp; Release</h2>
                <p class="mt-1 text-sm text-slate-600">Print the LT first. When the driver returns, prepare the DR and WC and hand them over before the truck leaves.</p>
            </div>
            <span class="hidden text-sm text-slate-500 sm:block">{{ now()->format('D d M Y') }}</span>
        </div>
    </x-slot>

    @include('operations._boot')

    <div class="py-6" x-data="opsApp(window.OPS_SEED, {})">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            <nav class="flex flex-wrap gap-2 rounded-lg border border-slate-200 bg-white p-3" role="tablist" aria-label="Release stage">
                <template x-for="t in docFilters" :key="t[0]">
                    <button type="button" role="tab" @click="setDocF(t[0])" :aria-selected="docF === t[0]"
                            :class="docF === t[0] ? 'bg-[#1c3d34] text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100'"
                            class="flex-1 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium">
                        <span x-text="t[1]"></span> <span class="ml-1 text-xs opacity-70" x-text="'(' + docCount(t[0]) + ')'"></span>
                    </button>
                </template>
            </nav>

            <div class="grid items-start gap-5 lg:grid-cols-[22rem_minmax(0,1fr)]">
                <div class="min-w-0 space-y-3">
                    <div class="rounded-lg border border-slate-200 bg-white p-3">
                        <label for="q" class="mb-1 block text-sm font-medium text-slate-700">Search approved ATLs</label>
                        <input id="q" type="search" x-model="search" x-on:input="docId = null" placeholder="ATL, plate, driver, hauler or document no." class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                    </div>
                    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                        <template x-for="a in dlist" :key="a.id">
                            <button type="button" @click="pickDoc(a.id)" class="block w-full border-b border-slate-100 px-4 py-3 text-left hover:bg-slate-50" :class="dcur && dcur.id === a.id ? 'bg-emerald-50' : ''">
                                <span class="flex items-center justify-between gap-2"><b class="text-sm text-slate-900" x-text="a.id"></b><span :class="pill(stageP(a)[0])" x-text="stageP(a)[1]"></span></span>
                                <span class="mt-1 block text-sm text-slate-600" x-text="a.truck + ' · ' + driver(a.driver).name"></span>
                                <span class="block text-xs text-slate-500" x-text="a.to"></span>
                            </button>
                        </template>
                        <p x-show="!dlist.length" class="p-6 text-center text-sm text-slate-500">No approved ATLs match.</p>
                    </section>
                </div>

                <section x-show="dcur" class="min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white">
                    <template x-if="dcur">
                        <div>
                            <div class="flex flex-wrap items-center gap-3 border-b border-slate-200 px-5 py-4">
                                <h3 class="font-mono text-lg font-semibold text-slate-900" x-text="dcur.id"></h3>
                                <span :class="pill(stageP(dcur)[0])" x-text="stageP(dcur)[1]"></span>
                                <span class="ml-auto text-sm text-slate-500" x-text="dcur.truck + ' · ' + driver(dcur.driver).name + ' · ' + fmtL(total(dcur))"></span>
                            </div>

                            <div class="border-b border-slate-100 px-5 py-4">
                                <ol class="grid gap-2 sm:grid-cols-4">
                                    <template x-for="s in steps" :key="s.a">
                                        <li class="rounded-lg border p-3 text-sm" :class="s.done ? 'border-emerald-300 bg-emerald-50' : s.now ? 'border-amber-300 bg-amber-50' : 'border-slate-200'">
                                            <div class="text-xs font-medium text-slate-500" x-text="s.a + (s.done ? ' · done' : '')"></div>
                                            <div class="font-medium text-slate-900" x-text="s.b"></div>
                                        </li>
                                    </template>
                                </ol>
                            </div>

                            {{-- Loading ticket --}}
                            <div class="border-b border-slate-100 px-5 py-4">
                                <h4 class="mb-2 text-sm font-semibold text-slate-900">Loading Ticket</h4>
                                <div class="rounded-lg border border-slate-200 p-3 text-sm">
                                    <div class="flex items-center justify-between gap-2"><b>Loading Ticket</b><span class="font-mono text-xs text-slate-600" x-text="dcur.d.lt.no"></span></div>
                                    <div class="mt-1"><span :class="pill(dcur.d.lt.st === 'printed' ? 'ok' : 'warn')" x-text="dcur.d.lt.st === 'printed' ? 'Printed' + (dcur.d.lt.n > 1 ? ' (' + dcur.d.lt.n + 'x)' : '') : 'Awaiting print'"></span></div>
                                    <div class="mt-1 text-xs text-slate-500" x-text="dcur.d.lt.st === 'printed' ? 'Last printed ' + dcur.d.lt.at : 'Not printed yet'"></div>
                                    <div class="mt-2 flex gap-2">
                                        <button type="button" @click="openPreview('lt')" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">Preview</button>
                                        <button type="button" x-show="dcur.d.lt.st === 'printed'" @click="openReprint('lt')" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">Reprint</button>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                                    <span x-show="dcur.d.ret" :class="pill('ok')">Driver is back in Operations</span>
                                    <template x-if="!dcur.d.ret">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <button type="button" @click="markReturned()" :disabled="dcur.d.lt.st !== 'printed'" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">Mark driver returned to Operations</button>
                                            <span class="text-xs text-slate-500" x-text="dcur.d.lt.st === 'printed' ? 'Use this once the truck has loaded and the driver is at the desk.' : 'Print the LT first.'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- DR and WC --}}
                            <div class="border-b border-slate-100 px-5 py-4">
                                <h4 class="mb-2 text-sm font-semibold text-slate-900">Delivery Receipt and Withdrawal Certificate</h4>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <template x-for="k in ['dr', 'wc']" :key="k">
                                        <div class="rounded-lg border p-3 text-sm" :class="docLocked(dcur, k) ? 'border-slate-200 bg-slate-50 opacity-70' : 'border-slate-200'">
                                            <div class="flex items-center justify-between gap-2"><b x-text="docName[k]"></b><span class="font-mono text-xs text-slate-600" x-text="dcur.d[k].no"></span></div>
                                            <div class="mt-1">
                                                <span x-show="docLocked(dcur, k)" :class="pill('')">Locked</span>
                                                <span x-show="!docLocked(dcur, k)" :class="pill(dcur.d[k].st === 'printed' ? 'ok' : 'warn')" x-text="dcur.d[k].st === 'printed' ? 'Printed' + (dcur.d[k].n > 1 ? ' (' + dcur.d[k].n + 'x)' : '') : 'Awaiting print'"></span>
                                            </div>
                                            <div class="mt-1 text-xs text-slate-500" x-text="docLocked(dcur, k) ? 'Unlocks when the driver returns.' : dcur.d[k].st === 'printed' ? 'Last printed ' + dcur.d[k].at : 'Not printed yet'"></div>
                                            <div class="mt-2 flex gap-2">
                                                <button type="button" :disabled="docLocked(dcur, k)" @click="openPreview(k)" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">Preview</button>
                                                <button type="button" x-show="dcur.d[k].st === 'printed'" @click="openReprint(k)" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">Reprint</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Handover --}}
                            <div class="border-b border-slate-100 px-5 py-4">
                                <h4 class="mb-2 text-sm font-semibold text-slate-900">Handover to driver</h4>
                                <template x-if="dcur.d.ho">
                                    <div class="flex flex-wrap items-center gap-3 text-sm"><span :class="pill('ok')">Released</span>
                                        <span class="text-slate-700">DR and WC handed to <b x-text="driver(dcur.driver).name"></b>. Released by <b x-text="dcur.d.ho.staff"></b> at <b class="font-mono" x-text="dcur.d.ho.time"></b>.</span></div>
                                </template>
                                <template x-if="!dcur.d.ho">
                                    <div>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div><label for="hstaff" class="mb-1 block text-sm font-medium text-slate-700">Released by</label>
                                                <select id="hstaff" x-model="hStaff" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700"><template x-for="s in d.staff" :key="s"><option :value="s" x-text="s"></option></template></select></div>
                                            <div><label for="htime" class="mb-1 block text-sm font-medium text-slate-700">Time of release</label>
                                                <input id="htime" type="time" x-model="hTime" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700"></div>
                                        </div>
                                        <div class="mt-3 flex flex-wrap items-center gap-3">
                                            <button type="button" @click="handover()" :disabled="!(dcur.d.dr.st === 'printed' && dcur.d.wc.st === 'printed')" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c] disabled:cursor-not-allowed disabled:opacity-40">Record handover</button>
                                            <span class="text-xs text-slate-500" x-show="!(dcur.d.dr.st === 'printed' && dcur.d.wc.st === 'printed')">Print the DR and WC first.</span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="px-5 py-4">
                                <h4 class="mb-2 text-sm font-semibold text-slate-900">Print and release history</h4>
                                <ul class="space-y-1.5 text-sm">
                                    <template x-for="(h, i) in dcur.d.log.slice().reverse()" :key="i"><li class="flex gap-3"><time class="w-14 shrink-0 text-xs text-slate-500" x-text="h.t"></time><span class="text-slate-700" x-text="h.text"></span></li></template>
                                </ul>
                                <p x-show="!dcur.d.log.length" class="text-sm text-slate-500">No activity yet.</p>
                            </div>
                        </div>
                    </template>
                </section>
                <section x-show="!dcur" x-cloak class="rounded-lg border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">Select an approved ATL to open its documents.</section>
            </div>
        </div>

        {{-- Preview / reprint modal --}}
        <div x-show="modal" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 p-4" @click.self="modal = null">
            <div class="max-h-[90vh] w-full overflow-y-auto rounded-lg bg-white shadow-xl" :class="modal && modal.kind === 'preview' ? 'max-w-2xl' : 'max-w-md'" role="dialog" aria-modal="true">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                    <h3 class="font-semibold text-slate-900" x-text="modal ? (modal.kind === 'preview' ? 'Preview' : 'Reprint ' + (dcur ? dcur.d[modal.k].no : '')) : ''"></h3>
                    <button type="button" @click="modal = null" class="rounded-lg border border-slate-300 px-3 py-1 text-sm text-slate-700 hover:bg-slate-50">Close</button>
                </div>

                <template x-if="paper">
                    <div class="px-5 py-4">
                        <div class="rounded border border-slate-300 p-5 text-sm">
                            <div class="flex items-start justify-between gap-3 border-b border-slate-300 pb-3">
                                <h4 class="text-lg font-semibold uppercase tracking-wide" x-text="paper.title"></h4>
                                <div class="text-right font-mono text-xs text-slate-600"><span x-text="paper.no"></span><br>ATL <span x-text="paper.atl"></span><br><span x-text="paper.date"></span></div>
                            </div>
                            <dl class="my-3 grid gap-2 sm:grid-cols-2">
                                <div><dt class="text-xs text-slate-500">Truck</dt><dd x-text="dcur.truck"></dd></div>
                                <div><dt class="text-xs text-slate-500">Driver</dt><dd x-text="driver(dcur.driver).name"></dd></div>
                                <div><dt class="text-xs text-slate-500">Hauler</dt><dd x-text="hauler(dcur.h).name"></dd></div>
                                <div><dt class="text-xs text-slate-500">Consignee</dt><dd x-text="dcur.to"></dd></div>
                            </dl>
                            <table class="w-full text-sm">
                                <thead class="text-left text-slate-500"><tr><th class="py-1">Comp.</th><th>Product</th><th class="text-right">Liters</th></tr></thead>
                                <tbody>
                                    <template x-for="l in dcur.lines" :key="l.c"><tr class="border-t border-slate-200"><td class="py-1" x-text="'C' + l.c"></td><td x-text="l.p"></td><td class="text-right" x-text="l.l.toLocaleString('en-US')"></td></tr></template>
                                    <tr class="border-t border-slate-300 font-semibold"><td></td><td class="py-1">Total</td><td class="text-right" x-text="total(dcur).toLocaleString('en-US')"></td></tr>
                                </tbody>
                            </table>
                            <div class="mt-8 grid grid-cols-3 gap-3 text-center text-xs text-slate-600">
                                <template x-for="s in paper.sig" :key="s"><div class="border-t border-slate-400 pt-1" x-text="s"></div></template>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button" @click="modal = null" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Close</button>
                            <button type="button" x-show="paper.awaiting" @click="doPrint()" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">Print</button>
                        </div>
                    </div>
                </template>

                <template x-if="modal && modal.kind === 'reprint' && dcur">
                    <div class="space-y-3 px-5 py-4 text-sm">
                        <p class="text-slate-700">This copy is logged as reprint <span x-text="dcur.d[modal.k].n"></span> for <span x-text="dcur.id"></span>.</p>
                        <div><label for="rp" class="mb-1 block font-medium text-slate-700">Reason for reprint</label>
                            <select id="rp" x-model="rpReason" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                                <option>Paper jam or misprint</option><option>Copy damaged or lost</option><option>Correction to details</option><option>Extra copy requested</option>
                            </select></div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="modal = null" class="rounded-lg border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50">Cancel</button>
                            <button type="button" @click="doReprint()" class="rounded-lg bg-[#1c3d34] px-4 py-2 font-medium text-white hover:bg-[#2a5a4c]">Reprint and log</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        @include('operations._toast')
    </div>
</x-app-layout>
