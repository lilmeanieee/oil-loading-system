@props(['status', 'label' => null])
@php
    $color = [
        'Draft'        => 'bg-slate-100 text-slate-700',
        'Submitted'    => 'bg-orange-100 text-orange-700',
        'Approved'     => 'bg-emerald-100 text-emerald-800',
        'Declined'     => 'bg-red-100 text-red-800',
        'Loading'      => 'bg-amber-100 text-amber-800',
        'Completed'    => 'bg-slate-200 text-slate-800',
        'Active'       => 'bg-emerald-100 text-emerald-800',
        'Inactive'     => 'bg-slate-200 text-slate-700',
        'Pending'      => 'bg-orange-100 text-orange-700',
        'Rejected'     => 'bg-red-100 text-red-800',
        'Verified'     => 'bg-emerald-100 text-emerald-800',
        'Not verified' => 'bg-amber-100 text-amber-800',
        'Eligible'     => 'bg-emerald-100 text-emerald-800',
        'Not eligible' => 'bg-red-100 text-red-800',
        'Expiring soon'=> 'bg-amber-100 text-amber-800',
        'OK'           => 'bg-emerald-100 text-emerald-800',
        'Offline'      => 'bg-amber-100 text-amber-800',
        'Conflict'     => 'bg-red-100 text-red-800',
    ][$status] ?? 'bg-slate-100 text-slate-700';
@endphp
<span class="inline-block whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium {{ $color }}">{{ $label ?? $status }}</span>
