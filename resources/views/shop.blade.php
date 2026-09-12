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
        @if(request('voucher'))
            <span>&gt;</span>
            <span class="current">Mã ưu đãi: {{ request('voucher') }}</span>
        @endif
    </div>

    <!-- 2. 4 PROMISE BADGES (Exact Match to Image 2) -->
    <div class="promise-badges-row">
        <div class="promise-badge-card" onclick="Swal.fire({title:'Giao Hàng Tận Nhà', text:'Giao hàng nhanh GHN toàn quốc, cước phí chuẩn xác theo địa chỉ nhận hàng và hỗ trợ voucher freeship!', icon:'info', confirmButtonColor:'#5D4037'})">
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
    <div class="catalog-layout" id="catalog-layout">
        
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
                            <input type="radio" name="cat_filter" value="" {{ !request('category_id') ? 'checked' : '' }} onchange="onCategoryFilterChange(this)">
                            Tất cả danh mục
                        </span>
                    </label>
                    @foreach($categories as $cat)
                        <label class="filter-checkbox-item">
                            <span>
                                <input type="radio" name="cat_filter" value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'checked' : '' }} onchange="onCategoryFilterChange(this)">
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
                    <input type="text" inputmode="numeric" id="filter-min-price" class="price-input" placeholder="Từ (đ)" value="{{ request('min_price') ? number_format((float)request('min_price'), 0, ',', '.') : '' }}" onblur="formatShopPrice(this)" onkeydown="if(event.key==='Enter'){formatShopPrice(this);applyFilters();}">
                    <span class="price-range-separator">-</span>
                    <input type="text" inputmode="numeric" id="filter-max-price" class="price-input" placeholder="Đến (đ)" value="{{ request('max_price') ? number_format((float)request('max_price'), 0, ',', '.') : '' }}" onblur="formatShopPrice(this)" onkeydown="if(event.key==='Enter'){formatShopPrice(this);applyFilters();}">
                    <button type="button" class="btn-clear-trash" id="btn-clear-price" onclick="clearPriceFilter()" title="Xoá khoảng giá">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
                <div class="price-preset-chips" style="margin-bottom: 10px;">
                    <span class="preset-chip price-chip" onclick="setPricePreset(0, 200000, this)">&lt; 200k</span>
                    <span class="preset-chip price-chip" onclick="setPricePreset(200000, 500000, this)">200k - 500k</span>
                    <span class="preset-chip price-chip" onclick="setPricePreset(500000, 1000000, this)">500k - 1tr</span>
                    <span class="preset-chip price-chip" onclick="setPricePreset(1000000, 3000000, this)">&gt; 1tr</span>
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
                <!-- Dòng 1: Ô nhập kích thước + chữ cm + icon thùng rác -->
                <div class="size-input-row">
                    <div class="size-input-wrapper">
                        <input type="text" id="filter-custom-size" class="price-input" placeholder="Nhập kích thước..." value="{{ request('size') }}" onkeydown="if(event.key==='Enter'){applyCustomSizeFilter();}">
                        <span class="size-unit-tag">cm</span>
                    </div>
                    <button type="button" class="btn-clear-trash" id="btn-clear-size" onclick="clearSizeFilter()" title="Xoá kích thước">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
                <!-- Dòng 2: Khung 25cm-40cm; 45cm-60cm -->
                <div class="size-preset-row">
                    <span class="preset-chip size-range-chip {{ request('size_range') == '25-40' ? 'active' : '' }}" data-range="25-40" onclick="setSizeRangePreset(25, 40, this)">25cm - 40cm</span>
                    <span class="preset-chip size-range-chip {{ request('size_range') == '45-60' ? 'active' : '' }}" data-range="45-60" onclick="setSizeRangePreset(45, 60, this)">45cm - 60cm</span>
                </div>
                <!-- Dòng 3: Khung 60cm-1m2; 1m2-1m8 -->
                <div class="size-preset-row" style="margin-bottom: 10px;">
                    <span class="preset-chip size-range-chip {{ request('size_range') == '60-120' ? 'active' : '' }}" data-range="60-120" onclick="setSizeRangePreset(60, 120, this)">60cm - 1m2</span>
                    <span class="preset-chip size-range-chip {{ request('size_range') == '120-180' ? 'active' : '' }}" data-range="120-180" onclick="setSizeRangePreset(120, 180, this)">1m2 - 1m8</span>
                </div>
                <!-- Dưới cùng: Nút áp dụng giống mục khoảng giá -->
                <button type="button" class="btn-honey-main" style="width: 100%; padding: 8px 14px; font-size: 12px; justify-content: center;" onclick="applyCustomSizeFilter()">
                    <i class="fa-solid fa-ruler-combined"></i> Áp Dụng Kích Thước
                </button>
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
            
            @if(isset($voucher) && $voucher)
                <div class="voucher-applied-banner" style="margin-bottom: 18px; background: linear-gradient(135deg, #FFFDF8 0%, #FFF7EC 100%); border: 1.5px dashed #E08A1E; border-radius: 14px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; box-shadow: 0 2px 8px rgba(224, 138, 30, 0.08);">
                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                        <div style="width: 42px; height: 42px; border-radius: 12px; background: {{ $voucher->voucher_type === 'SHIPPING' ? 'linear-gradient(135deg, #0D9488, #047857)' : 'linear-gradient(135deg, #E08A1E, #C2751D)' }}; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                            {{ $voucher->voucher_type === 'SHIPPING' ? '🚚' : '🏷️' }}
                        </div>
                        <div style="min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 3px;">
                                <span style="font-size: 13px; font-weight: 700; color: #2B1810;">
                                    Sản phẩm áp dụng mã:
                                </span>
                                <span style="font-family: monospace; font-weight: 800; font-size: 13.5px; background: #FFFFFF; padding: 2px 8px; border-radius: 6px; border: 1px solid #EBDDCD; color: #E08A1E; letter-spacing: 0.5px;">
                                    {{ $voucher->code }}
                                </span>
                                <span style="font-size: 11px; font-weight: 700; color: #FFFFFF; background: {{ $voucher->voucher_type === 'SHIPPING' ? '#0D9488' : '#E08A1E' }}; padding: 2px 8px; border-radius: 9999px;">
                                    @if(in_array($voucher->discount_type, ['PERCENT', 'PERCENTAGE']))
                                        Giảm {{ (int)$voucher->discount_value }}% {{ $voucher->max_discount_value > 0 ? '(Tối đa ' . number_format($voucher->max_discount_value, 0, ',', '.') . 'đ)' : '' }}
                                    @else
                                        Giảm {{ number_format($voucher->discount_value, 0, ',', '.') }}đ
                                    @endif
                                </span>
                            </div>
                            <div style="font-size: 12px; color: #7D6B5D; line-height: 1.4;">
                                Đơn tối thiểu: <strong>{{ number_format($voucher->min_order_value ?? 0, 0, ',', '.') }}đ</strong> • 
                                @if($voucher->apply_scope === 'CATEGORY')
                                    Chỉ áp dụng cho: <strong>{{ $voucher->categories->pluck('name')->join(', ') }}</strong>
                                @elseif($voucher->apply_scope === 'PRODUCT')
                                    Áp dụng cho các sản phẩm hợp lệ trong chương trình
                                @else
                                    Áp dụng cho toàn bộ sản phẩm trên cửa hàng
                                @endif
                                @if($voucher->start_date > now())
                                    • <span style="color: #0D9488; font-weight: 600;">Mở vào {{ $voucher->start_date->format('H:i d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('products.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #B91C1C; text-decoration: none; padding: 7px 12px; background: #FFFFFF; border-radius: 8px; border: 1px solid #FECACA; transition: all 0.2s;" onmouseover="this.style.background='#FEF2F2'" onmouseout="this.style.background='#FFFFFF'">
                        <i class="fa-solid fa-xmark"></i> Bỏ lọc voucher
                    </a>
                </div>
            @endif

            <!-- Toolbar (Image 2 style) -->
            <div class="catalog-toolbar">
                <div class="toolbar-results-info" id="catalog-results-info">
                    Hiển thị tất cả kết quả...
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <label for="catalog-sort-select" style="font-size: 13px; font-weight: 700; color: var(--text-muted);">Sắp xếp theo:</label>
                    <div class="select-sort-wrapper">
                        <select id="catalog-sort-select" class="select-sort-control" onchange="applyFilters()">
                            @php
                                $defaultSort = request('category_id') ? 'best_seller' : 'latest';
                                $currentSort = request('sort', $defaultSort);
                            @endphp
                            <option value="best_seller" {{ $currentSort == 'best_seller' ? 'selected' : '' }}>Bán chạy nhất</option>
                            <option value="latest" {{ $currentSort == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="price_asc" {{ $currentSort == 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                            <option value="price_desc" {{ $currentSort == 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                        </select>
                        <i class="fa-solid fa-chevron-down select-sort-icon"></i>
                    </div>
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
    function scrollToCatalogSection(smooth = true) {
        const target = document.getElementById('catalog-layout');
        if (!target) return;
        const header = document.querySelector('header.site-header');
        const headerHeight = header ? header.getBoundingClientRect().height : 125;
        const targetY = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - 15;
        window.scrollTo({
            top: Math.max(0, targetY),
            behavior: smooth ? 'smooth' : 'auto'
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadCatalogProducts();

        // Tự động lướt xuống danh sách sản phẩm khi có hash hoặc tham số lọc
        if (window.location.hash === '#catalog-layout' || window.location.hash.includes('catalog') || window.location.search.includes('category_id') || window.location.search.includes('size_range') || window.location.search.includes('size=') || window.location.search.includes('scroll=')) {
            setTimeout(() => {
                scrollToCatalogSection(true);
            }, 150);
        }
    });

    window.addEventListener('hashchange', () => {
        if (window.location.hash === '#catalog-layout' || window.location.hash.includes('catalog')) {
            scrollToCatalogSection(true);
        }
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

    let selectedSize = "{{ request('size') }}";
    let selectedSizeRange = "{{ request('size_range') }}";
    let selectedColor = "{{ request('color') }}";
    let currentPage = 1;

    function formatShopPrice(input) {
        if (!input || !input.value.trim()) return;
        const digits = String(input.value).replace(/\D/g, '');
        input.value = digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
    }

    function parseShopPrice(val) {
        if (!val) return '';
        const digits = String(val).replace(/\D/g, '');
        return digits || '';
    }

    function setPricePreset(min, max, el) {
        const minDigits = String(min).replace(/\D/g, '');
        const maxDigits = String(max).replace(/\D/g, '');
        document.getElementById('filter-min-price').value = minDigits ? minDigits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        document.getElementById('filter-max-price').value = maxDigits ? maxDigits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        
        // Cập nhật trạng thái active cho chip giá
        document.querySelectorAll('.price-chip').forEach(c => c.classList.remove('active'));
        if (el) el.classList.add('active');

        applyFilters();
    }

    function clearPriceFilter() {
        const minInput = document.getElementById('filter-min-price');
        const maxInput = document.getElementById('filter-max-price');
        if (minInput) minInput.value = '';
        if (maxInput) maxInput.value = '';
        document.querySelectorAll('.price-chip').forEach(c => c.classList.remove('active'));
        applyFilters();
    }

    function setSizeRangePreset(min, max, el) {
        const rangeVal = `${min}-${max}`;
        if (selectedSizeRange === rangeVal) {
            // Click lại chính nó thì bỏ chọn
            selectedSizeRange = '';
            el.classList.remove('active');
        } else {
            selectedSizeRange = rangeVal;
            document.querySelectorAll('.size-range-chip').forEach(c => c.classList.remove('active'));
            if (el) el.classList.add('active');

            // Xóa ô nhập tay khi chọn khung định sẵn
            const customInput = document.getElementById('filter-custom-size');
            if (customInput) customInput.value = '';
            selectedSize = '';
        }
        applyFilters();
    }

    function applyCustomSizeFilter() {
        const customInput = document.getElementById('filter-custom-size');
        const val = customInput ? customInput.value.trim() : '';
        selectedSize = val;
        selectedSizeRange = '';
        document.querySelectorAll('.size-range-chip').forEach(c => c.classList.remove('active'));
        applyFilters();
    }

    function clearSizeFilter() {
        const customInput = document.getElementById('filter-custom-size');
        if (customInput) customInput.value = '';
        selectedSize = '';
        selectedSizeRange = '';
        document.querySelectorAll('.size-range-chip').forEach(c => c.classList.remove('active'));
        applyFilters();
    }

    function resetAllFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('voucher')) {
            window.location.href = "{{ route('products.index') }}";
            return;
        }
        document.querySelectorAll('input[name="cat_filter"]').forEach(r => r.checked = (r.value === ''));
        document.getElementById('filter-min-price').value = '';
        document.getElementById('filter-max-price').value = '';
        document.querySelectorAll('.price-chip').forEach(c => c.classList.remove('active'));
        
        const customInput = document.getElementById('filter-custom-size');
        if (customInput) customInput.value = '';
        selectedSize = '';
        selectedSizeRange = '';
        document.querySelectorAll('.size-range-chip').forEach(c => c.classList.remove('active'));

        document.getElementById('filter-in-stock').checked = false;
        selectedColor = '';
        document.getElementById('catalog-sort-select').value = 'latest';
        currentPage = 1;
        loadCatalogProducts();
    }

    function onCategoryFilterChange(radio) {
        // Tự động chuyển sắp xếp sang "Bán chạy nhất" khi click vào bất kỳ danh mục nào
        const sortSelect = document.getElementById('catalog-sort-select');
        if (sortSelect) {
            sortSelect.value = 'best_seller';
        }
        applyFilters(1);
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
        const categoryId = selectedCat ? selectedCat.value : '';
        const minPrice = parseShopPrice(document.getElementById('filter-min-price').value);
        const maxPrice = parseShopPrice(document.getElementById('filter-max-price').value);
        const inStock = document.getElementById('filter-in-stock').checked;
        const sort = document.getElementById('catalog-sort-select').value;
        const urlParams = new URLSearchParams(window.location.search);
        const search = urlParams.get('search') || '';
        const voucher = urlParams.get('voucher') || '';

        const params = new URLSearchParams({
            page: currentPage,
            per_page: 9,
            sort: sort
        });

        if (search) params.append('search', search);
        if (voucher) params.append('voucher', voucher);
        if (categoryId) params.append('category_id', categoryId);
        if (minPrice) params.append('min_price', minPrice);
        if (maxPrice) params.append('max_price', maxPrice);
        if (selectedSize) params.append('size', selectedSize);
        if (selectedSizeRange) params.append('size_range', selectedSizeRange);
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
                const urlParams = new URLSearchParams(window.location.search);
                const voucherCode = urlParams.get('voucher');
                if (voucherCode) {
                    info.innerHTML = `Tìm thấy <strong>${meta.total}</strong> sản phẩm áp dụng mã <strong style="color: #E08A1E;">${voucherCode}</strong> (Trang ${meta.current_page}/${meta.last_page})`;
                } else {
                    info.innerHTML = `Hiển thị tất cả <strong>${meta.total}</strong> kết quả (Trang ${meta.current_page}/${meta.last_page})`;
                }
            }

            grid.innerHTML = products.map(p => {
                const primaryImg = (p.images && p.images.find(img => img && img.is_primary)) || (p.images && p.images[0]) || null;
                const imgUrl = (primaryImg && primaryImg.image_url) ? primaryImg.image_url : 'https://placehold.co/600x600/f5e6ca/7c4a2d?text=' + encodeURIComponent(p.name || 'Gau Bong');
                const price = Number(p.lowest_price !== undefined ? p.lowest_price : (p.price || 0));
                const salePrice = (p.lowest_sale_price !== undefined && p.lowest_sale_price !== null) 
                    ? Number(p.lowest_sale_price) 
                    : (p.sale_price ? Number(p.sale_price) : null);
                const isOnSale = (salePrice !== null && salePrice > 0 && salePrice < price);
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
                                    <div class="product-card-meta">
                                        <span class="rating-badge-pill" title="Đánh giá ${(parseFloat(p.avg_rating) || 5.0).toFixed(1)} sao">
                                            <i class="fa-solid fa-star"></i> ${(parseFloat(p.avg_rating) || 5.0).toFixed(1)}
                                        </span>
                                        <span class="sold-count-text">Đã bán ${p.sold_count || 0}</span>
                                    </div>
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
                if (typeof syncAllHeartIcons === 'function') {
                    syncAllHeartIcons();
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
