@php
        $canManage = \App\Support\Role::can('haulers.manage');
        $in = 'w-full rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700';
        $lb = 'mb-1 block text-sm font-medium text-slate-700';
    @endphp

    <div class="py-6" x-data="{ adding: false }">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-end">@if ($canManage)<button type="button" @click="adding = !adding" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]" x-text="adding ? 'Back to the hauler list' : 'Add hauler'"></button>@endif</div>

            @if ($canManage)
            <form x-show="adding" x-cloak class="grid gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:grid-cols-2" @submit.prevent="alert('Demo only: the backend will save haulers.'); adding = false">
                <div><label class="{{ $lb }}" for="h_name">Company name</label><input id="h_name" class="{{ $in }}" required></div>
                <div><label class="{{ $lb }}" for="h_contact">Contact person</label><input id="h_contact" class="{{ $in }}"></div>
                <div><label class="{{ $lb }}" for="h_phone">Phone</label><input id="h_phone" class="{{ $in }}"></div>
                <div><label class="{{ $lb }}" for="h_addr">Address</label><input id="h_addr" class="{{ $in }}"></div>
                <div class="flex justify-end gap-3 sm:col-span-2"><button type="button" @click="adding = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</button><button type="submit" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">Save hauler</button></div>
            </form>
            @endif

            <div x-show="!adding" class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3 font-medium">Company</th><th class="px-4 py-3 font-medium">Contact</th><th class="px-4 py-3 font-medium">Address</th><th class="px-4 py-3 text-right font-medium">Trucks</th><th class="px-4 py-3 text-right font-medium">Drivers</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3"></th></tr></thead>
                    @foreach ($haulers as $h)
                        <tbody x-data="{ open: false }" class="border-t border-slate-100">
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $h['name'] }}</td>
                                <td class="px-4 py-3">{{ $h['contact'] }}<br><span class="text-xs text-slate-500">{{ $h['phone'] }}</span></td>
                                <td class="px-4 py-3">{{ $h['address'] }}</td>
                                <td class="px-4 py-3 text-right">{{ count($h['plates']) }}</td>
                                <td class="px-4 py-3 text-right">{{ count($h['drivers']) }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$h['status']" /></td>
                                <td class="whitespace-nowrap px-4 py-3"><button type="button" @click="open = !open" class="font-medium text-emerald-800 hover:underline" x-text="open ? 'Hide trucks and drivers' : 'Trucks and drivers'"></button></td>
                            </tr>
                            <tr x-show="open" x-cloak>
                                <td colspan="7" class="bg-slate-50 px-4 py-3 text-sm">
                                    <p><span class="font-medium text-slate-700">Trucks:</span> {{ $h['plates'] ? implode(', ', $h['plates']) : 'None registered' }}</p>
                                    <p class="mt-1"><span class="font-medium text-slate-700">Drivers:</span> {{ $h['drivers'] ? implode(', ', $h['drivers']) : 'None registered' }}</p>
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
