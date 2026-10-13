<x-app-layout>
    @php
        $in = 'w-full rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700';
        $lb = 'mb-1 block text-sm font-medium text-slate-700';
        $tab = 'border-b-2 px-4 py-2 text-sm font-medium';
        $save = "alert('Demo only: the backend will save these settings.')";
    @endphp

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900">System Settings</h2>
        <p class="mt-1 text-sm text-slate-600">Company details, numbering, devices and security</p>
    </x-slot>

    <div class="py-6" x-data="{ tab: 'company' }">
        <div class="mx-auto max-w-4xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap gap-2 border-b border-slate-200" role="tablist">
                @foreach (['company' => 'Company', 'numbering' => 'Numbering', 'devices' => 'Devices', 'security' => 'Security'] as $key => $label)
                    <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'" class="{{ $tab }}" :class="tab === '{{ $key }}' ? 'border-[#1c3d34] text-slate-900' : 'border-transparent text-slate-500'">{{ $label }}</button>
                @endforeach
            </div>

            <form x-show="tab === 'company'" class="space-y-4 rounded-lg border border-slate-200 bg-white p-5" @submit.prevent="{{ $save }}">
                <p class="text-sm text-slate-600">These details are used on printed documents.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="{{ $lb }}" for="c_name">Depot company</label><input id="c_name" class="{{ $in }}" value="PA FUEL 118 CORPORATION"></div>
                    <div><label class="{{ $lb }}" for="c_tin">VAT registration TIN</label><input id="c_tin" class="{{ $in }}" value="449-521-810-00000"></div>
                    <div class="sm:col-span-2"><label class="{{ $lb }}" for="c_addr">Address</label><input id="c_addr" class="{{ $in }}" value="Lot 24, Balmores Street, Zone II, (Pob.) 9506, City of Koronadal, South Cotabato"></div>
                    <div><label class="{{ $lb }}" for="c_owner">Delivery Receipt and Withdrawal Certificate signer</label><input id="c_owner" class="{{ $in }}" value="MARY JOY LLENES"></div>
                    <div><label class="{{ $lb }}" for="c_officer">Revenue officer on the Withdrawal Certificate</label><input id="c_officer" class="{{ $in }}" value="HEDDA LYNN C. LATORRE"></div>
                </div>
                <div class="flex justify-end"><button type="submit" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">Save</button></div>
            </form>

            <form x-show="tab === 'numbering'" x-cloak class="space-y-4 rounded-lg border border-slate-200 bg-white p-5" @submit.prevent="{{ $save }}">
                <p class="text-sm text-slate-600">The year and month are filled in automatically. The last digits are typed by the ATL User.</p>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label class="{{ $lb }}" for="n_so">SO number format</label><input id="n_so" class="{{ $in }}" value="YYYY-MM-####"><p class="mt-1 text-xs text-slate-500">Example: {{ now()->format('Y-m') }}-0001</p></div>
                    <div><label class="{{ $lb }}" for="n_ctrl">Control number format</label><input id="n_ctrl" class="{{ $in }}" value="YYYY-###"><p class="mt-1 text-xs text-slate-500">Example: {{ now()->format('Y') }}-001</p></div>
                    <div><label class="{{ $lb }}" for="n_atl">ATL number format</label><input id="n_atl" class="{{ $in }}" value="ATL#000"><p class="mt-1 text-xs text-slate-500">Example: ATL#001</p></div>
                    <div><label class="{{ $lb }}" for="n_dr">Next Delivery Receipt number</label><input id="n_dr" class="{{ $in }}" value="{{ \App\Support\DemoData::nextNumbers()['dr'] }}"></div>
                    <div class="sm:col-span-2"><label class="{{ $lb }}" for="n_wcp">Next Withdrawal Certificate (WCP) number</label><input id="n_wcp" class="{{ $in }}" value="{{ \App\Support\DemoData::nextNumbers()['wcp'] }}"></div>
                </div>
                <div class="flex justify-end"><button type="submit" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">Save</button></div>
            </form>

            <section x-show="tab === 'devices'" x-cloak class="space-y-3">
                <p class="text-sm text-slate-600">Computers that work offline and send their saved work when the connection returns. A conflict means the server did not accept something that was saved offline.</p>
                <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3 font-medium">Device</th><th class="px-4 py-3 font-medium">User</th><th class="px-4 py-3 font-medium">Last sync</th><th class="px-4 py-3 text-right font-medium">Waiting</th><th class="px-4 py-3 font-medium">State</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($devices as $d)
                                <tr><td class="px-4 py-3 font-medium">{{ $d['label'] }}</td><td class="px-4 py-3">{{ $d['user'] }}</td><td class="whitespace-nowrap px-4 py-3">{{ \Carbon\Carbon::parse($d['last_sync'])->format('M d, g:i A') }}</td><td class="px-4 py-3 text-right">{{ $d['waiting'] }}</td><td class="px-4 py-3"><x-status-badge :status="$d['state']" /></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <form x-show="tab === 'security'" x-cloak class="space-y-4 rounded-lg border border-slate-200 bg-white p-5" @submit.prevent="{{ $save }}">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="{{ $lb }}" for="s_exp">Email code lifetime (minutes)</label><input id="s_exp" type="number" min="1" class="{{ $in }}" value="10"></div>
                    <div><label class="{{ $lb }}" for="s_try">Wrong codes allowed</label><input id="s_try" type="number" min="1" class="{{ $in }}" value="5"></div>
                    <div><label class="{{ $lb }}" for="s_resend">Wait before sending a new code (seconds)</label><input id="s_resend" type="number" min="0" class="{{ $in }}" value="60"></div>
                    <div><label class="{{ $lb }}" for="s_idle">Sign out after being idle (minutes)</label><input id="s_idle" type="number" min="1" class="{{ $in }}" value="30"></div>
                </div>
                <p class="text-xs text-slate-500">A code can only be used once.</p>
                <div class="flex justify-end"><button type="submit" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">Save</button></div>
            </form>
        </div>
    </div>
</x-app-layout>
