@include('operations._boot')

<div x-data="opsApp(window.OPS_SEED, { urls: @js(['approvals' => route('approvals.index'), 'documents' => route('documents.index'), 'hauler' => route('haulers.index')]) })" class="space-y-5">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-slate-600">What needs a decision or a printout right now.</p>
        <span class="text-sm text-slate-500" x-text="today"></span>
    </div>

    {{-- Tiles --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <a href="{{ route('approvals.index') }}?tab=pending" class="rounded-lg border bg-white p-4 hover:border-emerald-700" :class="c.pend.length ? 'border-amber-300' : 'border-slate-200'">
            <div class="text-sm text-slate-600">Pending ATL reviews</div>
            <div class="mt-1 text-3xl font-semibold text-slate-900" x-text="c.pend.length"></div>
            <div class="text-xs text-slate-500" x-text="c.pend.filter(a => a.st === 'resubmitted').length + ' resubmitted, ' + c.pend.filter(a => flagged(a)).length + ' flagged'"></div>
        </a>
        <a href="{{ route('approvals.index') }}?tab=approved" class="rounded-lg border border-slate-200 bg-white p-4 hover:border-emerald-700">
            <div class="text-sm text-slate-600">Approved ATLs</div>
            <div class="mt-1 text-3xl font-semibold text-slate-900" x-text="c.ap.length"></div>
            <div class="text-xs text-slate-500" x-text="c.done.length + ' fully released'"></div>
        </a>
        <a href="{{ route('documents.index') }}?f=lt" class="rounded-lg border bg-white p-4 hover:border-emerald-700" :class="c.lt.length ? 'border-amber-300' : 'border-slate-200'">
            <div class="text-sm text-slate-600">Loading Tickets awaiting printing</div>
            <div class="mt-1 text-3xl font-semibold text-slate-900" x-text="c.lt.length"></div>
            <div class="text-xs text-slate-500">Print before the truck loads</div>
        </a>
        <a href="{{ route('documents.index') }}?f=release" class="rounded-lg border bg-white p-4 hover:border-emerald-700" :class="(c.rel.length + c.ho.length) ? 'border-sky-300' : 'border-slate-200'">
            <div class="text-sm text-slate-600">DR and WC awaiting release</div>
            <div class="mt-1 text-3xl font-semibold text-slate-900" x-text="c.rel.length + c.ho.length"></div>
            <div class="text-xs text-slate-500" x-text="c.ho.length + ' printed, not yet handed over'"></div>
        </a>
    </div>

    {{-- Release workflow --}}
    <section class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="mb-3 flex items-baseline justify-between"><h3 class="font-semibold text-slate-900">Release workflow</h3><span class="text-xs text-slate-500">Trucks at each step today</span></div>
        <ol class="grid gap-3 sm:grid-cols-4">
            <template x-for="s in [['Step 1','Print LT',c.lt.length,'lt'],['Step 2','Driver returns',c.ret.length,'return'],['Step 3','Prepare DR and WC',c.rel.length,'release'],['Step 4','Hand over to driver',c.ho.length,'handover']]" :key="s[0]">
                <li><a :href="u.documents + '?f=' + s[3]" class="block rounded-lg border border-slate-200 p-3 hover:border-emerald-700">
                    <div class="text-xs uppercase tracking-wide text-slate-500" x-text="s[0]"></div>
                    <div class="text-sm font-medium text-slate-900" x-text="s[1]"></div>
                    <div class="mt-1 text-2xl font-semibold text-[#1c3d34]" x-text="s[2]"></div>
                </a></li>
            </template>
        </ol>
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        {{-- Needs action --}}
        <section class="rounded-lg border border-slate-200 bg-white p-4">
            <h3 class="mb-3 font-semibold text-slate-900">Needs action</h3>
            @php
                $groups = [
                    ['ATLs to review', 'c.pend', 'review', "u.approvals + '?tab=' + a.st + '&id=' + a.id", 'Review'],
                    ['Loading Tickets to print', 'c.lt', 'lt', "u.documents + '?f=lt&id=' + a.id", 'Open'],
                    ['DR and WC to release', '[...c.rel, ...c.ho]', 'dr', "u.documents + '?f=' + stage(a) + '&id=' + a.id", 'Open'],
                ];
            @endphp
            @foreach ($groups as [$title, $list, $kind, $href, $label])
                <div class="{{ $loop->first ? '' : 'mt-4 border-t border-slate-100 pt-4' }}">
                    <div class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-700">{{ $title }} <span :class="pill('')" x-text="{{ $list }}.length"></span></div>
                    <template x-for="a in {{ $list }}.slice(0, 3)" :key="a.id">
                        <div class="flex items-center gap-3 py-1.5 text-sm">
                            <b class="shrink-0 text-slate-900" x-text="short(a.id)"></b>
                            <span class="min-w-0 flex-1 truncate text-slate-600" x-text="qrow(a, '{{ $kind }}')"></span>
                            <a :href="{{ $href }}" class="shrink-0 rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">{{ $label }}</a>
                        </div>
                    </template>
                    <p x-show="{{ $list }}.length > 3" class="text-xs text-slate-500" x-text="'and ' + ({{ $list }}.length - 3) + ' more'"></p>
                    <p x-show="!{{ $list }}.length" class="text-sm text-slate-500">Nothing waiting.</p>
                </div>
            @endforeach
        </section>

        <div class="space-y-5">
            <section class="rounded-lg border border-slate-200 bg-white p-4">
                <div class="mb-3 flex items-center gap-2"><h3 class="font-semibold text-slate-900">Alerts</h3><span :class="pill(alerts.length ? 'warn' : 'ok')" x-text="alerts.length"></span></div>
                <template x-for="(x, i) in alerts" :key="i">
                    <a :href="x.href" class="flex items-start gap-2 py-1.5 text-sm hover:underline">
                        <span :class="pill(x.k)" x-text="x.k === 'bad' ? 'Action' : 'Watch'"></span>
                        <span class="min-w-0 break-words text-slate-700" x-text="x.x"></span>
                    </a>
                </template>
                <p x-show="!alerts.length" class="text-sm text-slate-500">No alerts.</p>
            </section>
            <section class="rounded-lg border border-slate-200 bg-white p-4">
                <h3 class="mb-3 font-semibold text-slate-900">Recent activity</h3>
                <ul class="space-y-2">
                    <template x-for="(x, i) in d.act.slice(0, 8)" :key="i">
                        <li class="flex items-start gap-3 text-sm">
                            <time class="w-12 shrink-0 text-xs text-slate-500" x-text="x.t"></time>
                            <i class="mt-1.5 h-2 w-2 shrink-0 rounded-full" :class="{ 'bg-emerald-600': x.k === 'ok', 'bg-red-500': x.k === 'bad', 'bg-sky-500': x.k === 'info' }"></i>
                            <span class="min-w-0 break-words text-slate-700" x-text="x.x"></span>
                        </li>
                    </template>
                </ul>
            </section>
        </div>
    </div>
</div>
