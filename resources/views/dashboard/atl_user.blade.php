@php
    $phrase = ['Draft' => 'Not submitted yet', 'Submitted' => 'Awaiting review', 'Approved' => 'Approved', 'Declined' => 'Returned for correction', 'Loading' => 'At the depot', 'Completed' => 'Completed'];
    $label = ['Submitted' => 'Pending', 'Declined' => 'Returned'];
@endphp

<div class="rounded-xl bg-[#1c3d34] p-5 text-white">
    <h3 class="text-lg font-semibold">Create a new ATL</h3>
    <p class="mt-2 text-sm text-emerald-100">Prepare an Authority to Load request.</p>
    <a href="{{ route('atl.create') }}" class="mt-4 block rounded-lg bg-white py-3 text-center font-medium text-[#1c3d34] hover:bg-emerald-50">+ Create ATL</a>
</div>

<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach ([['My Drafts', 'drafts', route('atl.drafts')], ['Pending', 'pending', route('atl.mine', ['status' => 'Submitted'])], ['Approved', 'approved', route('atl.mine', ['status' => 'Approved'])], ['Returned', 'returned', route('atl.returned')]] as [$name, $key, $url])
        <a href="{{ $url }}" class="rounded-lg border border-slate-200 bg-white p-4 hover:border-emerald-700">
            <p class="text-sm text-slate-600">{{ $name }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $counts[$key] }}</p>
        </a>
    @endforeach
</div>

<div class="grid gap-5 lg:grid-cols-3">
    <div class="space-y-3 lg:col-span-2">
        <h3 class="font-semibold text-slate-900">Recent ATL Records</h3>
        @forelse ($recent as $a)
            <a href="{{ route('atl.show', $a['id']) }}" class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white p-4 hover:border-emerald-700">
                <div>
                    <p class="font-medium text-slate-900">{{ $a['atl_no'] }}, SO {{ $a['so_no'] }}</p>
                    <p class="text-sm text-slate-500">{{ $phrase[$a['status']] }}. Updated {{ \Carbon\Carbon::parse(collect($a['timeline'])->last()['at'])->format('M d, g:i A') }}</p>
                </div>
                <x-status-badge :status="$a['status']" :label="$label[$a['status']] ?? null" />
            </a>
        @empty
            <p class="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">You have not created an ATL yet.</p>
        @endforelse
    </div>

    <div class="space-y-5">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <h3 class="font-semibold text-slate-900">Notifications</h3>
            <ul class="mt-2 space-y-2 text-sm">
                @forelse ($notes as $n)
                    <li><p class="text-slate-800">{{ $n['text'] }}</p><p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($n['at'])->format('M d, g:i A') }}</p></li>
                @empty
                    <li class="text-slate-500">Nothing new.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <h3 class="font-semibold text-slate-900">Offline sync</h3>
            <p class="mt-2 text-sm text-slate-700"><span data-pwa-queue-count>0</span> draft or submission waiting to sync</p>
            <p class="text-sm text-slate-500">Last successful sync: <span data-pwa-last-sync>not yet</span></p>
        </div>
    </div>
</div>
