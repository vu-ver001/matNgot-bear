<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#FFFDF9] text-[#1E293B]">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/70 backdrop-blur-md border-b border-amber-100">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-amber-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <x-application-logo class="h-9 w-auto" />
                            <p class="text-base font-bold text-[#8B5A2B]">Mật Ngọt Bear</p>
                        </div>
                        <p class="text-sm text-[#64748B] leading-relaxed">
                            Tiệm gấu bông thủ công dễ thương, mật ngọt như tình yêu và ấm áp như vòng tay gấu bông.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-[#1E293B] mb-3">Hỗ trợ mua hàng</h4>
                        <ul class="space-y-2 text-sm text-[#64748B]">
                            <li>Hướng dẫn đặt hàng</li>
                            <li>Chính sách đổi trả</li>
                            <li>Theo dõi đơn hàng</li>
                            <li>Câu hỏi thường gặp</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-[#1E293B] mb-3">Thanh toán</h4>
                        <ul class="space-y-2 text-sm text-[#64748B]">
                            <li>Thanh toán khi nhận hàng (COD)</li>
                            <li>Chuyển khoản ngân hàng / QR</li>
                            <li>Ví điện tử</li>
                            <li>Thẻ thanh toán</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-[#1E293B] mb-3">Hotline &amp; Địa chỉ</h4>
                        <ul class="space-y-2 text-sm text-[#64748B]">
                            <li>Hotline: 0912 345 678</li>
                            <li>Email: hello@matngotbear.com</li>
                            <li>Địa chỉ: Hà Nội, Việt Nam</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-amber-100">
                    <p class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-center text-sm text-[#64748B]">
                        © {{ date('Y') }} Mật Ngọt Bear — Mật ngọt cho mọi khoảnh khắc yêu thương
                    </p>
                </div>
            </footer>
        </div>
    </body>
</html>