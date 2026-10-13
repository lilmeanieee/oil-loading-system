<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach ($stats as $label => $n)
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-600">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $n }}</p>
        </div>
    @endforeach
</div>

<section class="rounded-lg border border-slate-200 bg-white p-4">
    <h3 class="font-semibold text-slate-900">System Overview</h3>
    <div class="mt-2 divide-y divide-slate-100 text-sm">
        <div class="flex items-center justify-between py-3"><span class="text-slate-700">Recent user activity</span><span class="text-slate-500">Last sign-in {{ \Carbon\Carbon::parse($lastLogins[0]['last_login'])->format('M d, g:i A') }}</span></div>
        <a href="{{ route('users.index') }}" class="flex items-center justify-between py-3 hover:text-emerald-800"><span class="text-slate-700">Pending user accounts</span><span class="font-medium text-orange-700">{{ count($registrations) }} waiting</span></a>
        <a href="{{ route('settings.index') }}" class="flex items-center justify-between py-3 hover:text-emerald-800"><span class="text-slate-700">Offline devices and sync issues</span><span class="font-medium text-slate-700">{{ collect($devices)->where('state', 'Conflict')->count() }} conflict, {{ collect($devices)->where('state', 'Offline')->count() }} offline</span></a>
    </div>
</section>

<div class="grid gap-5 lg:grid-cols-2">
    <section class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between"><h3 class="font-semibold text-slate-900">Account requests</h3><a href="{{ route('users.index') }}" class="text-sm text-emerald-800 hover:underline">Open queue</a></div>
        <ul class="mt-3 space-y-3 text-sm">
            @foreach ($registrations as $r)
                <li class="flex items-center justify-between gap-3">
                    <div><p class="font-medium text-slate-800">{{ $r['name'] }}</p><p class="text-xs text-slate-500">Wants {{ \App\Support\Role::LABELS[$r['requested']] }}, {{ \Carbon\Carbon::parse($r['registered'])->format('M d, g:i A') }}</p></div>
                    <x-status-badge :status="$r['verified'] ? 'Verified' : 'Not verified'" />
                </li>
            @endforeach
        </ul>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between"><h3 class="font-semibold text-slate-900">Offline devices</h3><a href="{{ route('settings.index') }}" class="text-sm text-emerald-800 hover:underline">Details</a></div>
        <ul class="mt-3 space-y-3 text-sm">
            @foreach ($devices as $d)
                <li class="flex items-center justify-between gap-3">
                    <div><p class="font-medium text-slate-800">{{ $d['label'] }}</p><p class="text-xs text-slate-500">{{ $d['waiting'] }} waiting, last sync {{ \Carbon\Carbon::parse($d['last_sync'])->format('M d, g:i A') }}</p></div>
                    <x-status-badge :status="$d['state']" />
                </li>
            @endforeach
        </ul>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between"><h3 class="font-semibold text-slate-900">Data alerts</h3><a href="{{ route('haulers.index', ['tab' => 'trucks']) }}" class="text-sm text-emerald-800 hover:underline">Hauler management</a></div>
        <ul class="mt-3 space-y-2 text-sm">
            @forelse ($alerts as $t)
                @foreach (array_filter($t['cert_list'], fn ($c) => $c['status'] !== 'Valid') as $c)
                    <li class="flex items-center justify-between gap-3"><span>{{ $t['plate'] }}: {{ $c['name'] }} ({{ $c['expiry'] }})</span><x-status-badge :status="$c['status']" /></li>
                @endforeach
            @empty
                <li class="text-slate-500">All truck certificates are valid.</li>
            @endforelse
            @foreach ($driverAlerts as $d)
                <li class="flex items-center justify-between gap-3"><span>{{ $d['name'] }}: license ({{ \Carbon\Carbon::parse($d['license_expiry'])->format('M d, Y') }})</span><x-status-badge :status="\App\Support\DemoData::certStatus($d['license_expiry'])" /></li>
            @endforeach
        </ul>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between"><h3 class="font-semibold text-slate-900">Recent audit and security events</h3><a href="{{ route('audit.index') }}" class="text-sm text-emerald-800 hover:underline">Audit trail</a></div>
        <ul class="mt-3 space-y-3 text-sm">
            @foreach ($audit as $e)
                <li><p class="text-slate-800">{{ $e['event'] }}: {{ $e['details'] }}</p><p class="text-xs text-slate-500">{{ $e['user'] }}, {{ \Carbon\Carbon::parse($e['at'])->format('M d, g:i A') }}</p></li>
            @endforeach
        </ul>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 lg:col-span-2">
        <div class="flex items-center justify-between"><h3 class="font-semibold text-slate-900">Recent ATL activity</h3><a href="{{ route('atl.index') }}" class="text-sm text-emerald-800 hover:underline">ATL records</a></div>
        <ul class="mt-3 divide-y divide-slate-100 text-sm">
            @foreach ($recentAtls as $a)
                <li class="flex items-center justify-between gap-3 py-2"><a href="{{ route('atl.show', $a['id']) }}" class="text-slate-800 hover:underline">{{ $a['atl_no'] }}, {{ $a['plate'] }}, {{ $a['destination'] }}</a><x-status-badge :status="$a['status']" /></li>
            @endforeach
        </ul>
    </section>
</div>
