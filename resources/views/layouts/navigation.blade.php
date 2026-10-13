<div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-black/40 lg:hidden print:hidden"></div>

<aside class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col bg-[#eef5f1] pt-16 transition-transform lg:static lg:translate-x-0 lg:pt-0 print:hidden"
       :class="sidebar ? 'translate-x-0' : '-translate-x-full'">
    <div class="flex items-center justify-between px-5 pt-4 lg:hidden">
        <span class="font-semibold text-[#1c3d34]">Menu</span>
        <button type="button" @click="sidebar = false" class="text-sm text-slate-600 hover:text-slate-900">Close</button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Main menu">
        @foreach (\App\Support\Role::menu() as [$label, $route, $pattern, $icon])
            <a href="{{ route($route) }}" @if (request()->routeIs(...explode('|', $pattern))) aria-current="page" @endif
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm {{ request()->routeIs(...explode('|', $pattern)) ? 'bg-[#d9eadf] font-medium text-[#1c3d34]' : 'text-slate-700 hover:bg-[#e2efe8]' }}">
                <x-icon :name="$icon" class="h-5 w-5 shrink-0" />
                <span>{{ $label }}</span>
            </a>
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
