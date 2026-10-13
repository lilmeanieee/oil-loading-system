<x-app-layout>
    @php
        $roles = \App\Support\Role::LABELS;
        $assignable = collect($roles)->except(\App\Support\Role::isDeveloper() ? [] : ['developer'])->all();
        $in = 'rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700';
        $btn = 'rounded-lg px-3 py-1.5 text-sm font-medium';
        $tab = 'border-b-2 px-4 py-2 text-sm font-medium';
    @endphp

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900">User Management</h2>
        <p class="mt-1 text-sm text-slate-600">Registration requests, roles and account activation</p>
    </x-slot>

    <div class="py-6" x-data="{ tab: '{{ count($registrations) ? 'queue' : 'all' }}' }">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('status'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>@endif

            <div class="flex gap-2 border-b border-slate-200" role="tablist">
                <button type="button" role="tab" @click="tab = 'queue'" :aria-selected="tab === 'queue'" class="{{ $tab }}" :class="tab === 'queue' ? 'border-[#1c3d34] text-slate-900' : 'border-transparent text-slate-500'">Registration queue ({{ count($registrations) }})</button>
                <button type="button" role="tab" @click="tab = 'all'" :aria-selected="tab === 'all'" class="{{ $tab }}" :class="tab === 'all' ? 'border-[#1c3d34] text-slate-900' : 'border-transparent text-slate-500'">All users</button>
            </div>

            <section x-show="tab === 'queue'" class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <p class="px-4 pt-4 text-sm text-slate-600">People cannot give themselves Admin access. You assign the role when you approve the request.</p>
                <table class="mt-2 min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3 font-medium">Name</th><th class="px-4 py-3 font-medium">Email</th><th class="px-4 py-3 font-medium">Email status</th><th class="px-4 py-3 font-medium">Requested role</th><th class="px-4 py-3 font-medium">Requested</th><th class="px-4 py-3"></th></tr></thead>
                    @forelse ($registrations as $r)
                        <tbody x-data="{ mode: null }" class="border-t border-slate-100">
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $r['name'] }}</td>
                                <td class="px-4 py-3">{{ $r['email'] }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$r['verified'] ? 'Verified' : 'Not verified'" /></td>
                                <td class="px-4 py-3">{{ $roles[$r['requested']] }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ \Carbon\Carbon::parse($r['registered'])->format('M d, g:i A') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <button type="button" @click="mode = mode === 'approve' ? null : 'approve'" @if (! $r['verified']) disabled title="The email must be verified first" @endif class="{{ $btn }} bg-[#1c3d34] text-white hover:bg-[#2a5a4c] disabled:cursor-not-allowed disabled:opacity-40">Approve</button>
                                    <button type="button" @click="mode = mode === 'reject' ? null : 'reject'" class="{{ $btn }} border border-red-300 text-red-700 hover:bg-red-50">Reject</button>
                                </td>
                            </tr>
                            <tr x-show="mode" x-cloak>
                                <td colspan="6" class="bg-slate-50 px-4 py-4">
                                    <form x-show="mode === 'approve'" method="POST" action="{{ route('users.approve', $r['id']) }}" data-offline="online" class="flex flex-wrap items-end gap-3">
                                        @csrf
                                        <div><label class="mb-1 block text-sm font-medium text-slate-700" for="role_{{ $r['id'] }}">Assign role</label>
                                            <select id="role_{{ $r['id'] }}" name="role" class="{{ $in }}">@foreach ($assignable as $key => $label)<option value="{{ $key }}" @selected($key === $r['requested'])>{{ $label }}</option>@endforeach</select></div>
                                        <button type="submit" class="{{ $btn }} bg-[#1c3d34] text-white hover:bg-[#2a5a4c]">Approve and activate</button>
                                    </form>
                                    <form x-show="mode === 'reject'" method="POST" action="{{ route('users.reject', $r['id']) }}" data-offline="online" class="space-y-2">
                                        @csrf
                                        <label class="block text-sm font-medium text-slate-700" for="reason_{{ $r['id'] }}">Reason for rejecting (the person will see this)</label>
                                        <textarea id="reason_{{ $r['id'] }}" name="reason" rows="2" required minlength="5" class="{{ $in }} w-full"></textarea>
                                        <button type="submit" class="{{ $btn }} bg-red-600 text-white hover:bg-red-700">Reject request</button>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody><tr><td colspan="6" class="px-4 py-8 text-center text-slate-600">No registration requests are waiting.</td></tr></tbody>
                    @endforelse
                </table>
            </section>

            <section x-show="tab === 'all'" x-cloak class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3 font-medium">Name</th><th class="px-4 py-3 font-medium">Email</th><th class="px-4 py-3 font-medium">Role</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Last sign-in</th><th class="px-4 py-3"></th></tr></thead>
                    @foreach ($users as $u)
                        <tbody x-data="{ editing: false }" class="border-t border-slate-100">
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $u['name'] }}</td>
                                <td class="px-4 py-3">{{ $u['email'] }}</td>
                                <td class="px-4 py-3">{{ $roles[$u['role']] }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$u['status']" /></td>
                                <td class="whitespace-nowrap px-4 py-3">{{ \Carbon\Carbon::parse($u['last_login'])->format('M d, g:i A') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <button type="button" @click="editing = !editing" class="{{ $btn }} border border-slate-300 text-slate-700 hover:bg-slate-50">Change role</button>
                                    <form method="POST" action="{{ route('users.toggle', $u['id']) }}" data-offline="online" class="inline">
                                        @csrf
                                        <button type="submit" class="{{ $btn }} border {{ $u['status'] === 'Active' ? 'border-red-300 text-red-700 hover:bg-red-50' : 'border-emerald-700 text-emerald-800 hover:bg-emerald-50' }}">{{ $u['status'] === 'Active' ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                </td>
                            </tr>
                            <tr x-show="editing" x-cloak>
                                <td colspan="6" class="bg-slate-50 px-4 py-3">
                                    <form method="POST" action="{{ route('users.role', $u['id']) }}" data-offline="online" class="flex flex-wrap items-end gap-3">
                                        @csrf
                                        <div><label class="mb-1 block text-sm font-medium text-slate-700" for="newrole_{{ $u['id'] }}">Role for {{ $u['name'] }}</label>
                                            <select id="newrole_{{ $u['id'] }}" name="role" class="{{ $in }}">@foreach ($assignable as $key => $label)<option value="{{ $key }}" @selected($key === $u['role'])>{{ $label }}</option>@endforeach</select></div>
                                        <button type="submit" class="{{ $btn }} bg-[#1c3d34] text-white hover:bg-[#2a5a4c]">Save role</button>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                </table>
            </section>
        </div>
    </div>
</x-app-layout>
