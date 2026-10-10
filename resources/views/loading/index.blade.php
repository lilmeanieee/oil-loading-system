<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Loading</h2></x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            <div class="overflow-x-auto rounded border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr><th class="px-4 py-3 font-medium">ATL no.</th><th class="px-4 py-3 font-medium">SO number</th><th class="px-4 py-3 font-medium">Plate</th><th class="px-4 py-3 font-medium">Driver</th><th class="px-4 py-3 text-right font-medium">Requested (L)</th><th class="px-4 py-3 text-right font-medium">Actual (L)</th><th class="px-4 py-3 font-medium">Loading ticket</th><th class="px-4 py-3 font-medium"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($atls as $a)
                            <tr>
                                <td class="px-4 py-3 font-medium"><a class="underline decoration-amber-500 underline-offset-2" href="{{ route('atl.show', $a['id']) }}">{{ $a['atl_no'] }}</a></td>
                                <td class="px-4 py-3">{{ $a['so_no'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $a['plate'] }}</td>
                                <td class="px-4 py-3">{{ $a['authorized'] }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format(collect($a['items'])->sum('volume')) }}</td>
                                <td class="px-4 py-3 text-right">{{ $a['actual'] ? number_format($a['actual']) : 'Not confirmed' }}</td>
                                <td class="px-4 py-3">{{ ['Approved' => 'Not started', 'Loading' => 'In progress', 'Completed' => 'Confirmed'][$a['status']] }}</td>
                                <td class="whitespace-nowrap px-4 py-3"><a class="font-medium text-amber-700 hover:underline" href="{{ route('loading-tickets.create', $a['id']) }}">{{ $a['status'] === 'Approved' ? 'Create ticket' : 'Open ticket' }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
