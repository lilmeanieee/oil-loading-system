@php
    $me = auth()->user();
    $items = $me->notifications()->latest()->take(10)->get()->map(fn ($n) => [
        'id'   => $n->id,
        'text' => $n->data['text'] ?? '',
        'url'  => $n->data['url'] ?? null,
        'at'   => $n->created_at->toIso8601String(),
        'read' => $n->read_at !== null,
    ])->values();
@endphp
<div class="relative"
     data-realtime-user="{{ $me->id }}"
     x-data="{
        open: false,
        items: @js($items),
        toast: null,
        timer: null,
        get unread() { return this.items.filter(i => !i.read).length },
        when(iso) { return new Date(iso).toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }) },
        push(n) {
            if (this.items.some(i => i.id === n.id)) return;
            this.items.unshift({ id: n.id, text: n.text, url: n.url || null, at: n.at, read: false });
            this.items = this.items.slice(0, 10);
            this.toast = n;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.toast = null, 8000);
        },
        markAllRead() {
            fetch('{{ route('notifications.read') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).then(r => { if (r.ok) this.items = this.items.map(i => ({ ...i, read: true })) });
        }
     }"
     @notification-received.window="push($event.detail)"
     @keydown.escape.window="open = false">

    <button type="button" @click="open = !open" class="relative rounded-full p-2 hover:bg-white/10" aria-label="Notifications" :aria-expanded="open">
        <x-icon name="bell" class="h-6 w-6" />
        <span x-show="unread" x-cloak x-text="unread > 9 ? '9+' : unread" class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-orange-500 px-1 text-[10px] font-semibold text-white"></span>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-50 mt-2 w-80 rounded-lg bg-white p-3 text-sm text-slate-800 shadow-xl">
        <div class="mb-2 flex items-center justify-between">
            <p class="font-semibold">Notifications</p>
            <button type="button" x-show="unread" @click="markAllRead()" class="text-xs text-[#1c3d34] underline">Mark all as read</button>
        </div>
        <template x-for="i in items" :key="i.id">
            <a :href="i.url || '#'" class="block border-t border-slate-100 py-2 hover:bg-slate-50" :class="i.read ? '' : 'bg-emerald-50/60'">
                <p x-text="i.text"></p>
                <p class="text-xs text-slate-500" x-text="when(i.at)"></p>
            </a>
        </template>
        <p x-show="!items.length" class="border-t border-slate-100 py-2 text-slate-500">Nothing new.</p>
    </div>

    <div x-show="toast" x-cloak x-transition class="fixed right-4 top-4 z-[60] w-80 rounded-lg border border-emerald-200 bg-white p-4 text-sm text-slate-800 shadow-xl" role="status" aria-live="polite">
        <p class="font-semibold text-[#1c3d34]">New notification</p>
        <p class="mt-1" x-text="toast && toast.text"></p>
        <div class="mt-3 flex gap-4">
            <a x-show="toast && toast.url" :href="toast && toast.url" class="font-medium text-[#1c3d34] underline">View</a>
            <button type="button" @click="toast = null" class="text-slate-500 hover:text-slate-800">Dismiss</button>
        </div>
    </div>
</div>