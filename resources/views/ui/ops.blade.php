{{-- Operations pages. $page: dash | atl | alloc | docs. DEMO ONLY: see public/ui-ops.js. --}}
@include('ui._assets')
<div id="px" class="px"></div>
<script src="{{ asset('ui-ops.js') }}?v=1"></script>
<script>
    PXOps.mount({
        page: @js($page),
        id: @js($atlId ?? null),
        seed: @js($opsSeed),
        urls: @js([
            'approvals' => route('approvals.index'),
            'documents' => route('documents.index'),
            'allocate'  => route('approvals.allocate', ['id' => '__ID__']),
        ]),
    });
</script>
