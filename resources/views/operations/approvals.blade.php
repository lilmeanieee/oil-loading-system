<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">ATL Approvals</h2>
                <p class="mt-1 text-sm text-slate-600">Check every ATL against the truck, driver and load before it is approved.</p>
            </div>
            <span class="hidden text-sm text-slate-500 sm:block">{{ now()->format('D d M Y') }}</span>
        </div>
    </x-slot>

    @include('operations._boot')

    <div class="py-6" x-data="opsApp(window.OPS_SEED, {})">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            <nav class="flex flex-wrap gap-2 rounded-lg border border-slate-200 bg-white p-3" role="tablist" aria-label="ATL status">
                <template x-for="t in tabs" :key="t[0]">
                    <button type="button" role="tab" @click="setTab(t[0])" :aria-selected="tab === t[0]"
                            :class="tab === t[0] ? 'bg-[#1c3d34] text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100'"
                            class="flex-1 rounded-lg px-4 py-2 text-sm font-medium">
                        <span x-text="t[1]"></span> <span class="ml-1 text-xs opacity-70" x-text="'(' + tabCount(t[0]) + ')'"></span>
                    </button>
                </template>
            </nav>

            <div class="grid items-start gap-5 lg:grid-cols-[22rem_minmax(0,1fr)]">
                {{-- List --}}
                <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                    <template x-for="a in alist" :key="a.id">
                        <button type="button" @click="pick(a.id)" class="block w-full border-b border-slate-100 px-4 py-3 text-left hover:bg-slate-50"
                                :class="cur && cur.id === a.id ? 'bg-emerald-50' : ''">
                            <span class="flex items-center justify-between gap-2">
                                <b class="text-sm text-slate-900" x-text="a.id"></b>
                                <span x-show="flagged(a) && (a.st === 'pending' || a.st === 'resubmitted')" :class="pill('bad')">Flagged</span>
                                <span x-show="!(flagged(a) && (a.st === 'pending' || a.st === 'resubmitted'))" :class="pill(astat(a.st)[0])" x-text="astat(a.st)[1]"></span>
                            </span>
                            <span class="mt-1 block text-sm text-slate-600" x-text="a.truck + ' · ' + driver(a.driver).name"></span>
                            <span class="block text-sm text-slate-500" x-text="hauler(a.h).name"></span>
                            <span class="block text-xs text-slate-500" x-text="fmtL(total(a)) + ' · ' + a.at"></span>
                        </button>
                    </template>
                    <p x-show="!alist.length" class="p-6 text-center text-sm text-slate-500">No ATLs in this tab.</p>
                </section>

                {{-- Detail --}}
                <section x-show="cur" class="min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white">
                    <template x-if="cur">
                        <div>
                            <div class="flex flex-wrap items-center gap-3 border-b border-slate-200 px-5 py-4">
                                <h3 class="font-mono text-lg font-semibold text-slate-900" x-text="cur.id"></h3>
                                <span :class="pill(astat(cur.st)[0])" x-text="astat(cur.st)[1]"></span>
                                <span class="ml-auto text-sm text-slate-500" x-text="'Submitted ' + cur.at"></span>
                            </div>

                            <div x-show="cur.st === 'resubmitted'" class="border-b border-slate-200 bg-sky-50 px-5 py-3 text-sm text-sky-900">
                                <b>Resubmitted after correction.</b> <span x-text="cur.reason"></span>
                            </div>

                            <div class="border-b border-slate-100 px-5 py-4">
                                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                    <div><dt class="text-slate-500">Truck</dt><dd class="font-mono text-slate-900" x-text="cur.truck"></dd></div>
                                    <div><dt class="text-slate-500">Driver</dt><dd class="text-slate-900" x-text="driver(cur.driver).name"></dd></div>
                                    <div><dt class="text-slate-500">Hauler</dt><dd class="text-slate-900" x-text="hauler(cur.h).name"></dd></div>
                                    <div><dt class="text-slate-500">Consignee</dt><dd class="text-slate-900" x-text="cur.to"></dd></div>
                                </dl>
                            </div>

                            <div class="border-b border-slate-100 px-5 py-4">
                                <h4 class="mb-2 text-sm font-semibold text-slate-900">Products and compartments</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="text-left text-slate-500"><tr><th class="py-1 pr-4 font-medium">Comp.</th><th class="py-1 pr-4 text-right font-medium">Limit</th><th class="py-1 pr-4 font-medium">Product</th><th class="py-1 pr-4 text-right font-medium">Liters</th><th class="py-1 font-medium">Fill</th></tr></thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <template x-for="l in lineView(cur)" :key="l.c">
                                                <tr>
                                                    <td class="py-2 pr-4 font-mono" x-text="'C' + l.c"></td>
                                                    <td class="py-2 pr-4 text-right" x-text="l.cp ? fmtL(l.cp) : 'none'"></td>
                                                    <td class="py-2 pr-4" x-text="l.p"></td>
                                                    <td class="py-2 pr-4 text-right" :class="l.over ? 'font-medium text-red-700' : ''" x-text="fmtL(l.l)"></td>
                                                    <td class="py-2"><div class="h-2 w-28 rounded bg-slate-100"><div class="h-2 rounded" :class="l.over ? 'bg-red-500' : 'bg-emerald-600'" :style="'width:' + l.pct + '%'"></div></div></td>
                                                </tr>
                                            </template>
                                            <tr class="font-medium"><td class="py-2" colspan="3">Total</td><td class="py-2 pr-4 text-right" x-text="fmtL(total(cur))"></td><td></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="border-b border-slate-100 px-5 py-4">
                                <h4 class="mb-2 text-sm font-semibold text-slate-900">Attachments</h4>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="f in cur.files" :key="f"><span class="rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs text-slate-700" x-text="f"></span></template>
                                </div>
                            </div>

                            <div class="border-b border-slate-100 px-5 py-4">
                                <h4 class="mb-2 text-sm font-semibold text-slate-900">Verification</h4>
                                <template x-for="k in CHK" :key="k[0]">
                                    <div class="flex items-start gap-3 py-2">
                                        <input type="checkbox" :id="'c_' + k[0]" class="mt-1 rounded border-slate-300 text-[#1c3d34] focus:ring-emerald-700"
                                               :checked="!!ck[k[0]]" :disabled="!(cur.st === 'pending' || cur.st === 'resubmitted')" x-on:change="tick(k[0], $event.target.checked)">
                                        <label :for="'c_' + k[0]" class="min-w-0 flex-1 text-sm">
                                            <span class="font-medium text-slate-900" x-text="k[1]"></span>
                                            <span class="block" :class="{ 'text-slate-600': checksFor(cur)[k[0]].s === 'ok', 'text-amber-700': checksFor(cur)[k[0]].s === 'warn', 'text-red-700': checksFor(cur)[k[0]].s === 'bad' }" x-text="checksFor(cur)[k[0]].n"></span>
                                        </label>
                                        <span :class="pill(checksFor(cur)[k[0]].s === 'ok' ? 'ok' : checksFor(cur)[k[0]].s === 'bad' ? 'bad' : 'warn')" x-text="checksFor(cur)[k[0]].s === 'ok' ? 'Matches' : checksFor(cur)[k[0]].s === 'bad' ? 'Flagged' : 'Check'"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="border-b border-slate-100 px-5 py-4">
                                <h4 class="mb-2 text-sm font-semibold text-slate-900" x-text="(cur.st === 'pending' || cur.st === 'resubmitted') ? 'Decision' : 'Outcome'"></h4>

                                <template x-if="cur.st === 'pending' || cur.st === 'resubmitted'">
                                    <div>
                                        <div x-show="!mode" class="flex flex-wrap items-center gap-2">
                                            <button type="button" @click="approve()" :disabled="!canApprove" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c] disabled:cursor-not-allowed disabled:opacity-40">Approve ATL</button>
                                            <button type="button" @click="mode = 'return'" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Return for correction</button>
                                            <button type="button" @click="mode = 'decline'" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Decline</button>
                                            <p class="w-full text-xs text-slate-500" x-show="!canApprove">Tick every verification line to approve. An ATL with a flagged check cannot be approved.</p>
                                        </div>
                                        <div x-show="mode" x-cloak class="space-y-2">
                                            <label for="reason" class="block text-sm font-medium text-slate-700" x-text="mode === 'return' ? 'What does the hauler need to correct?' : 'Why is this ATL declined?'"></label>
                                            <textarea id="reason" x-model="reason" rows="3" class="w-full rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700" :placeholder="mode === 'return' ? 'Example: compartment 2 exceeds its limit.' : 'Example: volume is above the approved allocation.'"></textarea>
                                            <p class="text-sm text-red-700" x-text="reasonErr"></p>
                                            <div class="flex gap-2">
                                                <button type="button" @click="confirmReason()" class="rounded-lg px-4 py-2 text-sm font-medium text-white" :class="mode === 'return' ? 'bg-[#1c3d34] hover:bg-[#2a5a4c]' : 'bg-red-700 hover:bg-red-800'" x-text="mode === 'return' ? 'Send back to hauler' : 'Decline ATL'"></button>
                                                <button type="button" @click="mode = null; reasonErr = ''" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="cur.st === 'approved'">
                                    <div class="flex flex-wrap items-center gap-3 text-sm"><span :class="pill('ok')">Approved</span>
                                        <span class="text-slate-600">Documents are in <a :href="u.documents + '?id=' + cur.id" class="font-medium text-emerald-800 hover:underline">Documents &amp; Release</a>.</span></div>
                                </template>

                                <template x-if="cur.st === 'returned' || cur.st === 'declined'">
                                    <div class="text-sm"><span :class="pill(astat(cur.st)[0])" x-text="astat(cur.st)[1]"></span>
                                        <p class="mt-2"><b>Reason:</b> <span x-text="cur.reason"></span></p>
                                        <p x-show="cur.st === 'returned'" class="mt-1 text-xs text-slate-500">Waiting for the hauler to resubmit.</p></div>
                                </template>
                            </div>

                            <div class="px-5 py-4">
                                <h4 class="mb-2 text-sm font-semibold text-slate-900">History</h4>
                                <ul class="space-y-1.5 text-sm">
                                    <template x-for="(h, i) in cur.hist" :key="i"><li class="flex gap-3"><time class="w-20 shrink-0 text-xs text-slate-500" x-text="h.t"></time><span class="text-slate-700" x-text="h.text"></span></li></template>
                                </ul>
                            </div>
                        </div>
                    </template>
                </section>
                <section x-show="!cur" x-cloak class="rounded-lg border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">Select an ATL to review it.</section>
            </div>
        </div>

        @include('operations._toast')
    </div>
</x-app-layout>
