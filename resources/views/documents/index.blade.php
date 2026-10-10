<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Documents</h2></x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif
            <p class="text-sm text-slate-600">Delivery Receipts and Withdrawal Certificates that have been issued. A reprint keeps the original document number and needs a reason.</p>

            <div class="overflow-x-auto rounded border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr><th class="px-4 py-3 font-medium">Document</th><th class="px-4 py-3 font-medium">Number</th><th class="px-4 py-3 font-medium">ATL</th><th class="px-4 py-3 font-medium">SO number</th><th class="px-4 py-3 font-medium">Issued</th><th class="px-4 py-3 font-medium">Issued by</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Prints</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    @foreach ($documents as $d)
                        <tbody x-data="{ open: false }" class="border-t border-slate-100">
                            <tr>
                                <td class="px-4 py-3">{{ $d['type'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $d['no'] }}</td>
                                <td class="px-4 py-3"><a class="underline decoration-amber-500 underline-offset-2" href="{{ route('atl.show', $d['atl']) }}">ATL#{{ $d['atl'] }}</a></td>
                                <td class="px-4 py-3">{{ $d['so'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ \Carbon\Carbon::parse($d['issued'])->format('m/d/Y') }}</td>
                                <td class="px-4 py-3">{{ $d['by'] }}</td>
                                <td class="px-4 py-3"><span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Issued</span></td>
                                <td class="px-4 py-3 text-right">{{ $d['prints'] }}</td>
                                <td class="px-4 py-3">
                                    @if (\App\Support\Role::can('documents.reprint'))
                                        <button type="button" @click="open = !open" class="font-medium text-amber-700 hover:underline" x-text="open ? 'Close' : 'Reprint'"></button>
                                    @endif
                                </td>
                            </tr>
                            <tr x-show="open" x-cloak>
                                <td colspan="9" class="bg-slate-50 px-4 py-3">
                                    <form method="POST" action="#" class="flex flex-wrap items-end gap-3" onsubmit="event.preventDefault(); alert('Demo only: the backend will log the reprint.')">
                                        @csrf
                                        <div class="grow">
                                            <label class="mb-1 block text-sm font-medium text-slate-700" for="reason_{{ $loop->index }}">Reason for reprinting {{ $d['no'] }}</label>
                                            <input id="reason_{{ $loop->index }}" required minlength="3" class="w-full rounded border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
                                        </div>
                                        <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Reprint with the same number</button>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
