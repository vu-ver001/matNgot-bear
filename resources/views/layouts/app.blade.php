<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-bear-body bg-cream-bg selection:bg-honey selection:text-white"
          x-data="toastManager({
              success: {{ json_encode(session('success')) }},
              error: {{ json_encode(session('error')) }},
              info: {{ json_encode(session('info')) }}
          })">
        <div class="min-h-screen bg-cream-bg">
            @auth
                @include('layouts.navigation')
            @endauth

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

        <!-- Real-time Toast Notifications at Top-Right Corner -->
        <div class="fixed top-5 right-5 z-50 flex flex-col gap-3 w-full max-w-sm pointer-events-none px-4 sm:px-0">
            <template x-for="toast in toasts" :key="toast.id">
                <div x-show="toast.visible"
                     x-transition:enter="transform ease-out duration-300 transition"
                     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
                     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="pointer-events-auto w-full bg-white rounded-2xl shadow-xl border overflow-hidden p-4 flex items-start gap-3.5 transition-all"
                     :class="{
                         'border-emerald-200 bg-emerald-50/40': toast.type === 'success',
                         'border-rose-200 bg-rose-50/40': toast.type === 'error',
                         'border-amber-200 bg-amber-50/40': toast.type === 'warning',
                         'border-sky-200 bg-sky-50/40': toast.type === 'info'
                     }">
                    
                    <!-- Icon -->
                    <div class="shrink-0 mt-0.5">
                        <template x-if="toast.type === 'success'">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500 text-white text-sm shadow-sm">✓</span>
                        </template>
                        <template x-if="toast.type === 'error'">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-500 text-white text-sm shadow-sm">✕</span>
                        </template>
                        <template x-if="toast.type === 'warning'">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500 text-white text-sm shadow-sm">⚠️</span>
                        </template>
                        <template x-if="toast.type === 'info'">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500 text-white text-sm shadow-sm">ℹ️</span>
                        </template>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold uppercase tracking-wider"
                             :class="{
                                 'text-emerald-800': toast.type === 'success',
                                 'text-rose-800': toast.type === 'error',
                                 'text-amber-800': toast.type === 'warning',
                                 'text-sky-800': toast.type === 'info'
                             }"
                             x-text="toast.title || (toast.type === 'success' ? 'Thành công' : (toast.type === 'error' ? 'Lỗi' : 'Thông báo'))">
                        </div>
                        <div class="text-sm font-medium text-gray-700 mt-0.5 break-words" x-text="toast.message"></div>
                    </div>

                    <!-- Close button -->
                    <button @click="removeToast(toast.id)" class="text-gray-400 hover:text-gray-600 transition p-1 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </template>
        </div>
    </body>
</html>