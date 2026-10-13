{{-- Offline indicator, waiting-to-sync list and messages. Filled in by public/pwa.js --}}
<div class="fixed bottom-4 right-4 z-50 print:hidden">
    <button id="pwa-pill" type="button" hidden aria-expanded="false" aria-controls="pwa-panel"
            class="rounded-full bg-slate-900 px-4 py-2 text-sm text-white shadow-lg ring-1 ring-emerald-700 hover:bg-slate-800">
        <span id="pwa-pill-text"></span>
    </button>
</div>

<section id="pwa-panel" hidden aria-label="Offline sync" class="fixed bottom-16 right-4 z-50 w-80 max-w-[calc(100vw-2rem)] rounded border border-slate-200 bg-white p-4 text-sm shadow-xl print:hidden">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Waiting to sync</h2>
        <button id="pwa-panel-close" type="button" class="text-slate-500 hover:text-slate-800">Close</button>
    </div>
    <p id="pwa-panel-note" class="mt-1 text-slate-600"></p>
    <ul id="pwa-list" class="mt-3 space-y-2"></ul>
    <div class="mt-3 flex justify-end">
        <button id="pwa-sync-now" type="button" class="rounded bg-[#1c3d34] px-3 py-1.5 font-medium text-white hover:bg-[#2a5a4c]">Sync now</button>
    </div>
</section>

<template id="pwa-item-tpl">
    <li class="rounded border border-slate-200 p-2">
        <p class="font-medium text-slate-800" data-label></p>
        <p class="text-xs text-slate-500" data-meta></p>
        <p class="text-xs text-red-700" data-error hidden></p>
        <div class="mt-1 flex gap-3 text-xs">
            <button type="button" data-retry hidden class="font-medium text-emerald-800 hover:underline">Try again</button>
            <button type="button" data-remove class="font-medium text-red-700 hover:underline">Remove</button>
        </div>
    </li>
</template>

<div id="pwa-toast" role="status" aria-live="polite" hidden class="fixed bottom-4 left-1/2 z-50 max-w-[90vw] -translate-x-1/2 rounded bg-slate-900 px-4 py-2 text-sm text-white shadow-lg print:hidden"></div>
