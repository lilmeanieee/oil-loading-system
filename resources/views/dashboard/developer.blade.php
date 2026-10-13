{{-- Developer: the Operations overview followed by the full Admin overview. --}}
@include('dashboard.staff')

<div class="pt-2">
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Admin overview</h3>
    <div class="space-y-5">
        @include('dashboard.admin')
    </div>
</div>
