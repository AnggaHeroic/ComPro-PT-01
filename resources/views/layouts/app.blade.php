<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Primary Metadata --}}
    <meta
        name="description"
        content="@yield(
            'description',
            'PT. Putra Maju Sukses merupakan perusahaan yang bergerak dalam berbagai bidang perdagangan dan layanan.'
        )"
    >
    <meta name="theme-color" content="#cb1e1b">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    {{-- Page Title --}}
    <title>@yield('title', 'PT. Putra Maju Sukses')</title>

    {{-- Assets --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body>
    {{-- Global Navigation --}}
    @include('components.navbar')

    {{-- Page Content --}}
    <main id="main-content">
        @yield('content')
    </main>

    {{-- Global Footer --}}
    @include('components.footer')
</body>
</html>