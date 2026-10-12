<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Oil Loading System</h1>
        <p class="mt-1 text-sm text-slate-600">Sign in with the account your administrator gave you.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        
    <div class="mt-4">
    <x-input-label for="password" :value="__('Password')" />

    <div class="relative">
        <x-text-input
            id="password"
            class="mt-1 block w-full pr-10"
            type="password"
            name="password"
            required
            autocomplete="current-password"
        />

        <button
            type="button"
            id="togglePassword"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 focus:outline-none"
            aria-label="Show password"
            aria-pressed="false"
        >
            <svg
                id="eyeIcon"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
            </svg>
        </button>
    </div>

    
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const password = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');
        const eye = document.getElementById('eyeIcon');

        if (!password || !toggle || !eye) return;

        toggle.addEventListener('click', function () {
            const showPassword = password.type === 'password';

            password.type = showPassword ? 'text' : 'password';

            toggle.setAttribute(
                'aria-label',
                showPassword ? 'Hide password' : 'Show password'
            );

            toggle.setAttribute('aria-pressed', String(showPassword));

            eye.innerHTML = showPassword
                ? `<path stroke-linecap="round" stroke-linejoin="round"
                     d="M3.98 8.223A10.477 10.477 0 001.934 12
                     C3.226 16.338 7.244 19.5 12 19.5
                     c.993 0 1.953-.138 2.852-.395M6.228 6.228
                     A10.45 10.45 0 0112 4.5
                     c4.756 0 8.774 3.162 10.066 7.5
                     a10.523 10.523 0 01-4.293 5.774" />
                   <path stroke-linecap="round" stroke-linejoin="round"
                     d="M9.88 9.88a3 3 0 104.24 4.24M3 3l18 18" />`
                : `<path stroke-linecap="round" stroke-linejoin="round"
                     d="M2.036 12.322a1.012 1.012 0 010-.639
                     C3.423 7.51 7.36 4.5 12 4.5
                     c4.638 0 8.573 3.007 9.963 7.178
                     .07.207.07.431 0 .639
                     C20.577 16.49 16.64 19.5 12 19.5
                     c-4.638 0-8.573-3.007-9.963-7.178z" />
                   <path stroke-linecap="round" stroke-linejoin="round"
                     d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />`;
        });
    });
</script>


    <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>


        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-amber-600 shadow-sm focus:ring-amber-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">Keep me signed in on this computer</span>
            </label>
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full rounded bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">Sign in</button>
        </div>
    </form>
</x-guest-layout>
