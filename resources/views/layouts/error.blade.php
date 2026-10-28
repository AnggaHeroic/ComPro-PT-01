<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#cb1e1b">
    <link rel="icon" href="{{ asset('images/logo.png') }}">

    <title>@yield('title', 'PT. Putra Maju Sukses')</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

{{-- Layout ringkas tanpa akses database, dipakai bila halaman error tidak bisa memakai layout utama. --}}
<body>
    <header class="border-b border-slate-200 bg-white">
        <div class="container-site py-4">
            <a href="{{ url('/') }}" aria-label="PT. Putra Maju Sukses">
                <img src="{{ asset('images/logo.png') }}" alt="PT. Putra Maju Sukses" class="h-10 w-auto">
            </a>
        </div>
    </header>

    <main id="main-content">
        @yield('content')
    </main>
</body>

</html>
