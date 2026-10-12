@php
    $can = fn ($p) => \App\Support\Role::can($p);
    $groups = [
        'Main' => [['Dashboard', 'dashboard', 'dashboard', true]],
        'ATL' => [
            ['Create ATL', 'atl.create', 'atl.create', $can('atl.create')],
            ['ATL records', 'atl.index', 'atl.index', $can('atl.view')],
            ['Approvals', 'approvals.index', 'approvals.*', $can('atl.approve')],
        ],
        'Records' => [
            ['Trucks', 'trucks.index', 'trucks.*', $can('trucks.view')],
            ['Documents', 'documents.index', 'documents.*', $can('documents.menu')],
        ],
    ];
@endphp

<div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-black/40 lg:hidden print:hidden"></div>

<aside class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-900 transition-transform lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:translate-x-0 print:hidden"
       :class="sidebar ? 'translate-x-0' : '-translate-x-full'">
    <div class="flex items-center justify-between px-5 py-5">
        <a href="{{ route('dashboard') }}" class="font-semibold text-amber-400">Oil Loading System</a>
        <button type="button" @click="sidebar = false" class="text-sm text-slate-400 hover:text-white lg:hidden">Close</button>
    </div>

    <nav class="flex-1 space-y-5 overflow-y-auto px-3 pb-4" aria-label="Main menu">
        @foreach ($groups as $heading => $items)
            @php $items = array_filter($items, fn ($l) => $l[3]); @endphp
            @if ($items)
                <div>
                    <p class="px-3 pb-1 text-xs text-slate-500">{{ $heading }}</p>
                    @foreach ($items as [$label, $route, $pattern])
                        <a href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif
                           class="block rounded px-3 py-2 text-sm {{ request()->routeIs($pattern) ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">{{ $label }}</a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>

    <div class="space-y-3 border-t border-slate-700 px-4 py-4">
        <button type="button" id="pwa-install" hidden class="w-full rounded border border-amber-400 px-3 py-1.5 text-sm text-amber-300 hover:bg-slate-800">Install app on this device</button>
        {{-- DEMO ONLY: preview the pages as each role --}}
        @if (\App\Support\Role::isDeveloper())
    <form method="POST" action="{{ route('developer.view-as') }}" data-offline="online" data-pwa-clear>
        @csrf
        <label for="view_as" class="mb-1 block text-xs text-slate-400">Viewing as</label>
        <select id="view_as" name="role" onchange="this.form.requestSubmit()" class="w-full rounded border-0 bg-slate-800 py-1.5 text-sm text-slate-100 focus:ring-amber-500">
            @foreach (\App\Support\Role::LABELS as $key => $label)
                <option value="{{ $key }}" @selected(\App\Support\Role::current() === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </form>
@else
    <div>
        <p class="mb-1 text-xs text-slate-400">Viewing as</p>
        <p class="w-full rounded bg-slate-800 px-3 py-1.5 text-sm text-slate-100">{{ \App\Support\Role::LABELS[\App\Support\Role::current()] }}</p>
    </div>
@endif
        <div class="flex items-center justify-between text-sm">
            <a href="{{ route('profile.edit') }}" class="truncate text-slate-300 hover:text-white">{{ Auth::user()->name }}</a>
            <form method="POST" action="{{ route('logout') }}" data-offline="online" data-pwa-clear>
                @csrf
                <button type="submit" class="text-slate-400 hover:text-white">Log out</button>
            </form>
        </div>
    </div>
</aside>
