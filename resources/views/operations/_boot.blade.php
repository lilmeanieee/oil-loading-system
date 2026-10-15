{{-- Loads the shared Operations script and data. Haulers, drivers and trucks come from the database; the rest is still browser data (see App\Support\OpsData). --}}
@php
    $opsApi = [
        'csrf'    => csrf_token(),
        'haulers' => route('haulers.store', [], false),
        'trucks'  => route('trucks.store', [], false),
        'drivers' => \Illuminate\Support\Str::beforeLast(route('drivers.update', ['id' => 0], false), '/'),
    ];
@endphp
<script>
    window.OPS_SEED = @json($opsSeed);
    window.OPS_API = @json($opsApi);
</script>
<script src="{{ asset('ops.js') }}?v=5.2"></script>