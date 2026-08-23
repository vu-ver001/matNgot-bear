<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title') - {{ config('app.name', 'Mật Ngọt Bear') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dancing-script:500,600,700|montserrat:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-body font-sans antialiased">
        <div
            class="auth-page min-h-screen"
            style="--auth-hero-image: url('{{ asset('images/auth/bear-hero.png') }}'); --auth-panel-image: url('{{ asset('images/auth/auth-panel-background.png') }}')"
        >
            <a
                href="#"
                class="auth-home-link"
                data-placeholder-link
                title="Liên kết trang chủ sẽ được cập nhật sau"
            >
                <span>Mật Ngọt Bear</span>
                <span aria-hidden="true">⌂</span>
            </a>

            @include('auth.sharedKT.hero')

            <main class="auth-panel flex min-h-screen justify-center">
                <section class="auth-card @yield('card-class')">
                    @yield('content')
                </section>
            </main>
        </div>
    </body>
</html>
