<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PA Fuel 118') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none !important}</style>
        <x-pwa-head />
    </head>
    <body class="font-sans antialiased" data-pwa-auth="1" data-pwa-home="{{ route('dashboard') }}">
        <div class="flex min-h-screen flex-col bg-gray-50" x-data="{ sidebar: false }">
            @include('layouts.header')

            <div class="flex flex-1">
                @include('layouts.navigation')

                <div class="min-w-0 flex-1">
                    @isset($header)
                        <header class="bg-white shadow-sm print:shadow-none">
                            <div class="px-4 py-5 sm:px-6 lg:px-8">{{ $header }}</div>
                        </header>
                    @endisset

                    <main>{{ $slot }}</main>
                </div>
            </div>
        </div>
        <x-pwa-status />
    </body>
</html>
