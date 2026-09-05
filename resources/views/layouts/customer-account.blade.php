@php
    $user = auth()->user();
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($user->full_name), 0, 1));

    $menuGroups = [
        [
            'label' => 'Mua sắm',
            'items' => [
                ['label' => 'Đơn hàng của tôi', 'route' => 'customer.orders.index', 'active' => ['customer.orders.*'], 'icon' => 'package'],
                ['label' => 'Danh sách yêu thích', 'route' => 'customer.wishlist.index', 'active' => ['customer.wishlist.*'], 'icon' => 'heart'],
                ['label' => 'Đánh giá của tôi', 'route' => 'customer.reviews.index', 'active' => ['customer.reviews.*'], 'icon' => 'star'],
            ],
        ],
        [
            'label' => 'Hỗ trợ',
            'items' => [
                ['label' => 'Tin nhắn / Hỗ trợ', 'route' => null, 'active' => [], 'icon' => 'message'],
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

        <title>{{ $title }} - {{ config('app.name', 'Mật Ngọt Bear') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
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
                                        href="{{ route($item['route']) }}"
                                        @class(['customer-account-nav-item', 'is-active' => $isActive])
                                        @if ($isActive) aria-current="page" @endif
                                        title="{{ $item['label'] }}"
                                    >
                                        @include('customer.partials.account-icon', ['name' => $item['icon']])
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @else
                                    <span class="customer-account-nav-item is-disabled" title="Chức năng chưa kết nối">
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
                        @click="sidebarCollapsed ? (setSidebarCollapsed(false), accountMenuOpen = true) : accountMenuOpen = ! accountMenuOpen"
                        :aria-expanded="accountMenuOpen.toString()"
                        aria-controls="customer-account-user-menu"
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
                </button>

                <main @class(['customer-account-page', 'is-flush-page' => $flush])>
                    <div @class(['customer-account-content', 'is-flush' => $flush])>
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @include('ReviewKT.partials.review-modal')
    </body>
</html>
