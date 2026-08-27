<!-- Main Header Top Row (Không gồm thanh danh mục) -->
<div class="header-main-row">
    <!-- Brand Logo -->
    <a href="{{ route('home') }}" class="brand-logo">
        <div class="brand-icon">
            <i class="fa-solid fa-paw"></i>
        </div>
        <div class="brand-text">
            <span class="brand-name">Mật Ngọt Bear</span>
            <span class="brand-slogan">Thế Giới Gấu Bông</span>
        </div>
    </a>

    <!-- Central Search Bar -->
    <form action="{{ route('products.index') }}" method="GET" class="header-search-form">
        <div class="search-input-wrapper">
            <input 
                type="text" 
                name="search" 
                class="search-input" 
                placeholder="Tìm kiếm gấu bông yêu thích (Teddy, Capybara, Loopy...)"
                value="{{ request('search') }}"
                autocomplete="off"
            >
            <button type="submit" class="search-submit-btn" title="Tìm kiếm">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>

    <!-- Utility Group -->
    <div class="header-utility-group">
        <!-- Hotline -->
        <a href="tel:0979896616" class="hotline-pill">
            <div class="hotline-icon">
                <i class="fa-solid fa-phone"></i>
            </div>
            <div class="hotline-meta">
                <span class="label">Hotline 24/7</span>
                <span class="number">097.989.6616</span>
            </div>
        </a>

        <!-- Wishlist (Yêu thích - Chờ bạn nhóm gắn link trang riêng) -->
        <a href="#" class="utility-icon-btn" id="wishlist-header-btn" title="Danh sách yêu thích">
            <i class="fa-solid fa-heart" style="font-size: 16px; color: #E57373;"></i>
            <span class="badge-count" id="wishlist-count">0</span>
        </a>

        <!-- Cart (Giỏ hàng) -->
        <a href="{{ route('customer.cart') }}" class="utility-icon-btn" title="Giỏ hàng">
            <i class="fa-solid fa-bag-shopping" style="font-size: 16px; color: var(--honey-dark);"></i>
            <span class="badge-count" id="cart-count">{{ (int) ($realCartCount ?? 0) }}</span>
        </a>

        <!-- My Orders (Đơn của tôi - Chờ đường dẫn của bạn nhóm) -->
        <a href="#" class="utility-icon-btn" title="Đơn hàng của tôi">
            <i class="fa-solid fa-clipboard-list" style="font-size: 16px; color: #8D6E63;"></i>
        </a>

        <!-- Nút Đăng nhập / Đăng xuất & Tài khoản (Chờ đường dẫn của bạn nhóm) -->
        <div style="position: relative;">
            @guest
                <a href="#" class="btn-auth-pill" title="Đăng nhập">
                    <i class="fa-solid fa-right-to-bracket"></i> ĐĂNG NHẬP
                </a>
            @endguest

            @auth
                @php $userRole = auth()->user()->role; @endphp
                @if($userRole === 'ADMIN')
                    <a href="javascript:void(0)" class="btn-auth-pill" style="background: linear-gradient(135deg, #8D6E63 0%, #6D4C41 100%);" onclick="this.parentElement.querySelector('.dropdown-menu').classList.toggle('show')">
                        <i class="fa-solid fa-shield-halved"></i> ADMIN <i class="fa-solid fa-chevron-down" style="font-size: 9px;"></i>
                    </a>
                    <div class="dropdown-menu role-dropdown" style="right: 0; left: auto; min-width: 220px;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; gap: 10px;">
                            <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #8D6E63, #6D4C41); color: #fff; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-user-shield"></i></div>
                            <div>
                                <div style="font-weight: 700; font-size: 12.5px; color: #5D4037;">{{ auth()->user()->full_name }}</div>
                                <div style="font-size: 10.5px; color: var(--text-light);">Quản trị viên</div>
                            </div>
                        </div>
                        <a href="#" class="dropdown-item">
                            <span><i class="fa-solid fa-user-pen" style="color: #8D6E63; margin-right: 6px;"></i> Hồ Sơ Cá Nhân</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                            <span><i class="fa-solid fa-gauge-high" style="color: #8D6E63; margin-right: 6px;"></i> Quản Lý</span>
                            <i class="fa-solid fa-arrow-right" style="font-size: 10px; color: var(--text-light);"></i>
                        </a>
                        <div style="border-top: 1px solid var(--border-light); margin-top: 4px; padding-top: 4px;">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-size: 12.5px; font-weight: 600; color: var(--danger);">
                                    <span><i class="fa-solid fa-right-from-bracket" style="margin-right: 6px;"></i> Đăng Xuất</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @elseif($userRole === 'STAFF')
                    <a href="javascript:void(0)" class="btn-auth-pill" style="background: linear-gradient(135deg, #A1887F 0%, #795548 100%);" onclick="this.parentElement.querySelector('.dropdown-menu').classList.toggle('show')">
                        <i class="fa-solid fa-user-tag"></i> STAFF <i class="fa-solid fa-chevron-down" style="font-size: 9px;"></i>
                    </a>
                    <div class="dropdown-menu role-dropdown" style="right: 0; left: auto; min-width: 220px;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; gap: 10px;">
                            <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #A1887F, #795548); color: #fff; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-user-tie"></i></div>
                            <div>
                                <div style="font-weight: 700; font-size: 12.5px; color: #5D4037;">{{ auth()->user()->full_name }}</div>
                                <div style="font-size: 10.5px; color: var(--text-light);">Nhân viên</div>
                            </div>
                        </div>
                        <a href="#" class="dropdown-item">
                            <span><i class="fa-solid fa-user-pen" style="color: #8D6E63; margin-right: 6px;"></i> Hồ Sơ Cá Nhân</span>
                        </a>
                        <a href="{{ route('staff.dashboard') }}" class="dropdown-item">
                            <span><i class="fa-solid fa-boxes-packing" style="color: #8D6E63; margin-right: 6px;"></i> Xử Lý</span>
                            <i class="fa-solid fa-arrow-right" style="font-size: 10px; color: var(--text-light);"></i>
                        </a>
                        <div style="border-top: 1px solid var(--border-light); margin-top: 4px; padding-top: 4px;">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-size: 12.5px; font-weight: 600; color: var(--danger);">
                                    <span><i class="fa-solid fa-right-from-bracket" style="margin-right: 6px;"></i> Đăng Xuất</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="javascript:void(0)" class="btn-auth-pill" style="background: linear-gradient(135deg, #D7CCC8 0%, #BCAAA4 100%); color: #4E342E !important;" onclick="this.parentElement.querySelector('.dropdown-menu').classList.toggle('show')">
                        <i class="fa-solid fa-user"></i> KHÁCH HÀNG <i class="fa-solid fa-chevron-down" style="font-size: 9px;"></i>
                    </a>
                    <div class="dropdown-menu role-dropdown" style="right: 0; left: auto; min-width: 220px;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; gap: 10px;">
                            <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #D7CCC8, #BCAAA4); color: #4E342E; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-user"></i></div>
                            <div>
                                <div style="font-weight: 700; font-size: 12.5px; color: #5D4037;">{{ auth()->user()->full_name }}</div>
                                <div style="font-size: 10.5px; color: var(--text-light);">Khách hàng thân thiết</div>
                            </div>
                        </div>
                        <a href="#" class="dropdown-item">
                            <span><i class="fa-solid fa-user-gear" style="color: #8D6E63; margin-right: 6px;"></i> Tài Khoản Của Tôi</span>
                        </a>
                        <div style="border-top: 1px solid var(--border-light); margin-top: 4px; padding-top: 4px;">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-size: 12.5px; font-weight: 600; color: var(--danger);">
                                    <span><i class="fa-solid fa-right-from-bracket" style="margin-right: 6px;"></i> Đăng Xuất</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</div>
