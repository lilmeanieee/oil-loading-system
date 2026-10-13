<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900">Hauler Management</h2>
        <p class="mt-1 text-sm text-slate-600">Haulers, trucks and drivers in one place</p>
    </x-slot>

    <div x-data="{
            tab: new URLSearchParams(location.search).get('tab') || @js($tab),
            allowed: @js($tabs),
            q: '',
            init() { if (!this.allowed.includes(this.tab)) this.tab = this.allowed[0]; this.$watch('q', () => this.filter()); this.$watch('tab', () => { this.q = ''; this.filter(); }); },
            filter() {
                const panel = this.$refs[this.tab]; if (!panel) return;
                const term = this.q.toLowerCase().trim();
                const groups = panel.querySelectorAll('table > tbody');
                const items = groups.length > 1 ? [...groups] : [...panel.querySelectorAll('table > tbody > tr')];
                items.forEach(el => { el.style.display = (!term || el.textContent.toLowerCase().includes(term)) ? '' : 'none'; });
            }
        }">
        <div class="mx-auto max-w-6xl px-4 pt-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <nav class="flex gap-1 rounded-lg bg-slate-100 p-1" role="tablist" aria-label="Hauler management sections">
                    @foreach (['haulers' => 'Haulers', 'trucks' => 'Trucks', 'drivers' => 'Drivers'] as $key => $label)
                        @if (in_array($key, $tabs))
                            <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                                    :class="tab === '{{ $key }}' ? 'bg-white text-[#1c3d34] shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="rounded-md px-4 py-2 text-sm font-medium">{{ $label }}</button>
                        @endif
                    @endforeach
                </nav>
                <input type="search" x-model="q" :placeholder="'Search ' + tab + '...'" aria-label="Search records" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700 sm:w-72">
            </div>
        </div>

        @if (in_array('haulers', $tabs))
            <div x-show="tab === 'haulers'" x-ref="haulers" role="tabpanel">@include('hauler-management.partials.haulers')</div>
        @endif
        @if (in_array('trucks', $tabs))
            <div x-show="tab === 'trucks'" x-cloak x-ref="trucks" role="tabpanel">@include('hauler-management.partials.trucks')</div>
        @endif
        @if (in_array('drivers', $tabs))
            <div x-show="tab === 'drivers'" x-cloak x-ref="drivers" role="tabpanel">@include('hauler-management.partials.drivers')</div>
        @endif
    </div>
</x-app-layout>
