<x-app-layout>
    @php
        $in = 'w-full rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700';
        $lb = 'mb-1 block text-sm font-medium text-slate-700';
    @endphp

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900">Product Master</h2>
        <p class="mt-1 text-sm text-slate-600">Product codes, names and active status</p>
    </x-slot>

    <div class="py-6" x-data="{ adding: false }">
        <div class="mx-auto max-w-5xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="max-w-2xl text-sm text-slate-600">Each product has three names: the one on the ATL, the code on the Loading Ticket, and the name on the Delivery Receipt and Withdrawal Certificate.</p>
                <button type="button" @click="adding = !adding" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]" x-text="adding ? 'Back to the product list' : 'Add product'"></button>
            </div>

            <form x-show="adding" x-cloak class="grid gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:grid-cols-2" @submit.prevent="alert('Demo only: the backend will save products.'); adding = false">
                <div><label class="{{ $lb }}" for="p_atl">Name on the ATL</label><input id="p_atl" class="{{ $in }}" required></div>
                <div><label class="{{ $lb }}" for="p_code">Code on the Loading Ticket</label><input id="p_code" class="{{ $in }}" required></div>
                <div><label class="{{ $lb }}" for="p_doc">Name on the DR and Withdrawal Certificate</label><input id="p_doc" class="{{ $in }}" required></div>
                <div><label class="{{ $lb }}" for="p_tax">Default tax note</label><input id="p_tax" class="{{ $in }}" placeholder="Example: CME 3%"></div>
                <div class="flex justify-end gap-3 sm:col-span-2"><button type="button" @click="adding = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</button><button type="submit" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">Save product</button></div>
            </form>

            <div x-show="!adding" class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3 font-medium">ATL name</th><th class="px-4 py-3 font-medium">Loading Ticket code</th><th class="px-4 py-3 font-medium">DR / Withdrawal Certificate name</th><th class="px-4 py-3 font-medium">Default tax note</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($products as $p)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $p['atl'] }}</td>
                                <td class="px-4 py-3">{{ $p['ticket'] }}</td>
                                <td class="px-4 py-3">{{ $p['doc'] }}</td>
                                <td class="px-4 py-3">{{ $p['tax_note'] ?: 'None' }}</td>
                                <td class="px-4 py-3"><x-status-badge status="Active" /></td>
                                <td class="whitespace-nowrap px-4 py-3"><button type="button" @click="alert('Demo only: the backend will change the status.')" class="font-medium text-emerald-800 hover:underline">Deactivate</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-slate-500">Ethanol is a placeholder. Confirm its names with the office.</p>
        </div>
    </div>
</x-app-layout>
