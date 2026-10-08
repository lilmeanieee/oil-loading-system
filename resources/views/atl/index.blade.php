<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Authority to Load (ATL)</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4">
        <p>Welcome, {{ Auth::user()->name }}. ATL list will appear here.</p>
    </div>
</x-app-layout>