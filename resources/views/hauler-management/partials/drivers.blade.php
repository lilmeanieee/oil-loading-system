@php
        $canManage = \App\Support\Role::can('drivers.manage');
        $in = 'w-full rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700';
        $lb = 'mb-1 block text-sm font-medium text-slate-700';
        $plates = collect(\App\Support\DemoData::trucks())->pluck('plate');
    @endphp

    <div class="py-6" x-data="{ adding: false }">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="max-w-2xl text-sm text-slate-600">A driver is eligible when the account is active and the license has not expired.</p>
                @if ($canManage)<button type="button" @click="adding = !adding" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]" x-text="adding ? 'Back to the driver list' : 'Add driver'"></button>@endif
            </div>

            @if ($canManage)
            <form x-show="adding" x-cloak class="grid gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:grid-cols-2" @submit.prevent="alert('Demo only: the backend will save drivers.'); adding = false">
                <div><label class="{{ $lb }}" for="d_name">Full name</label><input id="d_name" class="{{ $in }}" required></div>
                <div><label class="{{ $lb }}" for="d_contact">Contact number</label><input id="d_contact" class="{{ $in }}"></div>
                <div><label class="{{ $lb }}" for="d_lic">License number</label><input id="d_lic" class="{{ $in }}" required></div>
                <div><label class="{{ $lb }}" for="d_exp">License expiry date</label><input id="d_exp" type="date" class="{{ $in }}" required></div>
                <div><label class="{{ $lb }}" for="d_default">Default driver of</label><select id="d_default" class="{{ $in }}"><option value="">Not a default driver</option>@foreach ($plates as $p)<option>{{ $p }}</option>@endforeach</select></div>
                <fieldset><legend class="{{ $lb }}">Authorized substitute for</legend><div class="flex flex-wrap gap-3">@foreach ($plates as $p)<label class="flex items-center gap-1.5 text-sm"><input type="checkbox" class="rounded border-slate-300 text-emerald-800 focus:ring-emerald-700"> {{ $p }}</label>@endforeach</div></fieldset>
                <div class="flex justify-end gap-3 sm:col-span-2"><button type="button" @click="adding = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</button><button type="submit" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">Save driver</button></div>
            </form>
            @endif

            <div x-show="!adding" class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3 font-medium">Name</th><th class="px-4 py-3 font-medium">Contact</th><th class="px-4 py-3 font-medium">License</th><th class="px-4 py-3 font-medium">Default driver of</th><th class="px-4 py-3 font-medium">Substitute for</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Eligibility</th><th class="px-4 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($drivers as $d)
                            @php $lic = \App\Support\DemoData::certStatus($d['license_expiry']); $ok = $d['status'] === 'Active' && $lic !== 'Expired'; @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $d['name'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $d['contact'] }}</td>
                                <td class="px-4 py-3">{{ $d['license_no'] }}<br><span class="text-xs text-slate-500">Expires {{ \Carbon\Carbon::parse($d['license_expiry'])->format('M d, Y') }}</span> @if ($lic !== 'Valid')<x-status-badge :status="$lic" />@endif</td>
                                <td class="px-4 py-3">{{ $d['default_for'] ?: 'None' }}</td>
                                <td class="px-4 py-3">{{ $d['sub_for'] ? implode(', ', $d['sub_for']) : 'None' }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$d['status']" /></td>
                                <td class="px-4 py-3"><x-status-badge :status="$ok ? 'Eligible' : 'Not eligible'" /></td>
                                <td class="whitespace-nowrap px-4 py-3">@if ($canManage)<button type="button" @click="alert('Demo only: the backend will change the status.')" class="font-medium text-emerald-800 hover:underline">{{ $d['status'] === 'Active' ? 'Deactivate' : 'Activate' }}</button>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
