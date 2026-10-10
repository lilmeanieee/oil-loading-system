<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none !important}</style>
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen bg-gray-100" x-data="{ sidebar: false }">
            @include('layouts.navigation')

            <div class="flex min-w-0 flex-1 flex-col">
                <div class="flex items-center justify-between bg-slate-900 px-4 py-3 lg:hidden print:hidden">
                    <span class="font-semibold text-amber-400">Oil Loading System</span>
                    <button type="button" @click="sidebar = true" class="rounded px-2 py-1 text-sm text-slate-200 hover:bg-slate-800">Menu</button>
                </div>

                @isset($header)
                    <header class="bg-white shadow print:shadow-none">
                        <div class="px-4 py-5 sm:px-6 lg:px-8">{{ $header }}</div>
                    </header>
                @endisset

                <main class="flex-1">{{ $slot }}</main>
            </div>
        </div>
    </body>
</html>
