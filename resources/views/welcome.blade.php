<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @fonts

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */ @layer properties{@supports (((-webkit-hyphens:none)) and (not (margin-trim:inline))) or ((-moz-orient:inline) and (not (color:rgb(from red r g b)))){*,:before,:after,::backdrop{--tw-translate-x:0;--tw-translate-y:0;--tw-translate-z:0;--tw-rotate-x:initial;--tw-rotate-y:initial;--tw-rotate-z:initial;--tw-skew-x:initial;--tw-skew-y:initial;--tw-space-x-reverse:0;--tw-border-style:solid;--tw-leading:initial;--tw-font-weight:initial;--tw-tracking:initial;--tw-shadow:0 0 #0000;--tw-shadow-color:initial;--tw-shadow-alpha:100%;--tw-inset-shadow:0 0 #0000;--tw-inset-shadow-color:initial;--tw-inset-shadow-alpha:100%;--tw-ring-color:initial;--tw-ring-shadow:0 0 #0000;--tw-inset-ring-color:initial;--tw-inset-ring-shadow:0 0 #0000;--tw-ring-inset:initial;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-offset-shadow:0 0 #0000;--tw-blur:initial;--tw-brightness:initial;--tw-contrast:initial;--tw-grayscale:initial;--tw-hue-rotate:initial;--tw-invert:initial;--tw-opacity:initial;--tw-saturate:initial;--tw-sepia:initial;--tw-drop-shadow:initial;--tw-drop-shadow-color:initial;--tw-drop-shadow-alpha:100%;--tw-duration:initial;--tw-ease:initial;--tw-content:""}}}@layer theme{:root,:host{--font-sans:"Instrument Sans", ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";--font-serif:ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;--font-mono:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;--color-red-50:oklch(97.1% .013 17.38);--color-red-100:oklch(93.6% .032 17.717);--color-red-200:oklch(88.5% .062 18.334);--color-red-300:oklch(80.8% .114 19.571);--color-red-400:oklch(70.4% .191 22.216);--color-red-500:oklch(63.7% .237 25.331);--color-red-600:oklch(57.7% .245 27.325);--color-red-700:oklch(50.5% .213 27.518);--color-red-800:oklch(44.4% .... (line truncated to 2000 chars)
            </style>
        @endif
    </head>
    <body class="bg-[#FFFDF9] text-[#1E293B] min-h-screen flex flex-col">
        <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-amber-100">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-9 w-auto">
                        <circle cx="17" cy="19" r="10" fill="#8B5A2B"/>
                        <circle cx="47" cy="19" r="10" fill="#8B5A2B"/>
                        <circle cx="17" cy="19" r="4.5" fill="#F59E0B"/>
                        <circle cx="47" cy="19" r="4.5" fill="#F59E0B"/>
                        <path d="M32 8C20.5 8 12 16.8 12 28.5 12 41.5 20.8 52 32 52s20-10.5 20-23.5C52 16.8 43.5 8 32 8Z" fill="#8B5A2B"/>
                        <ellipse cx="32" cy="34.5" rx="12.5" ry="9.5" fill="#FDE68A"/>
                        <circle cx="24.5" cy="30" r="2.4" fill="#1E293B"/>
                        <circle cx="39.5" cy="30" r="2.4" fill="#1E293B"/>
                        <ellipse cx="32" cy="38" rx="3.2" ry="2.4" fill="#1E293B"/>
                        <path d="M26 27.5c1.8-1.6 4-2 6.5-1.5 2-0.6 4-0.4 5.5 1-1 1.4-3.4 1.8-5.5 1.2-1.9.6-4.5.3-6.5-0.7Z" fill="#1E293B"/>
                    </svg>
                    <span class="text-base font-bold text-[#8B5A2B]">Mật Ngọt Bear</span>
                </a>

                <div class="flex items-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}"
                               class="inline-flex items-center px-5 py-2 text-sm font-semibold text-white bg-amber-500 rounded-full hover:bg-[#8B5A2B] transition">
                                Vào cửa hàng
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-[#8B5A2B] border border-amber-200 rounded-full hover:bg-amber-50 transition">
                                Đăng nhập
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="inline-flex items-center px-5 py-2 text-sm font-semibold text-white bg-amber-500 rounded-full hover:bg-[#8B5A2B] transition">
                                    Đăng ký
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </nav>
        </header>

        <main class="flex-1">
            <!-- Hero -->
            <section class="relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-amber-100/60 via-[#FFFDF9] to-[#FFFDF9]"></div>
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white border border-amber-200 rounded-full text-sm font-medium text-[#8B5A2B] mb-6 shadow-sm">
                        🐻 Tiệm gấu bông thủ công
                    </div>
                    <h1 class="text-4xl lg:text-6xl font-bold text-[#1E293B] leading-tight">
                        Mật ngọt như tình yêu,
                        <span class="text-amber-500">ấm áp như vòng tay gấu bông</span>
                    </h1>
                    <p class="mt-6 max-w-2xl mx-auto text-lg text-[#64748B] leading-relaxed">
                        Mật Ngọt Bear chuyên gấu bông cao cấp, bó hoa gấu handmade và những món quà đáng yêu
                        cho người thân yêu — giao hàng toàn quốc, phí ship chỉ 30.000đ.
                    </p>
                    <div class="mt-10 flex flex-wrap justify-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}"
                               class="inline-flex items-center px-8 py-3 text-base font-semibold text-white bg-amber-500 rounded-full hover:bg-[#8B5A2B] shadow-lg shadow-amber-200 transition">
                                Khám phá ngay
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center px-8 py-3 text-base font-semibold text-white bg-amber-500 rounded-full hover:bg-[#8B5A2B] shadow-lg shadow-amber-200 transition">
                                Bắt đầu mua sắm
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="inline-flex items-center px-8 py-3 text-base font-medium text-[#8B5A2B] bg-white border border-amber-200 rounded-full hover:bg-amber-50 transition">
                                    Tạo tài khoản
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="product-card p-6">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-2xl mb-4">🧸</div>
                        <h3 class="text-base font-semibold text-[#1E293B] mb-2">Gấu bông chất lượng cao</h3>
                        <p class="text-sm text-[#64748B] leading-relaxed">
                            Lông nhung mềm mại, đường may tỉ mỉ, an toàn cho mọi lứa tuổi.
                        </p>
                    </div>
                    <div class="product-card p-6">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-2xl mb-4">🚚</div>
                        <h3 class="text-base font-semibold text-[#1E293B] mb-2">Giao hàng toàn quốc</h3>
                        <p class="text-sm text-[#64748B] leading-relaxed">
                            Phí vận chuyển cố định 30.000đ, theo dõi đơn hàng từng bước.
                        </p>
                    </div>
                    <div class="product-card p-6">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-2xl mb-4">💳</div>
                        <h3 class="text-base font-semibold text-[#1E293B] mb-2">Thanh toán linh hoạt</h3>
                        <p class="text-sm text-[#64748B] leading-relaxed">
                            COD, chuyển khoản QR, ví điện tử — mua sắm thật dễ dàng.
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-white border-t border-amber-100">
            <p class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-center text-sm text-[#64748B]">
                © {{ date('Y') }} Mật Ngọt Bear — Mật ngọt cho mọi khoảnh khắc yêu thương
            </p>
        </footer>
    </body>
</html>