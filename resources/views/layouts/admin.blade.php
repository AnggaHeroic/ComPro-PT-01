<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>Admin — {{ config('app.name') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-surface text-gray-900">
        <div x-data="{ sidebar: false }" @keydown.escape.window="sidebar = false">
            @include('layouts.navigation')

            <div class="lg:pl-64">
                {{-- Mobile top bar --}}
                <div class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-gray-200 bg-white px-4 lg:hidden">
                    <button type="button" @click="sidebar = true" aria-label="Buka menu" class="-ms-1 rounded-md p-2 text-gray-600 hover:bg-gray-100 focus:outline-none">
                        <x-icons.menu class="h-5 w-5" />
                    </button>
                    <x-application-logo class="h-8 w-auto mix-blend-multiply" />
                </div>

                <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-10 lg:py-10">
                    @isset($header)
                        <header class="mb-8 border-b border-gray-200 pb-6">
                            {{ $header }}
                        </header>
                    @endisset

                    <x-admin.flash />

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
