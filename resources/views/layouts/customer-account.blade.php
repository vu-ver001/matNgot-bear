@php
    $user = auth()->user();
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($user?->full_name ?? ''), 0, 1));
    $unreadMessagesCount = $user ? app(\App\Services\ChatKT\ChatService::class)->countUnreadMessagesForCustomer($user) : 0;

    $menuGroups = [
        [
            'label' => 'Mua sắm',
            'items' => [
                ['label' => 'Đơn hàng của tôi', 'route' => 'customer.orders.index', 'active' => ['customer.orders.*'], 'icon' => 'package'],
                ['label' => 'Danh sách yêu thích', 'route' => 'customer.wishlist.index', 'params' => ['view' => 'account'], 'active' => ['customer.wishlist.*'], 'icon' => 'heart'],
                ['label' => 'Đánh giá của tôi', 'route' => 'customer.reviews.index', 'active' => ['customer.reviews.*'], 'icon' => 'star'],
            ],
        ],
        [
            'label' => 'Hỗ trợ',
            'items' => [
                [
                    'label' => 'Tin nhắn / Hỗ trợ',
                    'route' => 'customer.messages.index',
                    'active' => ['customer.messages.*', 'account.messages*'],
                    'icon' => 'message',
                    'badge' => $unreadMessagesCount,
                ],
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Tài khoản' }} - {{ config('app.name', 'Mật Ngọt Bear') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Caveat:wght@600;700&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        @if (request()->routeIs('customer.orders.*'))
            <link rel="stylesheet" href="{{ asset('css/order-components.css') }}">
        @endif
    </head>
    <body class="font-sans {{ request()->routeIs('customer.messages.*') ? '' : 'antialiased' }}">
        <div
            class="customer-account-app"
            x-data="{
                sidebarOpen: false,
                sidebarCollapsed: false,
                accountMenuOpen: false,

                init() {
                    try {
                        this.sidebarCollapsed = localStorage.getItem('customer-account-sidebar-collapsed') === '1';
                    } catch (error) {
                        this.sidebarCollapsed = false;
                    }
                },

                setSidebarCollapsed(collapsed) {
                    this.sidebarCollapsed = collapsed;

                    try {
                        localStorage.setItem('customer-account-sidebar-collapsed', collapsed ? '1' : '0');
                    } catch (error) {
                        // Menu vẫn hoạt động nếu trình duyệt không cho phép lưu trạng thái.
                    }
                },

                toggleSidebar() {
                    this.setSidebarCollapsed(! this.sidebarCollapsed);
                },
            }"
            :class="{ 'is-sidebar-collapsed': sidebarCollapsed }"
            @keydown.escape.window="sidebarOpen = false; accountMenuOpen = false"
        >
            <script>
                try {
                    if (localStorage.getItem('customer-account-sidebar-collapsed') === '1') {
                        document.currentScript.parentElement.classList.add('is-sidebar-collapsed');
                    }
                } catch (error) {
                    // Giữ giao diện mặc định nếu trình duyệt không cho phép đọc trạng thái.
                }
            </script>

            <aside
                id="customer-account-sidebar"
                class="customer-account-sidebar"
                :class="{ 'is-open': sidebarOpen }"
            >
                <div class="customer-account-brand-row">
                    <div class="customer-account-brand-copy">
                        <a href="{{ route('home') }}" class="customer-account-brand-link" aria-label="Về trang chủ Mật Ngọt Bear">
                            <strong>Mật Ngọt Bear</strong>
                        </a>
                        <small>Khu vực khách hàng</small>
                    </div>

                    <button
                        type="button"
                        class="customer-account-collapse-button"
                        :class="{ 'is-collapsed': sidebarCollapsed }"
                        @click="window.innerWidth < 1024 ? sidebarOpen = false : toggleSidebar()"
                        aria-label="Thu gọn hoặc mở rộng menu tài khoản"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m14 7-5 5 5 5" />
                        </svg>
                    </button>
                </div>

                <nav class="customer-account-nav" aria-label="Điều hướng tài khoản">
                    @foreach ($menuGroups as $group)
                        <section class="customer-account-nav-group">
                            <p class="customer-account-nav-label">{{ $group['label'] }}</p>

                            @foreach ($group['items'] as $item)
                                @if ($item['route'])
                                    @php($isActive = request()->routeIs(...$item['active']))
                                    <a
                                        href="{{ route($item['route'], $item['params'] ?? []) }}"
                                        @class(['customer-account-nav-item', 'is-active' => $isActive])
                                        @if ($isActive) aria-current="page" @endif
                                        title="{{ $item['label'] }}"
                                        data-title="{{ $item['label'] }}"
                                    >
                                        @include('customer.partials.account-icon', ['name' => $item['icon']])
                                        <span>{{ $item['label'] }}</span>
                                        @if (!empty($item['badge']) && $item['badge'] > 0)
                                            <span class="customer-account-badge" title="{{ $item['badge'] }} tin nhắn chưa đọc">
                                                {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                            </span>
                                        @endif
                                    </a>
                                @else
                                    <span class="customer-account-nav-item is-disabled" title="Chức năng chưa kết nối" data-title="{{ $item['label'] }}">
                                        @include('customer.partials.account-icon', ['name' => $item['icon']])
                                        <span>{{ $item['label'] }}</span>
                                        <small>Chưa kết nối</small>
                                    </span>
                                @endif
                            @endforeach
                        </section>
                    @endforeach
                </nav>

                <div class="customer-account-sidebar-footer" @click.outside="accountMenuOpen = false">
                    <button
                        type="button"
                        class="customer-account-user"
                        @click="accountMenuOpen = ! accountMenuOpen"
                        :aria-expanded="accountMenuOpen.toString()"
                        aria-controls="customer-account-user-menu"
                        :title="sidebarCollapsed ? '{{ $user->full_name }}' : 'Tài khoản cá nhân'"
                    >
                        <div class="customer-account-avatar" aria-hidden="true">
                            @if ($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="">
                            @else
                                {{ $initial }}
                            @endif
                        </div>
                        <div class="customer-account-user-copy">
                            <strong>{{ $user->full_name }}</strong>
                            <span data-account-current-email>{{ $user->email }}</span>
                        </div>
                    </button>

                    <div
                        id="customer-account-user-menu"
                        class="customer-account-user-menu"
                        x-show="accountMenuOpen"
                        x-transition
                        x-cloak
                    >
                        <div class="customer-account-menu-profile">
                            <div class="customer-account-menu-avatar" aria-hidden="true">
                                @if ($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" alt="">
                                @else
                                    {{ $initial }}
                                @endif
                            </div>
                            <div class="customer-account-menu-profile-copy">
                                <strong>{{ $user->full_name }}</strong>
                                <span data-account-current-email>{{ $user->email }}</span>
                            </div>
                        </div>

                        <div class="customer-account-menu-divider"></div>

                        <a
                            href="{{ route('profile.edit') }}"
                            @class(['customer-account-nav-item', 'is-active' => request()->routeIs('profile.*')])
                            title="Hồ sơ"
                        >
                            @include('customer.partials.account-icon', ['name' => 'user'])
                            <span>Hồ sơ</span>
                        </a>

                        <a
                            href="{{ route('account.password.edit') }}"
                            @class(['customer-account-nav-item', 'is-active' => request()->routeIs('account.password.*')])
                            @if (request()->routeIs('account.password.*')) aria-current="page" @endif
                            title="Đổi mật khẩu"
                        >
                            @include('customer.partials.account-icon', ['name' => 'lock'])
                            <span>Đổi mật khẩu</span>
                        </a>

                        <div class="customer-account-menu-divider"></div>

                        <form method="POST" action="{{ route('logout') }}" class="customer-account-user-logout">
                            @csrf
                            <button type="submit" class="customer-account-nav-item" title="Đăng xuất">
                                @include('customer.partials.account-icon', ['name' => 'logout'])
                                <span>Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <button
                type="button"
                class="customer-account-backdrop"
                x-show="sidebarOpen"
                x-cloak
                @click="sidebarOpen = false"
                aria-label="Đóng menu tài khoản"
            ></button>

            <div class="customer-account-workspace">
                <button
                    type="button"
                    class="customer-account-mobile-toggle"
                    @click="sidebarOpen = true"
                    aria-controls="customer-account-sidebar"
                    aria-label="Mở menu tài khoản"
                >
                    @include('customer.partials.account-icon', ['name' => 'menu'])
                    @if ($unreadMessagesCount > 0)
                        <span class="customer-mobile-badge-dot" aria-hidden="true"></span>
                    @endif
                </button>

                <main @class(['customer-account-page', 'is-flush-page' => ($flush ?? false), 'orders-page' => request()->routeIs('customer.orders.*')])>
                    <div @class(['customer-account-content', 'is-flush' => ($flush ?? false)])>
                        {!! $slot ?? $__env->yieldContent('content') !!}
                    </div>
                </main>
            </div>
        </div>

        {{-- Toast góc phải (đồng bộ admin/staff, thay banner flash; x-data riêng để khỏi đè sidebar) --}}
        <div
            x-data="toastManager({
                success: {{ json_encode(session('success')) }},
                error: {{ json_encode(session('error')) }},
                info: {{ json_encode(session('info')) }}
            })"
        >
            <div class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 w-full max-w-sm pointer-events-none px-4 sm:px-0">
                <template x-for="toast in toasts" :key="toast.id">
                    <div x-show="toast.visible" x-transition:enter="transform ease-out duration-300 transition"
                        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
                        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="pointer-events-auto w-full bg-[#FAF6F0] rounded-2xl shadow-2xl shadow-[#5C3219]/15 border-2 overflow-hidden p-4 flex items-start gap-3.5 transition-all backdrop-blur-md"
                        :class="{
                            'border-emerald-400 bg-gradient-to-r from-emerald-50/95 via-[#FAF6F0] to-[#FAF6F0]': toast.type === 'success',
                            'border-rose-400 bg-gradient-to-r from-rose-50/95 via-[#FAF6F0] to-[#FAF6F0]': toast.type === 'error',
                            'border-amber-400 bg-gradient-to-r from-amber-50/95 via-[#FAF6F0] to-[#FAF6F0]': toast.type === 'warning',
                            'border-sky-400 bg-gradient-to-r from-sky-50/95 via-[#FAF6F0] to-[#FAF6F0]': toast.type === 'info'
                        }">
                        <div class="shrink-0 mt-0.5">
                            <template x-if="toast.type === 'success'">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white text-sm shadow-md shadow-emerald-500/25">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </span>
                            </template>
                            <template x-if="toast.type === 'error'">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-rose-600 text-white text-sm shadow-md shadow-rose-500/25">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </span>
                            </template>
                            <template x-if="toast.type === 'warning'">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-[#F4B860] to-[#E09028] text-white text-sm shadow-md shadow-[#E09028]/25">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </span>
                            </template>
                            <template x-if="toast.type === 'info'">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-sky-400 to-sky-600 text-white text-sm shadow-md shadow-sky-500/25">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </span>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0 pr-2">
                            <h5 class="text-sm font-bold text-[#2C1408] tracking-tight" x-text="toast.title || (toast.type === 'success' ? 'Thành công' : 'Thông báo')"></h5>
                            <p class="text-xs text-[#6B5E55] mt-0.5 leading-relaxed font-medium break-words" x-text="toast.message"></p>
                        </div>
                        <button type="button" @click="removeToast(toast.id)" class="shrink-0 text-[#8E8076] hover:text-[#2C1408] transition p-1 rounded-lg hover:bg-black/5 cursor-pointer" aria-label="Đóng">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        @include('ReviewKT.partials.review-modal')

        {{-- Tự động cập nhật dữ liệu mới nhất khi bấm nút Back (Quay lại) trên trình duyệt Chrome/Safari --}}
        <script>
            window.addEventListener('pageshow', function (event) {
                var isBack = event.persisted;
                if (!isBack && window.performance && window.performance.navigation) {
                    isBack = window.performance.navigation.type === 2;
                }
                if (!isBack && window.performance && window.performance.getEntriesByType) {
                    var entries = window.performance.getEntriesByType('navigation');
                    if (entries.length > 0 && entries[0].type === 'back_forward') {
                        isBack = true;
                    }
                }
                if (isBack) {
                    window.location.reload();
                }
            });
        </script>
    </body>
</html>
