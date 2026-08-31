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
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-bear-body bg-cream-bg selection:bg-honey selection:text-white"
    x-data="toastManager({
        success: {{ json_encode(session('success')) }},
        error: {{ json_encode(session('error')) }},
        info: {{ json_encode(session('info')) }}
    })">
    <div class="min-h-screen bg-cream-bg flex flex-col">
        @auth
            @include('layouts.navigation')
        @endauth

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white/70 backdrop-blur-md border-b border-amber-100 shadow-sm">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        <!-- Footer (Hidden on Checkout, Cart, Payment, and Orders) -->
        @if (!request()->routeIs('customer.cart*') && !request()->routeIs('customer.checkout*') && !request()->routeIs('customer.payment.*') && !request()->routeIs('customer.orders.*') && !isset($hideFooter))
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
        @endif
    </div>

    <!-- Real-time Toast Notifications at Top-Right Corner -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-3 w-full max-w-sm pointer-events-none px-4 sm:px-0">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="pointer-events-auto w-full bg-[#FAF6F0] rounded-2xl shadow-xl shadow-[#5C3219]/10 border-2 overflow-hidden p-4 flex items-start gap-3.5 transition-all backdrop-blur-md"
                :class="{
                    'border-emerald-300/80 bg-gradient-to-r from-emerald-50/90 via-[#FAF6F0] to-[#FAF6F0]': toast
                        .type === 'success',
                    'border-rose-300/80 bg-gradient-to-r from-rose-50/90 via-[#FAF6F0] to-[#FAF6F0]': toast
                        .type === 'error',
                    'border-amber-300/80 bg-gradient-to-r from-amber-50/90 via-[#FAF6F0] to-[#FAF6F0]': toast
                        .type === 'warning',
                    'border-sky-300/80 bg-gradient-to-r from-sky-50/90 via-[#FAF6F0] to-[#FAF6F0]': toast
                        .type === 'info'
                }">

                <!-- Icon SVG bo cong mềm mại -->
                <div class="shrink-0 mt-0.5">
                    <template x-if="toast.type === 'success'">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white text-sm shadow-md shadow-emerald-500/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </span>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-rose-400 to-rose-600 text-white text-sm shadow-md shadow-rose-500/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </span>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-[#F4B860] to-[#E09028] text-white text-sm shadow-md shadow-[#E09028]/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </span>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-sky-400 to-sky-600 text-white text-sm shadow-md shadow-sky-500/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </span>
                    </template>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold uppercase tracking-wider"
                        :class="{
                            'text-emerald-800': toast.type === 'success',
                            'text-rose-800': toast.type === 'error',
                            'text-[#5C3219]': toast.type === 'warning',
                            'text-sky-800': toast.type === 'info'
                        }"
                        x-text="toast.title || (toast.type === 'success' ? 'Thành công' : (toast.type === 'error' ? 'Lỗi' : 'Thông báo'))">
                    </div>
                    <div class="text-sm font-semibold text-[#2E190E] mt-0.5 break-words" x-text="toast.message"></div>
                </div>

                <!-- Close button -->
                <button @click="removeToast(toast.id)"
                    class="text-[#8E8076] hover:text-[#2E190E] hover:bg-[#EBDDCD]/50 rounded-lg transition p-1 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Scripts stack -->
    @stack('scripts')

    <script>
        window.isCustomerAuthenticated = {{ auth()->check() ? 'true' : 'false' }};

        function openAuthModal(targetUrl = null, customTitle = null, customDesc = null) {
            const modal = document.getElementById('mn-auth-modal');
            if (!modal) return;
            
            const loginBtn = document.getElementById('mn-auth-login-btn');
            const registerBtn = document.getElementById('mn-auth-register-btn');
            const titleEl = document.getElementById('mn-auth-modal-title');
            const descEl = document.getElementById('mn-auth-modal-desc');
            const baseLogin = "{{ route('login') }}";
            const baseRegister = "{{ route('register') }}";
            
            if (targetUrl) {
                loginBtn.href = `${baseLogin}?redirect=${encodeURIComponent(targetUrl)}`;
                registerBtn.href = `${baseRegister}?redirect=${encodeURIComponent(targetUrl)}`;
            } else {
                loginBtn.href = baseLogin;
                registerBtn.href = baseRegister;
            }

            if (titleEl) {
                titleEl.textContent = customTitle || 'Đăng nhập tài khoản';
            }
            if (descEl) {
                descEl.textContent = customDesc || 'Đăng nhập hoặc tạo tài khoản Mật Ngọt Bear để thêm vào giỏ hàng, mua hàng và tích lũy ưu đãi!';
            }
            
            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                const card = modal.querySelector('.mn-auth-card');
                if (card) {
                    card.classList.remove('scale-95');
                    card.classList.add('scale-100');
                }
            });
        }

        function closeAuthModal() {
            const modal = document.getElementById('mn-auth-modal');
            if (!modal) return;
            modal.classList.add('opacity-0', 'pointer-events-none');
            const card = modal.querySelector('.mn-auth-card');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
        }
    </script>

    {{-- Global Auth Required Modal for Checkout / Buy Now --}}
    <div id="mn-auth-modal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity duration-300 opacity-0 pointer-events-none" style="display: none;" onclick="if(event.target === this) closeAuthModal();">
        <div class="mn-auth-card relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-[#F2DECA] p-6 sm:p-8 text-center transform scale-95 transition-transform duration-300">
            {{-- Close button --}}
            <button type="button" onclick="closeAuthModal()" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-[#FFF9F2] hover:bg-[#F2DECA] text-[#7D6B5D] flex items-center justify-center text-lg transition cursor-pointer">
                ✕
            </button>

            {{-- Cute Icon / Badge --}}
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-[#FFF0DC] to-[#FFE3BA] flex items-center justify-center text-3xl shadow-md shadow-[#E08A1E]/15 mb-4">
                🧸
            </div>

            <h3 id="mn-auth-modal-title" class="text-xl sm:text-2xl font-black text-[#2B1810] mb-2 tracking-tight">
                Đăng nhập tài khoản
            </h3>
            <p id="mn-auth-modal-desc" class="text-xs sm:text-sm text-[#7D6B5D] leading-relaxed mb-5 font-medium">
                Đăng nhập hoặc tạo tài khoản <strong class="text-[#2B1810]">Mật Ngọt Bear</strong> để tiếp tục đặt hàng, nhận voucher ưu đãi và tích lũy điểm thưởng!
            </p>

            {{-- Value Props Checklist --}}
            <div class="bg-[#FFFDF9] border border-[#F2DECA] rounded-2xl p-3.5 mb-6 text-left space-y-2 text-xs font-semibold text-[#5D4037]">
                <div class="flex items-center gap-2">
                    <span class="text-base">🎟️</span>
                    <span>Áp dụng mã giảm giá voucher & miễn phí vận chuyển</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-base">📍</span>
                    <span>Lưu địa chỉ giao hàng tiện lợi cho các lần mua sau</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-base">📦</span>
                    <span>Theo dõi trạng thái và tiến độ giao hàng chi tiết</span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="space-y-3">
                <a id="mn-auth-login-btn" href="{{ route('login') }}" class="w-full bg-gradient-to-r from-[#E08A1E] to-[#D68729] hover:from-[#D17E17] hover:to-[#C2751D] text-white font-extrabold py-3.5 px-6 rounded-2xl shadow-lg shadow-[#E08A1E]/30 flex items-center justify-center gap-2 text-sm transition transform hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Đăng nhập tài khoản</span>
                </a>
                
                <a id="mn-auth-register-btn" href="{{ route('register') }}" class="w-full bg-[#FFF9F2] hover:bg-[#FFF0DC] text-[#8C4A19] font-extrabold py-3.5 px-6 rounded-2xl border border-[#F2DECA] flex items-center justify-center gap-2 text-sm transition transform hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Đăng ký tài khoản mới</span>
                </a>
            </div>

            <div class="mt-5 text-center">
                <button type="button" onclick="closeAuthModal()" class="text-xs font-bold text-[#A8988A] hover:text-[#7D6B5D] transition cursor-pointer">
                    Tiếp tục xem sản phẩm
                </button>
            </div>
        </div>
    </div>
</body>

</html>
