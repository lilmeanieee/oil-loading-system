<x-app-layout>
    @php $in = 'w-full rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700'; $lb = 'mb-1 block text-sm font-medium text-slate-700'; @endphp

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900">My Profile</h2>
        <p class="mt-1 text-sm text-slate-600">Account details and security settings</p>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-3xl space-y-5 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-slate-200 bg-white p-5">
                <h3 class="mb-3 font-semibold text-slate-900">Account details</h3>
                <dl class="grid grid-cols-[8rem_1fr] gap-y-2 text-sm">
                    <dt class="text-slate-500">Name</dt><dd>{{ Auth::user()->name }}</dd>
                    <dt class="text-slate-500">Email</dt><dd>{{ Auth::user()->email }}</dd>
                    <dt class="text-slate-500">Role</dt><dd>{{ $role }}</dd>
                    <dt class="text-slate-500">Status</dt><dd><x-status-badge status="Active" /></dd>
                </dl>
                <p class="mt-3 text-xs text-slate-500">Your role is set by an administrator. Ask them if it needs to change.</p>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5">
                <h3 class="mb-3 font-semibold text-slate-900">Security</h3>
                <p class="mb-4 text-sm text-slate-600">You confirm a code sent to your email each time you sign in.</p>
                <form class="grid gap-4 sm:grid-cols-2" x-data="{ show: false }" @submit.prevent="alert('Demo only: the backend will change the password.')">
                    <div class="sm:col-span-2"><label class="{{ $lb }}" for="cur">Current password</label><input id="cur" x-bind:type="show ? 'text' : 'password'" type="password" class="{{ $in }}" required autocomplete="current-password"></div>
                    <div><label class="{{ $lb }}" for="new">New password</label><input id="new" x-bind:type="show ? 'text' : 'password'" type="password" class="{{ $in }}" required minlength="8" autocomplete="new-password"></div>
                    <div><label class="{{ $lb }}" for="conf">Confirm new password</label><input id="conf" x-bind:type="show ? 'text' : 'password'" type="password" class="{{ $in }}" required autocomplete="new-password"></div>
                    <div class="flex items-center justify-between sm:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" x-model="show" class="rounded border-slate-300 text-emerald-800 focus:ring-emerald-700"> Show passwords</label>
                        <button type="submit" class="rounded-lg bg-[#1c3d34] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a5a4c]">Change password</button>
                    </div>
                </form>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5">
                <h3 class="mb-2 font-semibold text-slate-900">This device</h3>
                <p class="text-sm text-slate-700"><span data-pwa-queue-count>0</span> item waiting to sync</p>
                <p class="text-sm text-slate-500">Last successful sync: <span data-pwa-last-sync>not yet</span></p>
            </section>
        </div>
    </div>
</x-app-layout>
