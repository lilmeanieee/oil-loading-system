<x-guest-layout>
    <div class="mb-5">
        <p class="text-xs text-slate-500">Step 2 of 3</p>
        <h1 class="text-xl font-semibold text-[#1c3d34]">Verify your email</h1>
        <p class="mt-1 text-sm text-slate-600">We sent a 6-digit code to <strong>{{ $email }}</strong>. This confirms that the address is yours.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />
    <form method="POST" action="{{ route('register.confirm') }}" class="space-y-4"
          x-data="{ code: '', left: 600, wait: 60, tries: 5,
                    init() { setInterval(() => { if (this.left > 0) this.left--; if (this.wait > 0) this.wait--; }, 1000) },
                    get clock() { return String(Math.floor(this.left / 60)).padStart(2, '0') + ':' + String(this.left % 60).padStart(2, '0') } }">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700" for="code">Verification code</label>
            <input id="code" name="code" x-model="code" inputmode="numeric" maxlength="6" pattern="\d{6}" autocomplete="one-time-code" autofocus required
                   class="mt-1 block w-full rounded border-slate-300 text-center text-2xl tracking-[0.5em] focus:border-emerald-700 focus:ring-emerald-700">
            <x-input-error :messages="$errors->get('code')" class="mt-1" />
        </div>

        <p class="text-sm text-slate-600" aria-live="polite">
            <span x-show="left > 0">The code expires in <strong x-text="clock"></strong>.</span>
            <span x-show="left <= 0" x-cloak class="text-red-700">The code expired. Request a new one.</span>
            You have <strong x-text="tries"></strong> tries left.
        </p>

        <button type="submit" :disabled="code.length !== 6 || left <= 0" class="w-full rounded-lg bg-[#1c3d34] px-4 py-2.5 text-sm font-medium text-white hover:bg-[#2a5a4c] disabled:cursor-not-allowed disabled:opacity-50">Verify</button>

        <div class="text-center text-sm">
            <button type="submit" formaction="{{ route('register.resend') }}" formnovalidate :disabled="wait > 0" class="text-[#1c3d34] underline disabled:cursor-not-allowed disabled:text-slate-400 disabled:no-underline">">
                <span x-show="wait > 0">Send a new code in <span x-text="wait"></span>s</span><span x-show="wait <= 0" x-cloak>Send a new code</span>
            </button>
        </div>
        
    </form>
</x-guest-layout>
