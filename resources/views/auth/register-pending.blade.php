<x-guest-layout>
    <div class="mb-5">
        <p class="text-xs text-slate-500">Step 3 of 3</p>
        <h1 class="text-xl font-semibold text-[#1c3d34]">Waiting for admin approval</h1>
        <p class="mt-1 text-sm text-slate-600">Your email{{ $email ? ' ('.$email.')' : '' }} is verified. You cannot open the ATL pages until an administrator approves your account.</p>
    </div>

    <ol class="space-y-3 text-sm text-slate-700">
        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-800">1</span>Account details sent</li>
        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-800">2</span>Email verified</li>
        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-orange-100 text-xs font-semibold text-orange-700">3</span><span><strong>Admin review</strong>: the administrator assigns your role and activates your account, or rejects the request with a reason.</span></li>
        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-500">4</span>You sign in and see the dashboard for your role</li>
    </ol>

    <a href="{{ route('login') }}" class="mt-6 block w-full rounded-lg border border-[#1c3d34] px-4 py-2.5 text-center text-sm font-medium text-[#1c3d34] hover:bg-[#eef5f1]">Back to sign in</a>
</x-guest-layout>
