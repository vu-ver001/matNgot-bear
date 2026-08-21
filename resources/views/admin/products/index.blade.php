@extends('layouts.admin-dashboard')

@php $currentPage = 'products'; @endphp

@section('page-title', 'Quản Lý Sản Phẩm')

@section('content')
<style>
    /* Quick Stat Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: var(--shadow-subtle);
        transition: all 0.25s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-card);
        border-color: #D7CCC8;
    }

    .stat-info .stat-label {
        font-size: 12.5px;
        color: var(--text-muted);
        font-weight: 700;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-info .stat-value {
        font-family: 'Montserrat', sans-serif;
        font-size: 26px;
        font-weight: 800;
        color: var(--text-main);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .stat-icon.brown { background: #EFEBE9; color: #5D4037; }
    .stat-icon.honey { background: var(--honey-light); color: var(--honey-dark); }
    .stat-icon.green { background: #E8F5E9; color: #2E7D32; }
    .stat-icon.red   { background: #FFEBEE; color: #C62828; }

    /* Panel Card */
    .panel-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 1.75rem;
        box-shadow: var(--shadow-card);
    }

    .panel-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--border-light);
    }

    .panel-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 19px;
        font-weight: 800;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Toolbar & Filters */
    .toolbar-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 260px;
    }

    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        font-size: 14px;
    }

    .input-control, .select-control {
        width: 100%;
        background: var(--bg-page);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 10px 14px;
        font-size: 13.5px;
        color: var(--text-main);
        outline: none;
        transition: all 0.2s ease;
    }

    .search-box .input-control {
        padding-left: 38px;
    }

    .input-control:focus, .select-control:focus {
        border-color: #8D6E63;
        background: #FFFFFF;
        box-shadow: 0 0 0 3px rgba(141, 110, 99, 0.12);
    }

    .filter-select {
        min-width: 160px;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-size: 13.5px;
        font-weight: 700;
        border-radius: var(--radius-md);
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #8D6E63 0%, #5D4037 100%);
        color: #FFFFFF;
        box-shadow: 0 4px 12px rgba(109, 76, 65, 0.2);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(109, 76, 65, 0.3);
        background: linear-gradient(135deg, #795548 0%, #4E342E 100%);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text-main);
    }

    .btn-outline:hover {
        background: var(--bg-surface);
        border-color: #D7CCC8;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        background: var(--bg-surface);
        color: var(--text-main);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-icon:hover {
        background: var(--bg-card);
        border-color: #D7CCC8;
        transform: scale(1.05);
    }

    .btn-icon.delete:hover {
        background: #FFEBEE;
        border-color: #EF9A9A;
        color: var(--danger);
    }

    .btn-icon.edit:hover {
        background: var(--honey-light);
        border-color: var(--honey);
        color: var(--honey-dark);
    }

    /* Data Table */
    .table-container {
        width: 100%;
        overflow-x: auto;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        background: var(--bg-card);
    }

    table.data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13.5px;
    }

    table.data-table th {
        background: #FDFBF7;
        color: var(--text-muted);
        font-weight: 700;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
        text-transform: uppercase;
        font-size: 11.5px;
        letter-spacing: 0.5px;
    }

    table.data-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-main);
        vertical-align: middle;
    }

    table.data-table tbody tr:last-child td {
        border-bottom: none;
    }

    table.data-table tbody tr:hover {
        background: #FAF6F0;
    }

    .product-thumb {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        object-fit: cover;
        border: 1px solid var(--border);
        background: var(--bg-surface);
    }

    .product-name-cell {
        font-weight: 700;
        color: var(--text-main);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
    }

    .badge-status.active { background: #E8F5E9; color: #2E7D32; }
    .badge-status.inactive { background: #FFEBEE; color: #C62828; }

    .price-sale { color: #D32F2F; font-weight: 700; }
    .price-original.crossed { text-decoration: line-through; color: var(--text-light); font-size: 12px; margin-left: 4px; }

    /* Pagination */
    .pagination-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .pagination-info { font-size: 13px; color: var(--text-muted); font-weight: 600; }
    .pagination-controls { display: flex; align-items: center; gap: 6px; }

    .page-btn {
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        border: 1px solid var(--border);
        background: var(--bg-card);
        color: var(--text-main);
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
    }

    .page-btn:hover:not(:disabled) {
        background: var(--honey-light);
        border-color: var(--honey);
        color: var(--honey-dark);
    }

    .page-btn.active {
        background: #5D4037;
        color: #FFFFFF;
        border-color: #5D4037;
    }

    .page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

    /* Modal Styles */
    .modal-backdrop {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 1.5rem;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }

    .modal-backdrop.show { opacity: 1; pointer-events: auto; }

    .modal-box {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        width: 100%;
        max-width: 680px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        transform: translateY(20px) scale(0.98);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }

    .modal-backdrop.show .modal-box { transform: translateY(0) scale(1); }

    .modal-header {
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-surface);
    }

    .modal-title { font-size: 18px; font-weight: 800; color: var(--text-main); }
    .modal-body { padding: 1.5rem 1.75rem; overflow-y: auto; flex: 1; }
    .modal-footer {
        padding: 1.25rem 1.75rem;
        border-top: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        background: var(--bg-surface);
    }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 6px; }
    .form-label span.req { color: var(--danger); }
    textarea.input-control { resize: vertical; min-height: 80px; }

    .gallery-section {
        background: var(--bg-surface);
        border: 1px dashed var(--border);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        margin-top: 0.5rem;
    }

    .gallery-input-row { display: flex; gap: 8px; margin-bottom: 1rem; }
    .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; }

    .gallery-item-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        box-shadow: var(--shadow-subtle);
    }

    .gallery-item-card.is-primary { border: 2px solid var(--honey); background: var(--honey-light); }
    .gallery-item-thumb { width: 100%; height: 90px; object-fit: cover; border-radius: var(--radius-sm); margin-bottom: 8px; }
    .gallery-item-actions { display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 4px; }
    .primary-badge-btn { font-size: 11px; font-weight: 700; padding: 2px 6px; border-radius: 4px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-muted); cursor: pointer; }
    .gallery-item-card.is-primary .primary-badge-btn { background: var(--honey); color: #FFFFFF; border-color: var(--honey); }
</style>

<!-- 1. Stats Summary -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Tổng Sản Phẩm</div>
            <div class="stat-value" id="stat-total-products">--</div>
        </div>
        <div class="stat-icon brown"><i class="fa-solid fa-boxes-stacked"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đang Kinh Doanh</div>
            <div class="stat-value" id="stat-active-products">--</div>
        </div>
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Ngừng Kinh Doanh</div>
            <div class="stat-value" id="stat-inactive-products">--</div>
        </div>
        <div class="stat-icon honey"><i class="fa-solid fa-pause"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Cảnh Báo Tồn Kho (&le; 5)</div>
            <div class="stat-value" id="stat-low-stock">--</div>
        </div>
        <div class="stat-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
    </div>
</div>

<!-- 2. Products Panel -->
<div class="panel-card">
    <div class="panel-header">
        <div class="panel-title">
            <i class="fa-solid fa-box-open" style="color: #8D6E63;"></i>
            Danh Sách Sản Phẩm Gấu Bông
        </div>
        <button type="button" class="btn btn-primary" onclick="openProductModal()">
            <i class="fa-solid fa-plus"></i> Thêm Sản Phẩm Mới
        </button>
    </div>

    <!-- Toolbar & Filters -->
    <div class="toolbar-grid">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="prod-search" class="input-control" placeholder="Tìm kiếm tên sản phẩm, mã..." oninput="debounceSearchProduct()">
        </div>
        <div class="filter-select">
            <select id="prod-cat-filter" class="select-control" onchange="loadProducts(1)">
                <option value="">Tất cả danh mục</option>
            </select>
        </div>
        <div class="filter-select">
            <select id="prod-status-filter" class="select-control" onchange="loadProducts(1)">
                <option value="">Tất cả trạng thái</option>
                <option value="ACTIVE">Đang kinh doanh</option>
                <option value="INACTIVE">Ngừng kinh doanh</option>
            </select>
        </div>
        <div class="filter-select">
            <select id="prod-sort-filter" class="select-control" onchange="loadProducts(1)">
                <option value="latest">Mới nhất</option>
                <option value="price_asc">Giá tăng dần</option>
                <option value="price_desc">Giá giảm dần</option>
                <option value="stock_asc">Tồn kho ít nhất</option>
                <option value="best_seller">Bán chạy nhất</option>
            </select>
        </div>
        <button type="button" class="btn btn-outline btn-icon" onclick="resetProductFilters()" title="Làm mới bộ lọc">
            <i class="fa-solid fa-rotate-left"></i>
        </button>
    </div>

    <!-- Data Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Ảnh</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Danh Mục</th>
                    <th>Giá Bán</th>
                    <th>Size / Màu</th>
                    <th>Tồn Kho</th>
                    <th>Trạng Thái</th>
                    <th style="text-align: right; width: 100px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody id="products-table-body">
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem;">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: var(--honey);"></i>
                        <p style="margin-top: 8px; color: var(--text-light);">Đang tải danh sách sản phẩm...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" id="products-pagination">
        <!-- Rendered via JS -->
    </div>
</div>

<!-- 3. Modal Thêm / Sửa Sản Phẩm -->
<div class="modal-backdrop" id="product-modal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title" id="product-modal-title">Thêm Sản Phẩm Mới</h3>
            <button type="button" class="btn-icon" onclick="closeProductModal()" style="border: none; background: transparent;">
                <i class="fa-solid fa-xmark" style="font-size: 18px;"></i>
            </button>
        </div>
        <form id="product-form" onsubmit="saveProduct(event)">
            <div class="modal-body">
                <input type="hidden" id="prod-id">

                <div class="form-group">
                    <label class="form-label">Tên Sản Phẩm <span class="req">*</span></label>
                    <input type="text" id="prod-name" class="input-control" required placeholder="Ví dụ: Gấu Teddy Nơ Hồng 1m2...">
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Danh Mục <span class="req">*</span></label>
                        <select id="prod-modal-cat" class="select-control" required>
                            <option value="">Chọn danh mục</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trạng Thái</label>
                        <select id="prod-modal-status" class="select-control">
                            <option value="ACTIVE">Đang kinh doanh</option>
                            <option value="INACTIVE">Ngừng kinh doanh</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Giá Gốc (VNĐ) <span class="req">*</span></label>
                        <input type="number" id="prod-price" class="input-control" required min="0" step="1000" placeholder="189000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giá Khuyến Mãi (VNĐ)</label>
                        <input type="number" id="prod-sale-price" class="input-control" min="0" step="1000" placeholder="149000">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Kích Thước (Size)</label>
                        <input type="text" id="prod-size" class="input-control" placeholder="Ví dụ: 30cm, 1m2, 1m8...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Màu Sắc</label>
                        <input type="text" id="prod-color" class="input-control" placeholder="Ví dụ: Nâu socola, Vàng kem...">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Chất Liệu</label>
                        <input type="text" id="prod-material" class="input-control" placeholder="Ví dụ: Bông PP 3D, Vải nhung...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Số Lượng Tồn Kho <span class="req">*</span></label>
                        <input type="number" id="prod-stock" class="input-control" required min="0" value="10">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mô Tả Sản Phẩm</label>
                    <textarea id="prod-desc" class="input-control" placeholder="Mô tả chi tiết về sản phẩm gấu bông..."></textarea>
                </div>

                <!-- Gallery Manager (Khi Edit) -->
                <div class="gallery-section" id="prod-gallery-wrapper" style="display: none;">
                    <label class="form-label" style="margin-bottom: 8px;">
                        <i class="fa-solid fa-images" style="color: var(--honey-dark);"></i> Thư Viện Ảnh Sản Phẩm
                    </label>
                    <div class="gallery-input-row">
                        <input type="url" id="prod-new-img-url" class="input-control" placeholder="Dán link URL ảnh (https://...)">
                        <button type="button" class="btn btn-primary" style="padding: 8px 16px; white-space: nowrap;" onclick="addProductImage()">
                            <i class="fa-solid fa-plus"></i> Thêm Ảnh
                        </button>
                    </div>
                    <div class="gallery-grid" id="prod-gallery-grid">
                        <!-- Loaded dynamically -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeProductModal()">Hủy</button>
                <button type="submit" class="btn btn-primary" id="btn-save-prod">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu Sản Phẩm
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let searchTimeout = null;
    let categoriesList = [];

    document.addEventListener('DOMContentLoaded', () => {
        loadCategoriesSelect();
        loadProducts(1);
    });

    async function loadCategoriesSelect() {
        try {
            const res = await fetch('/api/categories');
            const data = await res.json();
            if (data.success) {
                categoriesList = data.data;
                const filterSelect = document.getElementById('prod-cat-filter');
                const modalSelect = document.getElementById('prod-modal-cat');
                
                let filterHtml = '<option value="">Tất cả danh mục</option>';
                let modalHtml = '<option value="">Chọn danh mục</option>';
                
                categoriesList.forEach(c => {
                    filterHtml += `<option value="${c.id}">${c.name}</option>`;
                    modalHtml += `<option value="${c.id}">${c.name}</option>`;
                });
                
                filterSelect.innerHTML = filterHtml;
                modalSelect.innerHTML = modalHtml;
            }
        } catch (e) {
            console.error("Lỗi nạp danh mục:", e);
        }
    }

    async function loadProducts(page = 1) {
        const tbody = document.getElementById('products-table-body');
        const search = document.getElementById('prod-search').value;
        const categoryId = document.getElementById('prod-cat-filter').value;
        const status = document.getElementById('prod-status-filter').value;
        const sort = document.getElementById('prod-sort-filter').value;

        const params = new URLSearchParams({ page, per_page: 8, sort });
        if (search) params.append('search', search);
        if (categoryId) params.append('category_id', categoryId);
        if (status) params.append('status', status);

        try {
            const res = await fetch(`/api/admin/products?${params.toString()}`);
            const data = await res.json();

            if (data.success) {
                const items = Array.isArray(data.data) ? data.data : (data.data?.data || []);
                const meta = data.meta || { current_page: 1, last_page: 1, total: items.length };
                renderProductsTable(items, meta);
                updateProductStats(meta.total);
            } else {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--danger);padding:2rem;">${data.message || 'Không thể tải dữ liệu.'}</td></tr>`;
            }
        } catch (e) {
            console.error("Lỗi loadProducts:", e);
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--danger);padding:2rem;">Lỗi tải dữ liệu. Vui lòng thử lại!</td></tr>`;
        }
    }

    function renderProductsTable(products, meta) {
        const tbody = document.getElementById('products-table-body');
        if (!products || products.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-light);">Không tìm thấy sản phẩm nào phù hợp.</td></tr>`;
            document.getElementById('products-pagination').innerHTML = '';
            return;
        }

        tbody.innerHTML = products.map(p => {
            const primaryImg = (p.images && p.images.find(img => img.is_primary)) || (p.images && p.images[0]) || { image_url: 'https://placehold.co/100x100/f5e6ca/7c4a2d?text=Gau+Bong' };
            const hasSale = p.sale_price && Number(p.sale_price) < Number(p.price);

            return `
                <tr>
                    <td>
                        <img src="${primaryImg.image_url}" class="product-thumb" alt="${p.name}" onerror="this.src='https://placehold.co/100x100/f5e6ca/7c4a2d?text=Gau'">
                    </td>
                    <td>
                        <div class="product-name-cell" title="${p.name}">${p.name}</div>
                        <div style="font-size: 11px; color: var(--text-light); margin-top: 2px;">ID: #${p.id}</div>
                    </td>
                    <td>
                        <span style="font-weight: 700; color: #8D6E63;">${p.category ? p.category.name : 'Chưa phân loại'}</span>
                    </td>
                    <td>
                        ${hasSale 
                            ? `<span class="price-sale">${Number(p.sale_price).toLocaleString('vi-VN')} đ</span><span class="price-original crossed">${Number(p.price).toLocaleString('vi-VN')} đ</span>`
                            : `<span style="font-weight: 700;">${Number(p.price).toLocaleString('vi-VN')} đ</span>`
                        }
                    </td>
                    <td>
                        <div>${p.size || '--'}</div>
                        <div style="font-size: 11px; color: var(--text-light);">${p.color || '--'}</div>
                    </td>
                    <td>
                        <span style="font-weight: 800; ${p.stock_quantity <= 5 ? 'color: var(--danger);' : ''}">${p.stock_quantity}</span>
                    </td>
                    <td>
                        <span class="badge-status ${p.status === 'ACTIVE' ? 'active' : 'inactive'}">
                            <i class="fa-solid fa-circle" style="font-size: 7px;"></i>
                            ${p.status === 'ACTIVE' ? 'Đang bán' : 'Ngừng bán'}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 4px;">
                            <button type="button" class="btn-icon edit" onclick="editProduct(${p.id})" title="Chỉnh sửa sản phẩm">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button type="button" class="btn-icon delete" onclick="deleteProduct(${p.id})" title="Ngừng kinh doanh / Xóa">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        renderPagination(meta, 'products-pagination', 'loadProducts');
    }

    function updateProductStats(total) {
        if (typeof total !== 'undefined') {
            document.getElementById('stat-total-products').innerText = total;
        }
        fetch('/api/admin/products?status=ACTIVE&per_page=1')
            .then(r => r.json())
            .then(d => { if (d.success && d.meta) document.getElementById('stat-active-products').innerText = d.meta.total; })
            .catch(() => {});
        fetch('/api/admin/products?status=INACTIVE&per_page=1')
            .then(r => r.json())
            .then(d => { if (d.success && d.meta) document.getElementById('stat-inactive-products').innerText = d.meta.total; })
            .catch(() => {});
        fetch('/api/admin/products?per_page=100')
            .then(r => r.json())
            .then(d => {
                if (d.success && d.data) {
                    const items = Array.isArray(d.data) ? d.data : (d.data.data || []);
                    const lowStockCount = items.filter(p => p.stock_quantity <= 5).length;
                    document.getElementById('stat-low-stock').innerText = lowStockCount;
                }
            })
            .catch(() => {});
    }

    function debounceSearchProduct() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadProducts(1), 350);
    }

    function resetProductFilters() {
        document.getElementById('prod-search').value = '';
        document.getElementById('prod-cat-filter').value = '';
        document.getElementById('prod-status-filter').value = '';
        document.getElementById('prod-sort-filter').value = 'latest';
        loadProducts(1);
    }

    function openProductModal(prod = null) {
        document.getElementById('product-modal').classList.add('show');
        if (prod) {
            document.getElementById('product-modal-title').innerText = 'Chỉnh Sửa Sản Phẩm #' + prod.id;
            document.getElementById('prod-id').value = prod.id;
            document.getElementById('prod-name').value = prod.name;
            document.getElementById('prod-modal-cat').value = prod.category_id;
            document.getElementById('prod-modal-status').value = prod.status;
            document.getElementById('prod-price').value = prod.price;
            document.getElementById('prod-sale-price').value = prod.sale_price || '';
            document.getElementById('prod-size').value = prod.size || '';
            document.getElementById('prod-color').value = prod.color || '';
            document.getElementById('prod-material').value = prod.material || '';
            document.getElementById('prod-stock').value = prod.stock_quantity;
            document.getElementById('prod-desc').value = prod.description || '';
            document.getElementById('prod-gallery-wrapper').style.display = 'block';
            loadProductGallery(prod.id);
        } else {
            document.getElementById('product-modal-title').innerText = 'Thêm Sản Phẩm Mới';
            document.getElementById('product-form').reset();
            document.getElementById('prod-id').value = '';
            document.getElementById('prod-gallery-wrapper').style.display = 'none';
        }
    }

    function closeProductModal() {
        document.getElementById('product-modal').classList.remove('show');
    }

    async function editProduct(id) {
        try {
            const res = await fetch(`/api/products/${id}`);
            const data = await res.json();
            if (data.success) openProductModal(data.data);
        } catch (e) {
            Swal.fire('Lỗi', 'Không thể lấy thông tin sản phẩm', 'error');
        }
    }

    async function saveProduct(e) {
        e.preventDefault();
        const id = document.getElementById('prod-id').value;
        const body = {
            category_id: document.getElementById('prod-modal-cat').value,
            name: document.getElementById('prod-name').value,
            description: document.getElementById('prod-desc').value,
            price: document.getElementById('prod-price').value,
            sale_price: document.getElementById('prod-sale-price').value || null,
            size: document.getElementById('prod-size').value,
            color: document.getElementById('prod-color').value,
            material: document.getElementById('prod-material').value,
            stock_quantity: document.getElementById('prod-stock').value,
            status: document.getElementById('prod-modal-status').value,
        };

        const url = id ? `/api/admin/products/${id}` : '/api/admin/products';
        const method = id ? 'PUT' : 'POST';

        try {
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Thành công', text: data.message, timer: 1500, showConfirmButton: false });
                closeProductModal();
                loadProducts(1);
            } else {
                Swal.fire('Lỗi', data.message || 'Không thể lưu sản phẩm', 'error');
            }
        } catch (err) {
            Swal.fire('Lỗi', 'Có lỗi xảy ra khi kết nối máy chủ', 'error');
        }
    }

    async function deleteProduct(id) {
        const result = await Swal.fire({
            title: 'Ngừng kinh doanh sản phẩm?',
            text: 'Theo đặc tả nghiệp vụ, sản phẩm sẽ được chuyển sang trạng thái "Ngừng kinh doanh" an toàn.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#C62828',
            cancelButtonColor: '#795548',
            confirmButtonText: 'Đồng ý ngừng bán',
            cancelButtonText: 'Hủy bỏ'
        });

        if (result.isConfirmed) {
            try {
                const res = await fetch(`/api/admin/products/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Đã cập nhật', text: data.message, timer: 1500, showConfirmButton: false });
                    loadProducts(1);
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            } catch (err) {
                Swal.fire('Lỗi', 'Không thể thực hiện yêu cầu', 'error');
            }
        }
    }

    async function loadProductGallery(productId) {
        const grid = document.getElementById('prod-gallery-grid');
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;">Đang tải ảnh...</div>';
        try {
            const res = await fetch(`/api/products/${productId}`);
            const data = await res.json();
            if (data.success && data.data.images) {
                grid.innerHTML = data.data.images.map(img => `
                    <div class="gallery-item-card ${img.is_primary ? 'is-primary' : ''}">
                        <img src="${img.image_url}" class="gallery-item-thumb" onerror="this.src='https://placehold.co/100x100'">
                        <div class="gallery-item-actions">
                            <button type="button" class="primary-badge-btn" onclick="setPrimaryImage(${productId}, ${img.id})">
                                ${img.is_primary ? '<i class=\"fa-solid fa-star\"></i> Chính' : 'Đặt làm chính'}
                            </button>
                            <button type="button" class="btn-icon delete" style="width:24px;height:24px;font-size:11px;" onclick="deleteImage(${productId}, ${img.id})">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }
        } catch (e) {
            grid.innerHTML = '<div style="grid-column:1/-1;color:var(--danger);">Lỗi tải ảnh.</div>';
        }
    }

    async function addProductImage() {
        const productId = document.getElementById('prod-id').value;
        const imgUrl = document.getElementById('prod-new-img-url').value.trim();
        if (!imgUrl) {
            Swal.fire('Chú ý', 'Vui lòng dán link ảnh', 'warning');
            return;
        }

        try {
            const res = await fetch(`/api/admin/products/${productId}/images`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ image_url: imgUrl })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('prod-new-img-url').value = '';
                loadProductGallery(productId);
            }
        } catch (e) {
            Swal.fire('Lỗi', 'Không thể thêm ảnh', 'error');
        }
    }

    async function setPrimaryImage(productId, imageId) {
        try {
            await fetch(`/api/admin/products/${productId}/images/${imageId}/primary`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            loadProductGallery(productId);
            loadProducts(1);
        } catch (e) {
            Swal.fire('Lỗi', 'Không thể đổi ảnh chính', 'error');
        }
    }

    async function deleteImage(productId, imageId) {
        try {
            await fetch(`/api/admin/products/${productId}/images/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            loadProductGallery(productId);
            loadProducts(1);
        } catch (e) {
            Swal.fire('Lỗi', 'Không thể xóa ảnh', 'error');
        }
    }

    function renderPagination(meta, containerId, funcName) {
        const wrap = document.getElementById(containerId);
        if (!meta || meta.last_page <= 1) {
            wrap.innerHTML = `<div class="pagination-info">Hiển thị <strong>${meta.total}</strong> kết quả</div>`;
            return;
        }

        let html = `<div class="pagination-info">Trang <strong>${meta.current_page}</strong> / <strong>${meta.last_page}</strong> (Tổng <strong>${meta.total}</strong> mục)</div>`;
        html += '<div class="pagination-controls">';
        html += `<button class="page-btn" ${meta.current_page === 1 ? 'disabled' : ''} onclick="${funcName}(${meta.current_page - 1})"><i class="fa-solid fa-chevron-left"></i></button>`;

        for (let i = 1; i <= meta.last_page; i++) {
            if (i === 1 || i === meta.last_page || (i >= meta.current_page - 1 && i <= meta.current_page + 1)) {
                html += `<button class="page-btn ${i === meta.current_page ? 'active' : ''}" onclick="${funcName}(${i})">${i}</button>`;
            } else if (i === meta.current_page - 2 || i === meta.current_page + 2) {
                html += `<span style="padding: 0 4px; color: var(--text-light);">...</span>`;
            }
        }

        html += `<button class="page-btn" ${meta.current_page === meta.last_page ? 'disabled' : ''} onclick="${funcName}(${meta.current_page + 1})"><i class="fa-solid fa-chevron-right"></i></button>`;
        html += '</div>';
        wrap.innerHTML = html;
    }
</script>
@endsection
