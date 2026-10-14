@php
    $role = \App\Support\Role::current();
    $portal = \App\Support\Role::portal();
    $notes = \App\Support\DemoData::notifications($role);
    // Admins, and Developers on any interface, get the real (live) notifications.
    $live = auth()->check() && (\App\Support\Role::isDeveloper() || \App\Support\Role::can('users.manage'));
@endphp
<header class="relative z-30 bg-[#1c3d34] text-white print:hidden">
    <div class="flex items-center justify-between px-4 py-3 sm:px-6">
        <div class="flex items-center gap-3">
            <button type="button" @click="sidebar = true" class="rounded p-1 hover:bg-white/10 lg:hidden" aria-label="Open menu"><x-icon name="menu" class="h-6 w-6" /></button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-[#1c3d34]"><x-icon name="drop" class="h-6 w-6" /></span>
                <span class="leading-tight">
                    <span class="block text-lg font-semibold">PA Fuel 118</span>
                    @if ($portal)<span class="block text-xs tracking-wide text-emerald-100">{{ $portal }}</span>@endif
                </span>
            </a>
        </div>

        <div class="flex items-center gap-1">
            {{-- Notifications: real and live for Admin/Developer, sample data for the other roles --}}
            @if ($live)
                <x-live-bell />
            @else
                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button type="button" @click="open = !open" class="relative rounded-full p-2 hover:bg-white/10" aria-label="Notifications" :aria-expanded="open">
                        <x-icon name="bell" class="h-6 w-6" />
                        @if (count($notes))<span class="absolute right-1.5 top-1.5 h-2.5 w-2.5 rounded-full bg-orange-400"></span>@endif
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-50 mt-2 w-72 rounded-lg bg-white p-3 text-sm text-slate-800 shadow-xl">
                        <p class="mb-2 font-semibold">Notifications</p>
                        @forelse ($notes as $n)
                            <div class="border-t border-slate-100 py-2"><p>{{ $n['text'] }}</p><p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($n['at'])->format('M d, g:i A') }}</p></div>
                        @empty
                            <p class="border-t border-slate-100 py-2 text-slate-500">Nothing new.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Account menu: profile, Developer "Viewing as" switcher, log out --}}
            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button" @click="open = !open" class="rounded-full p-2 hover:bg-white/10" aria-label="Account menu" :aria-expanded="open"><x-icon name="account" class="h-6 w-6" /></button>
                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-50 mt-2 w-64 space-y-3 rounded-lg bg-white p-4 text-sm text-slate-800 shadow-xl">
                    <div><p class="font-semibold">{{ Auth::user()->name }}</p><p class="text-xs text-slate-500">{{ \App\Support\Role::LABELS[\App\Support\Role::actual()] }}</p></div>
                    <a href="{{ route('account.show') }}" class="block text-[#1c3d34] hover:underline">My profile</a>
                    @if (\App\Support\Role::canSwitch())
                        {{-- Developers can open every interface/dashboard --}}
                        <form method="POST" action="{{ route('developer.view-as') }}" data-offline="online" data-pwa-clear>
                            @csrf
                            <label for="header_view_as" class="mb-1 block text-xs text-slate-500">Viewing as</label>
                            <select id="header_view_as" name="role" onchange="this.form.requestSubmit()" class="w-full rounded border-slate-300 py-1.5 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                                @foreach (\App\Support\Role::LABELS as $key => $label)
                                    @continue($key === 'developer')
                                    <option value="{{ $key }}" @selected($role === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" data-offline="online" data-pwa-clear>
                        @csrf
                        <button type="submit" class="w-full rounded border border-slate-300 py-1.5 text-slate-700 hover:bg-slate-50">Log out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
