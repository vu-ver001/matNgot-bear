<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'Quản Trị Hệ Thống') - Mật Ngọt Bear</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Cấu hình SweetAlert2 triệt tiêu hoàn toàn hiện tượng padding scrollbar và nhảy ngang layout
        if (typeof Swal !== 'undefined') {
            const _origSwalFire = Swal.fire;
            Swal.fire = function(...args) {
                let options = {};
                if (args.length === 1 && typeof args[0] === 'object' && args[0] !== null) {
                    options = Object.assign({ heightAuto: false, scrollbarPadding: false }, args[0]);
                } else if (args.length >= 2) {
                    options = {
                        title: args[0],
                        text: args[1],
                        icon: args[2] || undefined,
                        heightAuto: false,
                        scrollbarPadding: false
                    };
                } else {
                    options = { heightAuto: false, scrollbarPadding: false };
                }

                const promise = _origSwalFire.call(this, options);
                if (promise && typeof promise.finally === 'function') {
                    promise.finally(() => {
                        window.scrollTo({ left: 0 });
                        if (document.documentElement) document.documentElement.scrollLeft = 0;
                        if (document.body) document.body.scrollLeft = 0;
                    });
                }
                return promise;
            };
        }
    </script>

    <!-- Script áp dụng trạng thái Mini Sidebar ngay lập tức trước khi render HTML để triệt tiêu độ khựng -->
    <script>
        (function() {
            if (localStorage.getItem('mn_admin_sidebar_collapsed') === '1') {
                document.documentElement.classList.add('admin-sidebar-collapsed');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Admin Dashboard Layout CSS (Tách riêng bởi Khánh Vân) -->
    <link rel="stylesheet" href="{{ asset('css/admin-layout.css') }}?v={{ file_exists(public_path('css/admin-layout.css')) ? filemtime(public_path('css/admin-layout.css')) : time() }}">
    @yield('styles')
<body class="font-sans antialiased text-[#2C1408] bg-[#F7F4EE] selection:bg-[#E08A1E] selection:text-white"
    x-data="toastManager({
        success: {{ json_encode(session('success')) }},
        error: {{ json_encode(session('error')) }},
        info: {{ json_encode(session('info')) }}
    })">

    @php
        $adminUser = auth()->user();
        $adminInitial = mb_strtoupper(mb_substr($adminUser->full_name ?? $adminUser->name ?? 'A', 0, 1, 'UTF-8'));
        $adminName = $adminUser->full_name ?? $adminUser->name ?? 'Quản Trị Viên';
        $adminEmail = $adminUser->email ?? 'admin@matngotbear.com';
    @endphp

    <!-- ====== SIDEBAR (GIAO DIỆN PASTEL THEO ẢNH 1 & ẢNH 2) ====== -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-content">
                <a href="{{ route('home') }}" class="sidebar-brand-name" title="Về trang chủ Mật Ngọt Bear">Mật Ngọt Bear</a>
                <span class="sidebar-brand-sub">KHU VỰC QUẢN LÝ</span>
            </div>
            <button type="button" class="sidebar-collapse-btn" onclick="toggleSidebar()" title="Thu gọn menu" id="sidebarCollapseBtn">
                <i class="fa-solid fa-chevron-left" id="sidebarToggleIcon"></i>
            </button>
            <button type="button" class="sidebar-mobile-close-btn" onclick="closeMobileSidebar()" title="Đóng menu" id="sidebarMobileCloseBtn">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Tổng Quan</div>
            <!-- Mục của bạn nhóm (Dashboard & Thống kê) -->
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard*') || ($currentPage ?? '') === 'dashboard' ? 'active' : '' }}" data-title="Dashboard & Thống kê">
                <i class="fa-solid fa-chart-pie"></i>
                <span class="sidebar-link-text">Dashboard &amp; Thống kê</span>
            </a>

            <div class="sidebar-section-label">Sản Phẩm &amp; Danh Mục</div>
            <!-- Phần của Khánh Vân -->
            <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products*') || ($currentPage ?? '') === 'products' ? 'active' : '' }}" data-title="Quản lý Sản phẩm">
                <i class="fa-solid fa-box-open"></i>
                <span class="sidebar-link-text">Quản lý Sản phẩm</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories*') || ($currentPage ?? '') === 'categories' ? 'active' : '' }}" data-title="Quản lý Danh mục">
                <i class="fa-solid fa-folder-tree"></i>
                <span class="sidebar-link-text">Quản lý Danh mục</span>
            </a>

            <div class="sidebar-section-label">Bán Hàng &amp; Tài Chính</div>
            <a href="{{ route('admin.vouchers.index') }}" class="sidebar-link {{ request()->routeIs('admin.vouchers*') || ($currentPage ?? '') === 'vouchers' ? 'active' : '' }}" data-title="Quản lý Voucher">
                <i class="fa-solid fa-ticket"></i>
                <span class="sidebar-link-text">Quản lý Voucher</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders*') || ($currentPage ?? '') === 'orders' ? 'active' : '' }}" data-title="Quản lý Đơn hàng">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="sidebar-link-text">Quản lý Đơn hàng</span>
            </a>
            <a href="{{ route('admin.payments.index') }}" class="sidebar-link {{ request()->routeIs('admin.payments*') || ($currentPage ?? '') === 'payments' ? 'active' : '' }}" data-title="Quản lý Thanh toán">
                <i class="fa-solid fa-credit-card"></i>
                <span class="sidebar-link-text">Quản lý Thanh toán</span>
            </a>

            <div class="sidebar-section-label">Người Dùng</div>
            <!-- Các mục của bạn nhóm -->
            <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users*') || request()->routeIs('admin.customers*') || ($currentPage ?? '') === 'customers' || ($currentPage ?? '') === 'users' ? 'active' : '' }}" data-title="Quản lý Người Dùng">
                <i class="fa-solid fa-users"></i>
                <span class="sidebar-link-text">Quản lý Người Dùng</span>
            </a>

            <div class="sidebar-section-label">Hỗ Trợ &amp; Báo Cáo</div>
            <!-- Các mục của bạn nhóm -->
            <a href="{{ route('admin.reviews.index') }}" class="sidebar-link {{ request()->routeIs('admin.reviews*') || ($currentPage ?? '') === 'reviews' ? 'active' : '' }}" data-title="Quản lý Review">
                <i class="fa-solid fa-star-half-stroke"></i>
                <span class="sidebar-link-text">Quản lý Review</span>
            </a>
            <a href="{{ route('admin.reports.revenue') }}" class="sidebar-link {{ request()->routeIs('admin.reports*') || ($currentPage ?? '') === 'reports' ? 'active' : '' }}" data-title="Báo cáo Doanh thu">
                <i class="fa-solid fa-chart-line"></i>
                <span class="sidebar-link-text">Báo cáo Doanh thu</span>
            </a>
            <a href="{{ route('admin.support.index') }}" class="sidebar-link {{ request()->routeIs('admin.support*') || ($currentPage ?? '') === 'support' ? 'active' : '' }}" data-title="Hỗ trợ khách hàng">
                <i class="fa-solid fa-comments"></i>
                <span class="sidebar-link-text">Hỗ trợ khách hàng</span>
            </a>
        </nav>

        <!-- Sidebar Footer & Popup Người Dùng (Theo chuẩn Ảnh 1 & 2) -->
        <div class="sidebar-footer-wrap">
            <!-- Popup Card (Ảnh 2) -->
            <div class="sidebar-user-popup" id="sidebarUserPopup">
                <div class="user-popup-header">
                    <div class="user-popup-avatar">
                        {{ $adminInitial }}
                    </div>
                    <div class="user-popup-info">
                        <div class="user-popup-name">{{ $adminName }}</div>
                        <div class="user-popup-email" title="{{ $adminEmail }}">{{ $adminEmail }}</div>
                    </div>
                </div>

                <div class="user-popup-divider"></div>

                <div class="user-popup-menu">
                    <a href="{{ route('profile.edit') }}" class="user-popup-item active">
                        <i class="fa-regular fa-user"></i>
                        <span>Hồ sơ</span>
                    </a>
                    <div class="user-popup-item disabled">
                        <i class="fa-solid fa-lock"></i>
                        <span>Đổi mật khẩu</span>
                        <span class="badge-not-connected">Chưa kết nối</span>
                    </div>
                </div>

                <div class="user-popup-divider"></div>

                <form method="POST" action="{{ route('logout') }}" class="user-popup-logout-form">
                    @csrf
                    <button type="submit" class="user-popup-item logout-btn">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Đăng xuất</span>
                    </button>
                </form>
            </div>

            <!-- Trigger bar (Ảnh 1) -->
            <div class="sidebar-user-trigger" onclick="toggleUserPopup(event)" id="sidebarUserTrigger" title="Tài khoản cá nhân">
                <div class="sidebar-user-avatar">
                    {{ $adminInitial }}
                </div>
                <div class="sidebar-user-details">
                    <div class="sidebar-user-name">{{ $adminName }}</div>
                    <div class="sidebar-user-email" title="{{ $adminEmail }}">{{ $adminEmail }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Script khôi phục trạng thái thu/mở tức thì không giật layout -->
    <script>
        (function() {
            if (localStorage.getItem('mn_admin_sidebar_collapsed') === '1') {
                document.getElementById('adminSidebar')?.classList.add('collapsed');
            }
        })();
    </script>

    <!-- ====== MAIN CONTENT ====== -->
    <div class="admin-main" id="adminMain">
        <!-- Thanh Header Mobile xuất hiện khi thu nhỏ màn hình có nút 3 gạch ở góc trên bên trái -->
        <header class="admin-mobile-topbar" id="adminMobileTopbar">
            <button type="button" class="mobile-menu-btn" onclick="toggleMobileSidebar()" title="Mở thanh menu" id="mobileMenuBtn" aria-label="Mở menu quản trị">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="mobile-topbar-brand">
                <a href="{{ route('admin.dashboard') }}" class="mobile-brand-title">Mật Ngọt Bear</a>
                <span class="mobile-brand-badge">Admin</span>
            </div>
            <div class="mobile-topbar-actions">
                <a href="{{ route('home') }}" class="mobile-btn-store" title="Xem cửa hàng" target="_blank">
                    <i class="fa-solid fa-store"></i>
                </a>
            </div>
        </header>

        <script>
            (function() {
                if (localStorage.getItem('mn_admin_sidebar_collapsed') === '1') {
                    document.getElementById('adminMain')?.classList.add('expanded');
                }
            })();
        </script>
        <div class="admin-content {{ $contentClass ?? '' }}">
            @yield('content')
        </div>
    </div>

    <!-- Backdrop mờ khi mở menu trên mobile/màn hình nhỏ -->
    <div class="mobile-sidebar-backdrop" id="mobileSidebarBackdrop" onclick="closeMobileSidebar()"></div>

    <!-- Script Điều Khiển Đóng / Mở Menu & Popup Card Người Dùng -->
    <script>
        function toggleUserPopup(e) {
            if (e) e.stopPropagation();
            const popup = document.getElementById('sidebarUserPopup');
            popup?.classList.toggle('show');
        }

        // Đóng popup khi click ra ngoài
        document.addEventListener('click', function(e) {
            const popup = document.getElementById('sidebarUserPopup');
            const trigger = document.getElementById('sidebarUserTrigger');
            if (popup && popup.classList.contains('show')) {
                if (!popup.contains(e.target) && !trigger?.contains(e.target)) {
                    popup.classList.remove('show');
                }
            }
        });

        function toggleSidebar() {
            const isCollapsed = document.documentElement.classList.contains('admin-sidebar-collapsed') ||
                                document.getElementById('adminSidebar')?.classList.contains('collapsed');
            if (isCollapsed) {
                expandSidebar();
            } else {
                collapseSidebar();
            }
        }

        function updateCollapseIcon(collapsed) {
            const icon = document.getElementById('sidebarToggleIcon');
            if (icon) {
                icon.className = collapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left';
            }
        }

        function expandSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');
            document.documentElement.classList.remove('admin-sidebar-collapsed');
            sidebar?.classList.remove('collapsed');
            main?.classList.remove('expanded');
            updateCollapseIcon(false);
            localStorage.setItem('mn_admin_sidebar_collapsed', '0');
        }

        function collapseSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');
            document.documentElement.classList.add('admin-sidebar-collapsed');
            sidebar?.classList.add('collapsed');
            main?.classList.add('expanded');
            updateCollapseIcon(true);
            localStorage.setItem('mn_admin_sidebar_collapsed', '1');
        }

        // ====== ĐIỀU KHIỂN MENU KHI THU NHỎ MÀN HÌNH (RESPONSIVE / MOBILE) ======
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            if (sidebar?.classList.contains('mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        }

        function openMobileSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('mobileSidebarBackdrop');
            sidebar?.classList.add('mobile-open');
            backdrop?.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('mobileSidebarBackdrop');
            sidebar?.classList.remove('mobile-open');
            backdrop?.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Tự động đóng menu mobile khi phóng to màn hình trở lại (> 992px)
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                closeMobileSidebar();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('mn_admin_sidebar_collapsed') === '1') {
                updateCollapseIcon(true);
            }

            // Tự động đóng sidebar mobile khi bấm vào link chuyển trang
            const navLinks = document.querySelectorAll('.sidebar-nav .sidebar-link');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        closeMobileSidebar();
                    }
                });
            });
        });
    </script>

    <!-- Real-time Toast Notifications at Top-Right Corner (Không gây nhảy/giật layout) -->
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

                <!-- Icon -->
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

                <!-- Text -->
                <div class="flex-1 min-w-0 pr-2">
                    <h5 class="text-sm font-bold text-[#2C1408] tracking-tight" x-text="toast.title || (toast.type === 'success' ? 'Thành công' : 'Thông báo')"></h5>
                    <p class="text-xs text-[#6B5E55] mt-0.5 leading-relaxed font-medium break-words" x-text="toast.message"></p>
                </div>

                <!-- Close Button -->
                <button type="button" @click="removeToast(toast.id)" class="shrink-0 text-[#8E8076] hover:text-[#2C1408] transition p-1 rounded-lg hover:bg-black/5 cursor-pointer" aria-label="Đóng">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    @yield('scripts')
</body>
</html>
