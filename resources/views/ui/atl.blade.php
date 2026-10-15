{{-- ATL User pages. $page: dash | records | create. DEMO ONLY: see public/ui-atl.js. --}}
@include('ui._assets')
<div id="px" class="px"></div>
<script src="{{ asset('ui-atl.js') }}?v=1"></script>
<script>
    PXAtl.mount({
        page: @js($page),
        tab: @js($tab ?? 'draft'),
        seed: @js($opsSeed),
        urls: @js([
            'dashboard' => route('dashboard'),
            'create'    => route('atl.create'),
            'records'   => route('atl.mine'),
        ]),
    });
</script>
