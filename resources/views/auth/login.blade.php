<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-[#1c3d34]">PA Fuel 118</h1>
        <p class="mt-1 text-sm text-slate-600">Sign in with the account your administrator approved.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" x-data="{ show: false }">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pe-16" x-bind:type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 text-sm text-slate-600 hover:text-slate-900" x-text="show ? 'Hide' : 'Show'" :aria-pressed="show"></button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-emerald-800 shadow-sm focus:ring-emerald-700" name="remember">
                <span class="ms-2 text-sm text-slate-600">Keep me signed in</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm text-[#1c3d34] underline" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full rounded-lg bg-[#1c3d34] px-4 py-2.5 text-sm font-medium text-white hover:bg-[#2a5a4c] focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">Sign in</button>
        </div>

        <p class="mt-4 text-center text-sm text-slate-600">No account yet? <a href="{{ route('register') }}" class="font-medium text-[#1c3d34] underline">Request an account</a></p>
    </form>
</x-guest-layout>
