<x-guest-layout>
    <div class="mb-5">
        <p class="text-xs text-slate-500">Step 1 of 3</p>
        <h1 class="text-xl font-semibold text-[#1c3d34]">Request an account</h1>
        <p class="mt-1 text-sm text-slate-600">Enter your details. An administrator must approve your account before you can use the system.</p>
    </div>

    @php $in = 'mt-1 block w-full rounded border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700'; $lb = 'block text-sm font-medium text-slate-700'; @endphp

    <form method="POST" action="{{ route('register.store') }}" x-data="{ show: false }" class="space-y-4">
        @csrf
        <div><label class="{{ $lb }}" for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" class="{{ $in }}" required autofocus autocomplete="name"><x-input-error :messages="$errors->get('name')" class="mt-1" /></div>
        <div><label class="{{ $lb }}" for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" class="{{ $in }}" required autocomplete="username"><x-input-error :messages="$errors->get('email')" class="mt-1" /></div>
        <div>
            <label class="{{ $lb }}" for="requested">Role you are requesting</label>
            <select id="requested" name="requested" class="{{ $in }}" required>
                <option value="atl_user" @selected(old('requested') === 'atl_user')>ATL User</option>
                <option value="operations" @selected(old('requested') === 'operations')>Operations</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">The administrator assigns your final role.</p>
            <x-input-error :messages="$errors->get('requested')" class="mt-1" />
        </div>
        <div>
            <label class="{{ $lb }}" for="password">Password</label>
            <div class="relative">
                <input id="password" name="password" x-bind:type="show ? 'text' : 'password'" type="password" class="{{ $in }} pe-16" required minlength="8" autocomplete="new-password">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 mt-1 px-3 text-sm text-slate-600 hover:text-slate-900" x-text="show ? 'Hide' : 'Show'"></button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>
        <div><label class="{{ $lb }}" for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" x-bind:type="show ? 'text' : 'password'" type="password" class="{{ $in }}" required autocomplete="new-password"></div>

        <button type="submit" class="w-full rounded-lg bg-[#1c3d34] px-4 py-2.5 text-sm font-medium text-white hover:bg-[#2a5a4c]">Continue</button>
        <p class="text-center text-sm text-slate-600">Already approved? <a href="{{ route('login') }}" class="font-medium text-[#1c3d34] underline">Sign in</a></p>
    </form>
</x-guest-layout>
