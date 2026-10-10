@props(['status'])
@php
    $color = [
        'Draft'     => 'bg-slate-100 text-slate-700',
        'Submitted' => 'bg-sky-100 text-sky-800',
        'Approved'  => 'bg-emerald-100 text-emerald-800',
        'Declined'  => 'bg-red-100 text-red-800',
        'Loading'   => 'bg-amber-100 text-amber-800',
        'Completed' => 'bg-slate-200 text-slate-800',
    ][$status] ?? 'bg-slate-100 text-slate-700';
@endphp
<span class="inline-block rounded px-2 py-0.5 text-xs font-medium {{ $color }}">{{ $status }}</span>
