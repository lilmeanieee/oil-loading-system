@php $hints = ['Submitted' => 'Waiting for Operations', 'Approved' => 'Ready for loading and documents', 'Loading' => 'At the depot', 'Completed' => 'Finished']; @endphp

<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach ($cards as $s)
        <a href="{{ route('atl.index', ['status' => $s]) }}" class="rounded-lg border border-slate-200 bg-white p-4 hover:border-emerald-700">
            <div class="text-2xl font-semibold text-slate-900">{{ $counts[$s] ?? 0 }}</div>
            <div class="text-sm font-medium text-slate-700">{{ $s }}</div>
            <div class="text-xs text-slate-500">{{ $hints[$s] }}</div>
        </a>
    @endforeach
</div>

<div class="grid gap-5 lg:grid-cols-3">
    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white lg:col-span-2">
        <h3 class="px-4 pt-4 font-semibold text-slate-900">Recent ATLs</h3>
        <table class="mt-2 min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-2 font-medium">ATL no.</th><th class="px-4 py-2 font-medium">SO number</th><th class="px-4 py-2 font-medium">Plate</th><th class="px-4 py-2 font-medium">Driver</th><th class="px-4 py-2 font-medium">Status</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($atls as $a)
                    <tr>
                        <td class="px-4 py-2 font-medium"><a class="underline decoration-emerald-700 underline-offset-2" href="{{ route('atl.show', $a['id']) }}">{{ $a['atl_no'] }}</a></td>
                        <td class="px-4 py-2">{{ $a['so_no'] }}</td>
                        <td class="px-4 py-2">{{ $a['plate'] }}</td>
                        <td class="px-4 py-2">{{ $a['authorized'] }}</td>
                        <td class="px-4 py-2"><x-status-badge :status="$a['status']" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <h3 class="font-semibold text-slate-900">Recent activity</h3>
        <ul class="mt-3 space-y-3 text-sm">
            @foreach ($activity as $t)
                <li>
                    <p class="text-slate-800">{{ $t['label'] }} on <a class="underline decoration-emerald-700 underline-offset-2" href="{{ route('atl.show', $t['atl_id']) }}">{{ $t['atl'] }}</a></p>
                    <p class="text-xs text-slate-500">{{ $t['by'] }}, {{ \Carbon\Carbon::parse($t['at'])->format('M d, g:i A') }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</div>
