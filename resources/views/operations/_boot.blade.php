{{-- Loads the shared Operations script and sample data. DEMO ONLY (see App\Support\OpsData). --}}
<script>window.OPS_SEED = @json($opsSeed);</script>
<script src="{{ asset('ops.js') }}?v=1"></script>
