@extends('layouts.admin-dashboard')

@php $currentPage = 'categories'; @endphp

@section('page-title', 'Quản Lý Danh Mục')

@section('content')
<style>
    /* Category Slider Carousel (5 ô trên 1 hàng + nút < >) */
    .category-slider-wrapper {
        position: relative;
        margin-bottom: 2rem;
    }

    .category-slider-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .category-slider-title {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .category-slider-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .slider-btn-nav {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1px solid var(--border);
        background: #FFFFFF;
        color: var(--text-main);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: var(--shadow-subtle);
        font-size: 13px;
    }

    .slider-btn-nav:hover {
        background: var(--honey-light);
        border-color: var(--honey);
        color: var(--honey-dark);
        transform: scale(1.08);
    }

    .category-slider-track {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        scroll-behavior: smooth;
        padding: 4px 2px 14px 2px;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .category-slider-track::-webkit-scrollbar {
        display: none;
    }

    /* Hiển thị chính xác 5 ô danh mục vừa vặn trên 1 hàng */
    .category-card {
        flex: 0 0 calc((100% - 4rem) / 5);
        min-width: calc((100% - 4rem) / 5);
        max-width: calc((100% - 4rem) / 5);
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.15rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 12px;
        transition: all 0.25s ease;
        box-shadow: var(--shadow-subtle);
        user-select: none;
    }

    @media (max-width: 1300px) {
        .category-card {
            flex: 0 0 calc((100% - 3rem) / 4);
            min-width: calc((100% - 3rem) / 4);
            max-width: calc((100% - 3rem) / 4);
        }
    }

    @media (max-width: 992px) {
        .category-card {
            flex: 0 0 calc((100% - 2rem) / 3);
            min-width: calc((100% - 2rem) / 3);
            max-width: calc((100% - 2rem) / 3);
        }
    }

    @media (max-width: 640px) {
        .category-card {
            flex: 0 0 calc((100% - 1rem) / 2);
            min-width: calc((100% - 1rem) / 2);
            max-width: calc((100% - 1rem) / 2);
        }
    }

    .category-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-card);
        border-color: #D7CCC8;
    }

    .category-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .category-icon {
        width: 38px;
        height: 38px;
        border-radius: var(--radius-md);
        background: var(--honey-light);
        color: var(--honey-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .category-name {
        font-family: 'Montserrat', sans-serif;
        font-size: 14px;
        font-weight: 800;
        color: var(--text-main);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

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

    /* Pin to header button style */
    .btn-icon.pin-btn {
        color: #BCAAA4;
        transition: all 0.2s ease;
        position: relative;
    }

    .btn-icon.pin-btn:hover {
        background: var(--honey-light);
        border-color: var(--honey);
        color: var(--honey-dark);
    }

    .btn-icon.pin-btn.is-pinned {
        background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%);
        border-color: var(--honey);
        color: #4E342E;
        box-shadow: 0 2px 8px rgba(229, 152, 25, 0.35);
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

    /* Modal */
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
        max-width: 580px;
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

    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 6px; }
    .form-label span.req { color: var(--danger); }
    textarea.input-control { resize: vertical; min-height: 80px; }
</style>

<!-- 1. Category Slider Carousel (5 ô trên 1 hàng + nút < >) -->
<div class="category-slider-wrapper">
    <div class="category-slider-header">
        <div class="category-slider-title">
            <i class="fa-solid fa-layer-group" style="color: #8D6E63;"></i>
            Xem Nhanh Các Danh Mục
        </div>
        <div class="category-slider-controls">
            <button type="button" class="slider-btn-nav" onclick="slideCategories(-1)" title="Chuyển sang trái">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" class="slider-btn-nav" onclick="slideCategories(1)" title="Chuyển sang phải">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>
    <div class="category-slider-track" id="category-cards-slider">
        <!-- Rendered via JS -->
    </div>
</div>

<!-- 2. Category Detail Panel -->
<div class="panel-card">
    <div class="panel-header">
        <div class="panel-title">
            <i class="fa-solid fa-folder-tree" style="color: #8D6E63;"></i>
            Bảng Chi Tiết Danh Mục Gấu Bông
        </div>
        <button type="button" class="btn btn-primary" onclick="openCategoryModal()">
            <i class="fa-solid fa-plus"></i> Thêm Danh Mục Mới
        </button>
    </div>

    <!-- Toolbar & Filters -->
    <div class="toolbar-grid">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="cat-search" class="input-control" placeholder="Tìm kiếm tên danh mục..." oninput="debounceSearchCategory()">
        </div>
        <div class="filter-select">
            <select id="cat-status-filter" class="select-control" onchange="loadCategoriesTable()">
                <option value="">Tất cả trạng thái</option>
                <option value="ACTIVE">Kích hoạt</option>
                <option value="INACTIVE">Tạm ẩn</option>
            </select>
        </div>
        <button type="button" class="btn btn-outline btn-icon" onclick="resetCategoryFilters()" title="Làm mới bộ lọc">
            <i class="fa-solid fa-rotate-left"></i>
        </button>
    </div>

    <!-- Data Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 70px;">Mã</th>
                    <th>Tên Danh Mục</th>
                    <th>Mô Tả Chi Tiết</th>
                    <th>Số Lượng SP</th>
                    <th>Trạng Thái</th>
                    <th style="text-align: center; width: 120px;">Ghim Header</th>
                    <th style="text-align: right; width: 110px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody id="categories-table-body">
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem;">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: var(--honey);"></i>
                        <p style="margin-top: 8px; color: var(--text-light);">Đang tải danh sách danh mục...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- 3. Modal Thêm / Sửa Danh Mục -->
<div class="modal-backdrop" id="category-modal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title" id="category-modal-title">Thêm Danh Mục Mới</h3>
            <button type="button" class="btn-icon" onclick="closeCategoryModal()" style="border: none; background: transparent;">
                <i class="fa-solid fa-xmark" style="font-size: 18px;"></i>
            </button>
        </div>
        <form id="category-form" onsubmit="saveCategory(event)">
            <div class="modal-body">
                <input type="hidden" id="cat-id">

                <div class="form-group">
                    <label class="form-label">Tên Danh Mục <span class="req">*</span></label>
                    <input type="text" id="cat-name" class="input-control" required placeholder="Ví dụ: TEDDY MR. BEAN...">
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng Thái</label>
                    <select id="cat-modal-status" class="select-control">
                        <option value="ACTIVE">Kích hoạt</option>
                        <option value="INACTIVE">Tạm ẩn</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Mô Tả Danh Mục</label>
                    <textarea id="cat-desc" class="input-control" placeholder="Mô tả thông tin chi tiết về bộ sưu tập danh mục này..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeCategoryModal()">Hủy</button>
                <button type="submit" class="btn btn-primary" id="btn-save-cat">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu Danh Mục
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let catSearchTimeout = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadCategories();
    });

    async function loadCategories() {
        await Promise.all([loadCategoriesSlider(), loadCategoriesTable()]);
    }

    async function loadCategoriesSlider() {
        const slider = document.getElementById('category-cards-slider');
        try {
            const res = await fetch('/api/admin/categories');
            const data = await res.json();
            if (data.success && data.data && data.data.length > 0) {
                slider.innerHTML = data.data.map(cat => {
                    const isActive = cat.is_active === true || cat.is_active === 1 || cat.status === 'ACTIVE';
                    return `
                        <div class="category-card">
                            <div class="category-card-top">
                                <div class="category-icon">
                                    <i class="fa-solid fa-paw"></i>
                                </div>
                                <span class="badge-status ${isActive ? 'active' : 'inactive'}">
                                    ${isActive ? 'Kích hoạt' : 'Tạm ẩn'}
                                </span>
                            </div>
                            <div class="category-name" title="${cat.name}">${cat.name}</div>
                        </div>
                    `;
                }).join('');
            } else {
                slider.innerHTML = '<div style="padding: 1rem; color: var(--text-light);">Chưa có danh mục nào.</div>';
            }
        } catch (e) {
            slider.innerHTML = '<div style="padding: 1rem; color: var(--danger);">Lỗi tải slider danh mục.</div>';
        }
    }

    function slideCategories(direction) {
        const track = document.getElementById('category-cards-slider');
        const card = track.querySelector('.category-card');
        const cardWidth = card ? (card.offsetWidth + 16) : 260;
        track.scrollBy({ left: direction * cardWidth, behavior: 'smooth' });
    }

    async function loadCategoriesTable() {
        const tbody = document.getElementById('categories-table-body');
        const search = document.getElementById('cat-search').value;
        const status = document.getElementById('cat-status-filter').value;

        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (status) params.append('status', status);

        try {
            const res = await fetch(`/api/admin/categories?${params.toString()}`);
            const data = await res.json();

            if (data.success) {
                const list = Array.isArray(data.data) ? data.data : (data.data?.data || []);
                renderCategoriesTable(list);
            } else {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--danger);padding:2rem;">${data.message || 'Lỗi tải dữ liệu.'}</td></tr>`;
            }
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--danger);padding:2rem;">Lỗi tải dữ liệu danh mục.</td></tr>`;
        }
    }

    function renderCategoriesTable(categories) {
        const tbody = document.getElementById('categories-table-body');
        if (!categories || categories.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--text-light);">Không tìm thấy danh mục nào.</td></tr>`;
            return;
        }

        tbody.innerHTML = categories.map(cat => {
            const slug = cat.slug || cat.name.toLowerCase().replace(/[^a-z0-9]+/g, '-');
            const isActive = cat.is_active === true || cat.is_active === 1 || cat.status === 'ACTIVE';
            const isPinned = Boolean(cat.is_pinned);

            return `
                <tr>
                    <td><strong>#${cat.id}</strong></td>
                    <td>
                        <div style="font-weight: 800; color: var(--text-main);">${cat.name}</div>
                        <div style="font-size: 11.5px; color: #8D6E63;">/${slug}</div>
                    </td>
                    <td>
                        <div style="max-width: 380px; font-size: 12.5px; color: var(--text-muted); line-height: 1.5;">
                            ${cat.description || '<em style="color: var(--text-light);">Chưa có mô tả</em>'}
                        </div>
                    </td>
                    <td>
                        <span style="font-weight: 800; color: #5D4037;">${cat.products_count ?? (cat.products ? cat.products.length : 0)}</span> SP
                    </td>
                    <td>
                        <span class="badge-status ${isActive ? 'active' : 'inactive'}">
                            <i class="fa-solid fa-circle" style="font-size: 7px;"></i>
                            ${isActive ? 'Kích hoạt' : 'Tạm ẩn'}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-icon pin-btn ${isPinned ? 'is-pinned' : ''}" 
                                onclick="confirmTogglePin(${cat.id}, '${escapeQuote(cat.name)}', ${isPinned})" 
                                title="${isPinned ? 'Đang ghim trên Header (Click để bỏ ghim)' : 'Ghim danh mục lên Header'}">
                            <i class="fa-solid fa-thumbtack"></i>
                        </button>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 4px;">
                            <button type="button" class="btn-icon edit" onclick="editCategory(${cat.id})" title="Chỉnh sửa danh mục">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button type="button" class="btn-icon delete" onclick="deleteCategory(${cat.id})" title="Xóa / Tạm ẩn">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function escapeQuote(str) {
        return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    async function confirmTogglePin(id, name, isPinned) {
        if (!isPinned) {
            // Xác nhận ghim vào Header
            const result = await Swal.fire({
                title: 'Ghim vào Header?',
                html: `Bạn có chắc chắn muốn ghim danh mục <strong>${name}</strong> vào header không ?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#8D6E63',
                cancelButtonColor: '#BCAAA4',
                confirmButtonText: 'Đồng ý ghim',
                cancelButtonText: 'Hủy bỏ'
            });

            if (!result.isConfirmed) return;

            try {
                const res = await fetch(`/api/admin/categories/${id}/toggle-pin`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: data.message,
                        timer: 1600,
                        showConfirmButton: false
                    });
                    loadCategories();
                } else {
                    // Bắt lỗi khi header đã đủ 5 danh mục
                    Swal.fire({
                        icon: 'warning',
                        title: 'Giới hạn danh mục Header',
                        html: `
                            <div style="font-size: 14.5px; line-height: 1.6; color: #4E342E;">
                                ${data.message}
                            </div>
                            <div style="font-size: 12px; color: #8D6E63; margin-top: 14px; font-weight: 700; border-top: 1px dashed #EADFCF; padding-top: 10px;">
                                Note: Tối đa để 5 danh mục ở header
                            </div>
                        `,
                        confirmButtonColor: '#8D6E63',
                        confirmButtonText: 'Đã hiểu'
                    });
                }
            } catch (e) {
                Swal.fire('Lỗi', 'Có lỗi kết nối máy chủ', 'error');
            }
        } else {
            // Xác nhận bỏ ghim
            const result = await Swal.fire({
                title: 'Bỏ ghim khỏi Header?',
                html: `Bạn có chắc muốn bỏ ghim danh mục <strong>${name}</strong> khỏi header không?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#C62828',
                cancelButtonColor: '#8D6E63',
                confirmButtonText: 'Bỏ ghim',
                cancelButtonText: 'Hủy bỏ'
            });

            if (!result.isConfirmed) return;

            try {
                const res = await fetch(`/api/admin/categories/${id}/toggle-pin`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã cập nhật',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    loadCategories();
                }
            } catch (e) {
                Swal.fire('Lỗi', 'Có lỗi kết nối máy chủ', 'error');
            }
        }
    }

    function debounceSearchCategory() {
        clearTimeout(catSearchTimeout);
        catSearchTimeout = setTimeout(() => loadCategoriesTable(), 350);
    }

    function resetCategoryFilters() {
        document.getElementById('cat-search').value = '';
        document.getElementById('cat-status-filter').value = '';
        loadCategoriesTable();
    }

    function openCategoryModal(cat = null) {
        document.getElementById('category-modal').classList.add('show');
        if (cat) {
            document.getElementById('category-modal-title').innerText = 'Chỉnh Sửa Danh Mục #' + cat.id;
            document.getElementById('cat-id').value = cat.id;
            document.getElementById('cat-name').value = cat.name;
            const isActive = cat.is_active === true || cat.is_active === 1 || cat.status === 'ACTIVE';
            document.getElementById('cat-modal-status').value = isActive ? 'ACTIVE' : 'INACTIVE';
            document.getElementById('cat-desc').value = cat.description || '';
        } else {
            document.getElementById('category-modal-title').innerText = 'Thêm Danh Mục Mới';
            document.getElementById('category-form').reset();
            document.getElementById('cat-id').value = '';
        }
    }

    function closeCategoryModal() {
        document.getElementById('category-modal').classList.remove('show');
    }

    async function editCategory(id) {
        try {
            const res = await fetch(`/api/admin/categories`);
            const data = await res.json();
            if (data.success) {
                const list = Array.isArray(data.data) ? data.data : (data.data?.data || []);
                const cat = list.find(c => c.id === id);
                if (cat) openCategoryModal(cat);
            }
        } catch (e) {
            Swal.fire('Lỗi', 'Không thể lấy thông tin danh mục', 'error');
        }
    }

    async function saveCategory(e) {
        e.preventDefault();
        const id = document.getElementById('cat-id').value;
        const statusVal = document.getElementById('cat-modal-status').value;
        const body = {
            name: document.getElementById('cat-name').value,
            description: document.getElementById('cat-desc').value,
            is_active: statusVal === 'ACTIVE' ? 1 : 0,
        };

        const url = id ? `/api/admin/categories/${id}` : '/api/admin/categories';
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
                closeCategoryModal();
                loadCategories();
            } else {
                Swal.fire('Lỗi', data.message || 'Không thể lưu danh mục', 'error');
            }
        } catch (err) {
            Swal.fire('Lỗi', 'Có lỗi xảy ra khi kết nối máy chủ', 'error');
        }
    }

    async function deleteCategory(id) {
        const result = await Swal.fire({
            title: 'Xóa / Tạm ẩn danh mục?',
            text: 'Danh mục sẽ được xử lý an toàn theo ràng buộc sản phẩm trong hệ thống.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#C62828',
            cancelButtonColor: '#795548',
            confirmButtonText: 'Đồng ý xóa',
            cancelButtonText: 'Hủy bỏ'
        });

        if (result.isConfirmed) {
            try {
                const res = await fetch(`/api/admin/categories/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Đã xóa', text: data.message, timer: 1500, showConfirmButton: false });
                    loadCategories();
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            } catch (err) {
                Swal.fire('Lỗi', 'Không thể xóa danh mục', 'error');
            }
        }
    }
</script>
@endsection
