@extends('layouts.customer')

@section('title', ($selectedCategory ? $selectedCategory->name : 'Danh Sách Gấu Bông') . ' - Mật Ngọt Bear')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/shop.css') }}">
@endsection

@section('content')

@php
    $catName = $selectedCategory ? $selectedCategory->name : 'Tất Cả Sản Phẩm Gấu Bông';
    $subTitle = request('sub') ?? '';
@endphp

<div class="catalog-page-container">

    <!-- 1. BREADCRUMBS -->
    <div class="breadcrumb-nav">
        <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
        <span>&gt;</span>
        @if($selectedCategory)
            <a href="{{ route('products.index', ['category_id' => $selectedCategory->id]) }}">{{ $selectedCategory->name }}</a>
            @if($subTitle)
                <span>&gt;</span>
                <span class="current">{{ $subTitle }}</span>
            @endif
        @else
            <span class="current">Tất Cả Sản Phẩm</span>
        @endif
        @if(request('search'))
            <span>&gt;</span>
            <span>Tìm kiếm: "{{ request('search') }}"</span>
        @endif
    </div>

    <!-- 2. 4 PROMISE BADGES (Exact Match to Image 2) -->
    <div class="promise-badges-row">
        <div class="promise-badge-card" onclick="Swal.fire({title:'Giao Hàng Tận Nhà', text:'Đồng giá ship 30.000đ toàn quốc. Miễn phí ship đơn từ 500k!', icon:'info', confirmButtonColor:'#5D4037'})">
            <div class="promise-icon-circle pink"><i class="fa-solid fa-truck-fast"></i></div>
            <div class="promise-badge-title">GIAO HÀNG TẬN NHÀ</div>
        </div>
        <div class="promise-badge-card" onclick="Swal.fire({title:'Gói Quà Siêu Đẹp', text:'Miễn phí hộp quà thắt nơ, xịt nước hoa thơm dịu và thiệp viết tay theo yêu cầu.', icon:'info', confirmButtonColor:'#5D4037'})">
            <div class="promise-icon-circle honey"><i class="fa-solid fa-gift"></i></div>
            <div class="promise-badge-title">GÓI QUÀ SIÊU ĐẸP</div>
        </div>
        <div class="promise-badge-card" onclick="Swal.fire({title:'Cách Giặt Gấu Bông', text:'Cho vào túi giặt, giặt chế độ êm hoặc tháo bông giặt vỏ riêng với gấu size khủng.', icon:'info', confirmButtonColor:'#5D4037'})">
            <div class="promise-icon-circle blue"><i class="fa-solid fa-pump-soap"></i></div>
            <div class="promise-badge-title">CÁCH GIẶT GẤU BÔNG</div>
        </div>
        <div class="promise-badge-card" onclick="Swal.fire({title:'Bảo Hành Gấu Bông', text:'Bảo hành trọn đời đường chỉ may và độ êm của bông nhồi tại cửa hàng Mật Ngọt Bear.', icon:'info', confirmButtonColor:'#5D4037'})">
            <div class="promise-icon-circle purple"><i class="fa-solid fa-shield-heart"></i></div>
            <div class="promise-badge-title">BẢO HÀNH GẤU BÔNG</div>
        </div>
    </div>

    <!-- 3. CATEGORY SEO & DESCRIPTION HERO (Image 2 Style) -->
    <div class="category-intro-box">
        <h1 class="category-intro-title">
            @if($subTitle)
                Các loại {{ $subTitle }} đẹp nhất Việt Nam
            @elseif($selectedCategory)
                Bộ sưu tập {{ $selectedCategory->name }} cao cấp chính hãng
            @else
                Các loại gấu bông Teddy đẹp và bán chạy nhất tại Mật Ngọt Bear
            @endif
        </h1>
        <div class="category-intro-desc">
            <p>
                Gấu bông tại Mật Ngọt Bear là những mẫu gấu bông được các bạn trẻ và trẻ nhỏ cực kỳ yêu thích. Với lớp lông nhung mềm mịn tự nhiên, 100% ruột nhồi bông PP 3D tinh khiết kháng khuẩn không xẹp lún, mang đến cảm giác ôm êm ái, an toàn và dễ chịu nhất sau những giờ học tập và làm việc căng thẳng.
            </p>

            <div class="category-intro-more" id="cat-intro-more">
                <h4 style="font-size: 16px; font-weight: 800; color: #4E342E; margin-bottom: 8px;">
                    🧸 Đặc điểm nổi bật của dòng {{ $subTitle ?: ($selectedCategory?->name ?: 'Gấu Teddy') }}:
                </h4>
                <p style="margin-bottom: 10px;">
                    Từng sản phẩm đều được kiểm định chất lượng nghiêm ngặt, thêu tay tỉ mỉ và hút chân không đóng hộp quà cẩn thận khi giao đến tay khách hàng. Thích hợp làm quà tặng sinh nhật, kỷ niệm tình yêu, ngày lễ 20/10, Valentine hay trang trí phòng ngủ lãng mạn.
                </p>
            </div>

            <button type="button" class="btn-toggle-readmore" onclick="toggleCatIntro()">
                <span id="readmore-text">Đọc thêm</span> <i class="fa-solid fa-chevron-down" id="readmore-icon"></i>
            </button>
        </div>
    </div>

    <!-- 4. CATALOG 2-COLUMN LAYOUT -->
    <div class="catalog-layout">
        
        <!-- LEFT FILTER SIDEBAR -->
        <aside class="filter-sidebar">
            <div class="filter-sidebar-header">
                <div class="filter-sidebar-title">
                    <i class="fa-solid fa-filter" style="color: var(--honey-dark);"></i>
                    Bộ Lọc Tìm Kiếm
                </div>
                <button type="button" class="btn-icon" onclick="resetAllFilters()" title="Làm mới bộ lọc" style="width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--border); background: var(--bg-surface); cursor: pointer;">
                    <i class="fa-solid fa-rotate-left" style="font-size: 11px;"></i>
                </button>
            </div>

            <!-- Filter 1: Categories -->
            <div class="filter-group">
                <div class="filter-group-title">
                    <span><i class="fa-solid fa-layer-group" style="color: var(--primary);"></i> Danh Mục</span>
                </div>
                <div class="filter-option-list">
                    <label class="filter-checkbox-item">
                        <span>
                            <input type="radio" name="cat_filter" value="" {{ !request('category_id') ? 'checked' : '' }} onchange="applyFilters()">
                            Tất cả danh mục
                        </span>
                    </label>
                    @foreach($categories as $cat)
                        <label class="filter-checkbox-item">
                            <span>
                                <input type="radio" name="cat_filter" value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'checked' : '' }} onchange="applyFilters()">
                                {{ $cat->name }}
                            </span>
                            <span class="filter-count">{{ $cat->products_count ?? 0 }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Filter 2: Price Range -->
            <div class="filter-group">
                <div class="filter-group-title">
                    <span><i class="fa-solid fa-tag" style="color: var(--primary);"></i> Khoảng Giá (VNĐ)</span>
                </div>
                <div class="price-range-inputs">
                    <input type="number" id="filter-min-price" class="price-input" placeholder="Từ (đ)" value="{{ request('min_price') }}">
                    <input type="number" id="filter-max-price" class="price-input" placeholder="Đến (đ)" value="{{ request('max_price') }}">
                </div>
                <div class="price-preset-chips" style="margin-bottom: 10px;">
                    <span class="preset-chip" onclick="setPricePreset(0, 200000)">&lt; 200k</span>
                    <span class="preset-chip" onclick="setPricePreset(200000, 500000)">200k - 500k</span>
                    <span class="preset-chip" onclick="setPricePreset(500000, 1000000)">500k - 1tr</span>
                    <span class="preset-chip" onclick="setPricePreset(1000000, 3000000)">&gt; 1tr</span>
                </div>
                <button type="button" class="btn-honey-main" style="width: 100%; padding: 8px 14px; font-size: 12px; justify-content: center;" onclick="applyFilters()">
                    <i class="fa-solid fa-magnifying-glass"></i> Áp Dụng Giá
                </button>
            </div>

            <!-- Filter 3: Size -->
            <div class="filter-group">
                <div class="filter-group-title">
                    <span><i class="fa-solid fa-ruler" style="color: var(--primary);"></i> Kích Thước (Size)</span>
                </div>
                <div class="chip-cloud" id="size-chips-cloud">
                    <span class="select-chip {{ request('size') == '30cm' ? 'active' : '' }}" onclick="selectSizeFilter('30cm')">30cm</span>
                    <span class="select-chip {{ request('size') == '35cm' ? 'active' : '' }}" onclick="selectSizeFilter('35cm')">35cm</span>
                    <span class="select-chip {{ request('size') == '40cm' ? 'active' : '' }}" onclick="selectSizeFilter('40cm')">40cm</span>
                    <span class="select-chip {{ request('size') == '45cm' ? 'active' : '' }}" onclick="selectSizeFilter('45cm')">45cm</span>
                    <span class="select-chip {{ request('size') == '50cm' ? 'active' : '' }}" onclick="selectSizeFilter('50cm')">50cm</span>
                    <span class="select-chip {{ request('size') == '60cm' ? 'active' : '' }}" onclick="selectSizeFilter('60cm')">60cm</span>
                    <span class="select-chip {{ request('size') == '1m2' ? 'active' : '' }}" onclick="selectSizeFilter('1m2')">1m2</span>
                    <span class="select-chip {{ request('size') == '1m6' ? 'active' : '' }}" onclick="selectSizeFilter('1m6')">1m6</span>
                    <span class="select-chip {{ request('size') == '1m8' ? 'active' : '' }}" onclick="selectSizeFilter('1m8')">1m8 - 2m</span>
                </div>
            </div>

            <!-- Filter 4: In Stock -->
            <div class="filter-group">
                <label class="filter-checkbox-item" style="font-weight: 800; color: var(--success);">
                    <span>
                        <input type="checkbox" id="filter-in-stock" {{ request('in_stock') ? 'checked' : '' }} onchange="applyFilters()">
                        <i class="fa-solid fa-circle-check"></i> Chỉ hiện sản phẩm còn hàng
                    </span>
                </label>
            </div>
        </aside>

        <!-- RIGHT PRODUCTS MAIN AREA -->
        <section class="catalog-main">
            
            <!-- Toolbar (Image 2 style) -->
            <div class="catalog-toolbar">
                <div class="toolbar-results-info" id="catalog-results-info">
                    Hiển thị tất cả kết quả...
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <label for="catalog-sort-select" style="font-size: 13px; font-weight: 700; color: var(--text-muted);">Sắp xếp theo:</label>
                    <select id="catalog-sort-select" class="select-sort-control" onchange="applyFilters()">
                        <option value="latest" {{ request('sort') == 'latest' || !request('sort') ? 'selected' : '' }}>Mới nhất</option>
                        <option value="best_seller" {{ request('sort') == 'best_seller' ? 'selected' : '' }}>Bán chạy nhất</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid Container (Image 2 Style) -->
            <div class="shop-products-grid" id="catalog-products-grid">
                <!-- Populated via AJAX / API -->
            </div>

            <!-- Pagination Controls -->
            <div class="shop-pagination-wrap" id="catalog-pagination-wrap">
                <!-- Dynamically Rendered -->
            </div>
        </section>

    </div>
</div>

@endsection

@section('scripts')
<script>
    let selectedSize = "{{ request('size') }}";
    let selectedColor = "{{ request('color') }}";
    let currentPage = 1;

    document.addEventListener('DOMContentLoaded', () => {
        loadCatalogProducts();
    });

    function toggleCatIntro() {
        const moreBox = document.getElementById('cat-intro-more');
        const text = document.getElementById('readmore-text');
        const icon = document.getElementById('readmore-icon');
        if (moreBox.classList.contains('open')) {
            moreBox.classList.remove('open');
            text.innerText = 'Đọc thêm';
            icon.className = 'fa-solid fa-chevron-down';
        } else {
            moreBox.classList.add('open');
            text.innerText = 'Thu gọn';
            icon.className = 'fa-solid fa-chevron-up';
        }
    }

    function setPricePreset(min, max) {
        document.getElementById('filter-min-price').value = min;
        document.getElementById('filter-max-price').value = max;
        applyFilters();
    }

    function selectSizeFilter(size) {
        selectedSize = (selectedSize === size) ? '' : size;
        updateChipClasses('size-chips-cloud', selectedSize);
        applyFilters();
    }

    function updateChipClasses(containerId, activeVal) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.querySelectorAll('.select-chip').forEach(chip => {
            if (chip.innerText.trim().includes(activeVal) && activeVal !== '') {
                chip.classList.add('active');
            } else {
                chip.classList.remove('active');
            }
        });
    }

    function resetAllFilters() {
        document.querySelectorAll('input[name="cat_filter"]').forEach(r => r.checked = (r.value === ''));
        document.getElementById('filter-min-price').value = '';
        document.getElementById('filter-max-price').value = '';
        document.getElementById('filter-in-stock').checked = false;
        selectedSize = '';
        selectedColor = '';
        updateChipClasses('size-chips-cloud', '');
        document.getElementById('catalog-sort-select').value = 'latest';
        currentPage = 1;
        loadCatalogProducts();
    }

    function applyFilters(page = 1) {
        currentPage = page;
        loadCatalogProducts();
    }

    async function loadCatalogProducts() {
        const grid = document.getElementById('catalog-products-grid');
        const info = document.getElementById('catalog-results-info');
        const pagWrap = document.getElementById('catalog-pagination-wrap');

        grid.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 4rem 1rem;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 32px; color: var(--honey);"></i>
                <div style="margin-top: 10px; font-weight: 700; color: var(--text-muted); font-size: 13.5px;">Đang tìm kiếm những chú gấu bông phù hợp...</div>
            </div>
        `;

        const selectedCat = document.querySelector('input[name="cat_filter"]:checked');
        const categoryId = selectedCat ? selectedCat.value : '{{ request("category_id") }}';
        const minPrice = document.getElementById('filter-min-price').value;
        const maxPrice = document.getElementById('filter-max-price').value;
        const inStock = document.getElementById('filter-in-stock').checked;
        const sort = document.getElementById('catalog-sort-select').value;
        const urlParams = new URLSearchParams(window.location.search);
        const search = urlParams.get('search') || '';

        const params = new URLSearchParams({
            page: currentPage,
            per_page: 9,
            sort: sort
        });

        if (search) params.append('search', search);
        if (categoryId) params.append('category_id', categoryId);
        if (minPrice) params.append('min_price', minPrice);
        if (maxPrice) params.append('max_price', maxPrice);
        if (selectedSize) params.append('size', selectedSize);
        if (inStock) params.append('in_stock', '1');

        try {
            const res = await fetch(`/api/products?${params.toString()}`);
            const data = await res.json();

            if (data.success) {
                renderCatalogGrid(data.data, data.meta);
            }
        } catch (err) {
            console.error("Lỗi tải sản phẩm:", err);
            grid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem 1rem; color: var(--danger);">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 32px;"></i>
                    <p style="margin-top: 8px; font-weight: 700;">Không thể tải danh sách sản phẩm. Vui lòng thử lại.</p>
                </div>
            `;
        }
    }

    function renderCatalogGrid(products, meta) {
        try {
            const grid = document.getElementById('catalog-products-grid');
            const info = document.getElementById('catalog-results-info');
            const pagWrap = document.getElementById('catalog-pagination-wrap');

            if (!grid) return;

            if (!products || products.length === 0) {
                if (info) info.innerText = 'Không tìm thấy sản phẩm nào';
                grid.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; padding: 4rem 1rem; background: #FFFFFF; border-radius: var(--radius-xl); border: 1px dashed var(--border);">
                        <i class="fa-solid fa-box-open" style="font-size: 48px; color: var(--border); margin-bottom: 1rem;"></i>
                        <h4 style="font-size: 18px; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">Không tìm thấy chú gấu bông nào phù hợp!</h4>
                        <p style="color: var(--text-muted); font-size: 13.5px; margin-bottom: 1.5rem;">Hãy thử xóa bớt tiêu chí lọc hoặc tìm kiếm với từ khóa khác nhé.</p>
                        <button class="btn-honey-main" onclick="resetAllFilters()">
                            <i class="fa-solid fa-rotate-left"></i> Xóa Tất Cả Bộ Lọc
                        </button>
                    </div>
                `;
                if (pagWrap) pagWrap.innerHTML = '';
                return;
            }

            if (info && meta) {
                info.innerHTML = `Hiển thị tất cả <strong>${meta.total}</strong> kết quả (Trang ${meta.current_page}/${meta.last_page})`;
            }

            grid.innerHTML = products.map(p => {
                const primaryImg = (p.images && p.images.find(img => img && img.is_primary)) || (p.images && p.images[0]) || null;
                const imgUrl = (primaryImg && primaryImg.image_url) ? primaryImg.image_url : 'https://placehold.co/600x600/f5e6ca/7c4a2d?text=' + encodeURIComponent(p.name || 'Gau Bong');
                const price = Number(p.price || 0);
                const salePrice = p.sale_price ? Number(p.sale_price) : null;
                const isOnSale = p.is_on_sale !== undefined ? Boolean(p.is_on_sale) : (salePrice !== null && salePrice < price);
                const discountPct = (isOnSale && price > 0 && salePrice) ? Math.round(((price - salePrice) / price) * 100) : 0;
                const nameEscaped = (p.name || 'Gấu bông').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const catName = (p.category && p.category.name) ? p.category.name : 'Gấu Bông';

                return `
                    <div class="product-grid-item">
                        <div class="product-photo-wrap">
                            ${isOnSale ? `<span class="card-badge-sale">-${discountPct}%</span>` : ''}
                            <button type="button" class="btn-wishlist-card" data-product-id="${p.id}" onclick="toggleWishlist({ id: ${p.id}, name: '${nameEscaped}', price: ${price}, sale_price: ${salePrice || 'null'}, image_url: '${imgUrl}' }, event)" title="Lưu vào yêu thích">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                            <a href="/products/${p.id}">
                                <img src="${imgUrl}" alt="${nameEscaped}" class="product-photo-img" onerror="this.src='https://placehold.co/600x600/f5e6ca/7c4a2d?text=Gau+Bong'">
                            </a>
                        </div>
                        <div class="product-info-wrap">
                            <div>
                                <div style="font-size: 11px; font-weight: 700; color: var(--text-light); text-transform: uppercase; margin-bottom: 4px;">
                                    ${catName}
                                </div>
                                <a href="/products/${p.id}">
                                    <h3 class="product-item-title">${p.name || ''}</h3>
                                </a>
                            </div>
                            <div>
                                <div class="product-card-prices">
                                    ${isOnSale && salePrice
                                        ? `<span class="price-current">${salePrice.toLocaleString('vi-VN')} đ</span><span class="price-old">${price.toLocaleString('vi-VN')} đ</span>`
                                        : `<span class="price-current" style="color: var(--primary-dark);">${price.toLocaleString('vi-VN')} đ</span>`
                                    }
                                </div>
                                <div class="product-card-footer">
                                    <span><i class="fa-solid fa-ruler" style="color: var(--text-light);"></i> ${p.size || 'Size chuẩn'}</span>
                                    ${(p.stock_quantity > 0)
                                        ? `<button type="button" class="btn-add-cart-quick" onclick="addToCart(${p.id}, '${nameEscaped}')" title="Thêm vào giỏ hàng">
                                            <i class="fa-solid fa-plus"></i>
                                           </button>`
                                        : `<button type="button" class="btn-add-cart-quick" style="opacity: 0.5; background: #e5e5e5; color: #888; cursor: not-allowed;" onclick="if(!window.isCustomerAuthenticated) { openAuthModal(window.location.href, 'Đăng nhập để Thêm vào giỏ'); } else { Toast.fire({icon: 'warning', title: 'Sản phẩm tạm hết hàng!'}); }" title="Tạm hết hàng">
                                            <i class="fa-solid fa-ban"></i>
                                           </button>`
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            try {
                if (typeof updateWishlistBadge === 'function') {
                    updateWishlistBadge();
                }
            } catch(e) {
                console.warn('Wishlist sync skipped:', e);
            }

            if (meta) {
                renderCatalogPagination(meta);
            }
        } catch (e) {
            console.error('Error rendering catalog grid:', e);
        }
    }

    function renderCatalogPagination(meta) {
        const wrap = document.getElementById('catalog-pagination-wrap');
        if (!wrap) return;
        if (!meta || meta.last_page <= 1) {
            wrap.innerHTML = '';
            return;
        }

        let html = '';
        html += `<button class="shop-page-btn" ${meta.current_page === 1 ? 'disabled' : ''} onclick="applyFilters(${meta.current_page - 1})"><i class="fa-solid fa-chevron-left"></i></button>`;

        for (let i = 1; i <= meta.last_page; i++) {
            if (i === 1 || i === meta.last_page || (i >= meta.current_page - 1 && i <= meta.current_page + 1)) {
                html += `<button class="shop-page-btn ${i === meta.current_page ? 'active' : ''}" onclick="applyFilters(${i})">${i}</button>`;
            } else if (i === meta.current_page - 2 || i === meta.current_page + 2) {
                html += `<span style="padding: 0 4px; color: var(--text-light); font-weight: 800;">...</span>`;
            }
        }

        html += `<button class="shop-page-btn" ${meta.current_page === meta.last_page ? 'disabled' : ''} onclick="applyFilters(${meta.current_page + 1})"><i class="fa-solid fa-chevron-right"></i></button>`;
        wrap.innerHTML = html;
    }
</script>
@endsection
