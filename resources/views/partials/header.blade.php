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

    <!-- Central Search Bar with Live Suggestions Dropdown -->
    <form action="{{ route('products.index') }}#catalog-layout" method="GET" class="header-search-form" id="headerSearchForm">
        <div class="search-input-wrapper">
            <input 
                type="text" 
                name="search" 
                id="headerSearchInput"
                class="search-input" 
                placeholder="Tìm kiếm gấu bông yêu thích (Teddy, Capybara, Loopy...)"
                value="{{ request('search') }}"
                autocomplete="off"
            >
            <button type="submit" class="search-submit-btn" title="Tìm kiếm">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>

        <!-- Live Search Suggestions Dropdown -->
        <div class="header-search-dropdown" id="headerSearchDropdown">
            <div class="search-dropdown-header">
                <span><i class="fa-solid fa-paw" style="color: var(--honey-dark);"></i> Gấu bông gợi ý cho bạn</span>
                <span class="search-count-tag" id="searchCountTag">0 kết quả</span>
            </div>
            <div class="search-dropdown-list" id="searchDropdownList">
                <!-- Danh sách sản phẩm gợi ý tự động -->
            </div>
            <div class="search-dropdown-footer">
                <button type="button" class="btn-view-all-search" onclick="submitHeaderSearch()">
                    Xem tất cả kết quả cho "<span id="searchKeywordPreview"></span>"
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </form>

    <!-- Utility Group -->
    <div class="header-utility-group">
        <!-- Hotline -->
        <a href="tel:0377466205" class="hotline-pill">
            <div class="hotline-icon">
                <i class="fa-solid fa-phone"></i>
            </div>
            <div class="hotline-meta">
                <span class="label">Hotline 24/7</span>
                <span class="number">037.746.6205</span>
            </div>
        </a>

        <!-- {{-- Tiện ích mua sắm khách hàng (Yêu thích, Giỏ hàng) - Chỉ hiển thị cho Khách hàng & Khách vãng lai, ẩn với Admin & Nhân viên --}} -->
        @if(!auth()->check() || auth()->user()->role === 'CUSTOMER')
            <!-- Wishlist (Yêu thích) -->
            @auth
                <a href="{{ route('customer.wishlist.index') }}" class="utility-icon-btn" id="wishlist-header-btn" title="Danh sách yêu thích">
            @endauth
            @guest
                <a href="javascript:void(0)" onclick="if(typeof openAuthModal === 'function') { openAuthModal(); } else { window.location.href='{{ route('login') }}'; }" class="utility-icon-btn" id="wishlist-header-btn" title="Danh sách yêu thích">
            @endguest
                <i class="fa-solid fa-heart" style="font-size: 16px; color: #E57373;"></i>
                <span class="badge-count" id="wishlist-count">0</span>
            </a>

            <!-- Cart (Giỏ hàng) -->
            @auth
                <a href="{{ route('customer.cart') }}" class="utility-icon-btn {{ request()->routeIs('customer.cart*') ? 'active' : '' }}" title="Giỏ hàng">
            @endauth
            @guest
                <a href="javascript:void(0)" onclick="if(typeof openAuthModal === 'function') { openAuthModal('{{ route('customer.cart') }}', 'Đăng nhập xem Giỏ hàng', 'Vui lòng đăng nhập hoặc tạo tài khoản Mật Ngọt Bear để xem giỏ hàng và thanh toán nhé!'); } else { window.location.href='{{ route('login', ['redirect' => route('customer.cart')]) }}'; }" class="utility-icon-btn {{ request()->routeIs('customer.cart*') ? 'active' : '' }}" title="Giỏ hàng">
            @endguest
                <i class="fa-solid fa-bag-shopping" style="font-size: 16px; color: var(--honey-dark);"></i>
                <span class="badge-count" id="cart-count">{{ (int) ($realCartCount ?? 0) > 99 ? '99+' : (int) ($realCartCount ?? 0) }}</span>
            </a>
        @endif

        <!-- {{-- Kho voucher: Hiển thị cho Khách hàng, Nhân viên tư vấn & Khách vãng lai, ẩn với Admin --}} -->
        @if(!auth()->check() || in_array(auth()->user()->role, ['CUSTOMER', 'STAFF']))
            <!-- Vouchers (Kho voucher khuyến mãi) -->
            @auth
                <a href="{{ route('customer.vouchers.index') }}" class="utility-icon-btn {{ request()->routeIs('customer.vouchers.*') ? 'active' : '' }}" title="Kho voucher & khuyến mãi">
            @endauth
            @guest
                <a href="javascript:void(0)" onclick="if(typeof openAuthModal === 'function') { openAuthModal('{{ route('customer.vouchers.index') }}', 'Đăng nhập xem Kho Voucher', 'Vui lòng đăng nhập hoặc tạo tài khoản Mật Ngọt Bear để xem toàn bộ voucher và nhận ưu đãi nhé!'); } else { window.location.href='{{ route('login', ['redirect' => route('customer.vouchers.index')]) }}'; }" class="utility-icon-btn {{ request()->routeIs('customer.vouchers.*') ? 'active' : '' }}" title="Kho voucher & khuyến mãi">
            @endguest
                <i class="fa-solid fa-ticket" style="font-size: 16px; color: #E08A1E;"></i>
                @if(($availableVoucherCount ?? 0) > 0)
                    <span class="badge-count" style="background: #E08A1E; color: #ffffff;">{{ (int) ($availableVoucherCount ?? 0) > 99 ? '99+' : (int) ($availableVoucherCount ?? 0) }}</span>
                @endif
            </a>
        @endif

        <!-- Nút Đăng nhập / Đăng xuất & Tài khoản -->
        <div style="position: relative;">
            @guest
                <a
                    href="{{ route('login', ['redirect' => route('home', absolute: false)]) }}"
                    class="btn-auth-pill"
                    title="Đăng nhập"
                >
                    <i class="fa-solid fa-right-to-bracket"></i> ĐĂNG NHẬP
                </a>
            @endguest

            @auth
                @php
                    $u = auth()->user();
                    $userRole = $u->role;
                    $avatarUrl = $u->avatar_url 
                        ?: ($u->avatar && file_exists(public_path('storage/' . $u->avatar)) ? asset('storage/' . $u->avatar) : null);
                    if (!$avatarUrl && $u->avatar && (str_starts_with($u->avatar, 'http://') || str_starts_with($u->avatar, 'https://'))) {
                        $avatarUrl = $u->avatar;
                    }
                    if (!$avatarUrl) {
                        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($u->full_name ?: 'User') . '&background=EAD8C3&color=4A2E2B&bold=true';
                    }
                @endphp

                <!-- 1 Icon Avatar của người đang đăng nhập -->
                <a href="javascript:void(0)" class="header-user-avatar-btn" onclick="this.parentElement.querySelector('.dropdown-menu').classList.toggle('show')" title="{{ $u->full_name }}">
                    <img src="{{ $avatarUrl }}" alt="{{ $u->full_name }}" class="header-avatar-circle">
                </a>

                <!-- Khung Popup Người Dùng (Theo Ảnh 4) -->
                <div class="dropdown-menu role-dropdown" style="right: 0; left: auto; min-width: 250px; border-radius: 18px; box-shadow: 0 12px 36px rgba(62, 39, 35, 0.12); padding: 6px 0; border: 1px solid var(--border-light); background: #FFFFFF;">
                    <!-- Header người dùng trong popup: Avatar + Họ tên + Email -->
                    <div style="padding: 14px 16px; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; gap: 12px;">
                        <img src="{{ $avatarUrl }}" alt="{{ $u->full_name }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #EAD8C3; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                        <div style="min-width: 0; overflow: hidden; flex: 1;">
                            <div style="font-weight: 700; font-size: 14px; color: #3E2723; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $u->full_name }}</div>
                            <div style="font-size: 11.5px; color: #8D6E63; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;" title="{{ $u->email }}">{{ $u->email }}</div>
                        </div>
                    </div>

                    @if($userRole === 'ADMIN')
                        <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                            <span><i class="fa-solid fa-gauge-high" style="color: #8D6E63; margin-right: 8px;"></i> Quản Lý Admin</span>
                            <i class="fa-solid fa-arrow-right" style="font-size: 10px; color: var(--text-light);"></i>
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="dropdown-item">
                            <span><i class="fa-solid fa-clipboard-list" style="color: #8D6E63; margin-right: 8px;"></i> Quản Lý Đơn Hàng</span>
                            <i class="fa-solid fa-arrow-right" style="font-size: 10px; color: var(--text-light);"></i>
                        </a>
                    @elseif($userRole === 'STAFF')
                        <a href="{{ route('staff.orders.index') }}" class="dropdown-item">
                            <span><i class="fa-solid fa-boxes-packing" style="color: #8D6E63; margin-right: 8px;"></i> Bảng Xử Lý</span>
                            <i class="fa-solid fa-arrow-right" style="font-size: 10px; color: var(--text-light);"></i>
                        </a>
                    @else
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <span><i class="fa-solid fa-user-gear" style="color: #8D6E63; margin-right: 8px;"></i> Tài Khoản Của Tôi</span>
                        </a>
                    @endif

                    <div style="border-top: 1px solid var(--border-light); margin-top: 4px; padding-top: 4px;">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-size: 13px; font-weight: 600; color: var(--danger); display: flex; align-items: center; justify-content: flex-start !important; gap: 8px;">
                                <i class="fa-solid fa-right-from-bracket" style="font-size: 14px;"></i>
                                <span>Đăng Xuất</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('headerSearchForm');
    const searchInput = document.getElementById('headerSearchInput');
    const searchDropdown = document.getElementById('headerSearchDropdown');
    const searchDropdownList = document.getElementById('searchDropdownList');
    const searchCountTag = document.getElementById('searchCountTag');
    const searchKeywordPreview = document.getElementById('searchKeywordPreview');

    if (!searchInput || !searchDropdown) return;

    let debounceTimer = null;
    let currentSelectedIndex = -1;

    function formatVND(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' })
            .format(amount || 0)
            .replace('₫', 'đ');
    }

    function highlightKeyword(text, keyword) {
        if (!keyword || !text) return text;
        // Escape regex special chars
        const cleanKeyword = keyword.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        if (!cleanKeyword) return text;

        try {
            const regex = new RegExp(`(${cleanKeyword})`, 'gi');
            return text.replace(regex, '<mark>$1</mark>');
        } catch (e) {
            return text;
        }
    }

    function doSearch(keyword) {
        keyword = keyword.trim();
        if (keyword.length < 1) {
            searchDropdown.classList.remove('show');
            return;
        }

        if (searchKeywordPreview) {
            searchKeywordPreview.textContent = keyword;
        }

        fetch(`/api/products/search-suggestions?q=${encodeURIComponent(keyword)}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) {
                    searchDropdown.classList.remove('show');
                    return;
                }

                const items = res.data || [];
                currentSelectedIndex = -1;

                if (items.length === 0) {
                    searchCountTag.textContent = '0 kết quả';
                    searchDropdownList.innerHTML = `
                        <div class="search-dropdown-empty">
                            <i class="fa-solid fa-box-open"></i>
                            <p>Không tìm thấy gấu bông "${escapeHtml(keyword)}"</p>
                            <span>Thử tìm với tên gọi khác như: Teddy, Capybara, Bơ, Loopy...</span>
                        </div>
                    `;
                } else {
                    searchCountTag.textContent = `${items.length} kết quả`;
                    searchDropdownList.innerHTML = items.map((item, idx) => {
                        const highlightedName = highlightKeyword(escapeHtml(item.name), keyword);
                        const categoryText = item.category_name ? escapeHtml(item.category_name) : 'Gấu bông Mật Ngọt';
                        const thumb = item.thumbnail || '/images/default-bear.png';
                        const price = formatVND(item.min_price);

                        return `
                            <a href="${item.url}" class="search-suggestion-item" data-index="${idx}">
                                <img src="${thumb}" alt="${escapeHtml(item.name)}" class="suggestion-thumb" onerror="this.src='/images/default-bear.png'">
                                <div class="suggestion-info">
                                    <span class="suggestion-name">${highlightedName}</span>
                                    <span class="suggestion-category"><i class="fa-solid fa-tag" style="font-size: 10px; margin-right: 4px;"></i>${categoryText}</span>
                                </div>
                                <span class="suggestion-price">${price}</span>
                            </a>
                        `;
                    }).join('');
                }

                searchDropdown.classList.add('show');
            })
            .catch(err => {
                console.error('Search suggestion error:', err);
            });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>"']/g, function(m) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[m];
        });
    }

    // Input typing with debounce
    searchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        const val = e.target.value;
        debounceTimer = setTimeout(() => {
            doSearch(val);
        }, 180);
    });

    // Reopen dropdown on focus if has text
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            doSearch(this.value);
        }
    });

    // Keyboard navigation (Arrow Up / Arrow Down / Enter)
    searchInput.addEventListener('keydown', function(e) {
        const items = searchDropdownList.querySelectorAll('.search-suggestion-item');
        if (!items || items.length === 0 || !searchDropdown.classList.contains('show')) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentSelectedIndex = (currentSelectedIndex + 1) % items.length;
            updateSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentSelectedIndex = (currentSelectedIndex - 1 + items.length) % items.length;
            updateSelection(items);
        } else if (e.key === 'Enter') {
            if (currentSelectedIndex >= 0 && currentSelectedIndex < items.length) {
                e.preventDefault();
                items[currentSelectedIndex].click();
            }
        } else if (e.key === 'Escape') {
            searchDropdown.classList.remove('show');
        }
    });

    function updateSelection(items) {
        items.forEach((it, idx) => {
            if (idx === currentSelectedIndex) {
                it.classList.add('active');
                it.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                it.classList.remove('active');
            }
        });
    }

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        if (searchForm && !searchForm.contains(e.target)) {
            searchDropdown.classList.remove('show');
        }
    });

    // Handle button view all search
    window.submitHeaderSearch = function() {
        if (searchForm) {
            searchForm.submit();
        }
    };
});
</script>

