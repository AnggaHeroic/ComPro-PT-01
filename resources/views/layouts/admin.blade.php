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
    <body class="bg-gray-100 text-gray-900">
        <div x-data="{ sidebar: false }" @keydown.escape.window="sidebar = false">
            @include('layouts.navigation')

            <div class="lg:pl-64">
                {{-- Mobile top bar (fixed) --}}
                <div class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b-[3px] border-primary bg-white px-4 shadow-sm lg:hidden">
                    <button type="button" @click="sidebar = true" aria-label="Buka menu" class="-ms-1 rounded p-2 text-gray-600 hover:bg-gray-100 focus:outline-none">
                        <x-icons.menu class="h-5 w-5" />
                    </button>
                    <x-application-logo class="h-8 w-auto mix-blend-multiply" />
                </div>

                {{-- Page header (fixed, sejajar tinggi dengan strip logo sidebar) --}}
                @isset($header)
                    <div class="sticky top-14 z-20 border-b-[3px] border-primary bg-white/95 shadow-sm backdrop-blur lg:top-0">
                        <div class="mx-auto flex min-h-[61px] max-w-6xl flex-wrap items-center justify-between gap-x-4 gap-y-2 px-4 py-2 sm:px-6 lg:px-10">
                            <div class="min-w-0 flex-1 border-l-4 border-primary pl-3 [&_h1]:truncate [&_h1]:text-xl [&_h1]:leading-tight [&_p]:mt-0.5 [&_p]:truncate [&_p]:text-xs">
                                {{ $header }}
                            </div>

                            @isset($actions)
                                <div class="flex shrink-0 flex-wrap items-center gap-2">
                                    {{ $actions }}
                                </div>
                            @endisset
                        </div>
                    </div>
                @endisset

                <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-10">
                    <x-admin.flash />

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
