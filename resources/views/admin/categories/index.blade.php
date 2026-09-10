@extends('layouts.admin-dashboard')

@php $currentPage = 'categories'; @endphp

@section('page-title', 'Quản Lý Danh Mục')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-categories.css') }}">
@endsection

@section('content')

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

<!-- 4. Modal Cấu Hình Menu Header (Tối đa 4 cột to, mỗi cột tối đa 3 sản phẩm) -->
<div class="modal-backdrop" id="header-menu-modal" style="display: none; z-index: 1050;" onclick="if(event.target === this) closeHeaderMenuModal()">
    <div class="modal-box" style="max-width: 980px; width: 95%; max-height: 90vh; display: flex; flex-direction: column; background: #FFFFFF; border-radius: 16px; box-shadow: 0 20px 45px rgba(78, 52, 46, 0.25); overflow: hidden;">
        <div class="modal-header" style="background: linear-gradient(135deg, #FFFDF8 0%, #FAF6EE 100%); border-bottom: 1.5px solid #F6D89B; padding: 18px 24px; display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%); color: #3E2723; font-size: 19px; box-shadow: 0 4px 14px rgba(229, 152, 25, 0.35);">
                    <i class="fa-solid fa-table-columns"></i>
                </span>
                <div>
                    <h3 class="modal-title" style="margin: 0; color: #3E2723; font-size: 19px; font-weight: 900;">
                        Cấu Hình Menu Header: <span id="header-menu-cat-name" style="color: #D98200; font-weight: 900;"></span>
                    </h3>
                    <p style="margin: 4px 0 0 0; font-size: 12.5px; color: #795548; line-height: 1.4;">
                        Thiết lập hiển thị khi rê chuột vào danh mục: Giới hạn tối đa <strong>4 cột menu to</strong> (được đặt tên riêng), mỗi cột tối đa <strong>3 sản phẩm</strong>.
                    </p>
                </div>
            </div>
            <button type="button" class="btn-icon" onclick="closeHeaderMenuModal()" style="border: none; background: transparent; color: #8D6E63; font-size: 20px; cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body" style="padding: 20px 24px; overflow-y: auto; flex: 1; background: #FAF7F2;">
            <!-- Action bar inside modal -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px dashed #E0D0C0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span id="col-counter-badge" style="font-weight: 800; font-size: 12px; padding: 5px 14px; border-radius: 999px; background: #FFF9EC; color: #B86F00; border: 1.5px solid #F6D89B; box-shadow: 0 2px 6px rgba(229, 152, 25, 0.15);">
                        0 / 4 Cột To
                    </span>
                    <span style="font-size: 12.5px; color: #795548;">(Khách hàng sẽ thấy theo đúng thứ tự cột từ trái sang phải)</span>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="autoGenerateSampleColumns()" style="font-size: 12.5px; padding: 7px 15px; color: #B86F00; border: 1.5px solid #E59819; background: #FFFBF0; font-weight: 800; box-shadow: 0 2px 6px rgba(229, 152, 25, 0.15);">
                        <i class="fa-solid fa-wand-magic-sparkles" style="color: #E59819;"></i> ⚡ Tự động tạo 4 cột mẫu
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-add-column" onclick="addNewColumn()" style="font-size: 12.5px; padding: 7px 15px; background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%); border: none; color: #3E2723; font-weight: 800; box-shadow: 0 3px 10px rgba(229, 152, 25, 0.3);">
                        <i class="fa-solid fa-plus"></i> Thêm Cột To
                    </button>
                </div>
            </div>

            <!-- Grid container for up to 4 columns -->
            <div id="megamenu-columns-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(215px, 1fr)); gap: 16px;">
                <!-- Dynamically populated via JS -->
            </div>
        </div>

        <div class="modal-footer" style="padding: 14px 24px; border-top: 1px solid #EFEBE9; background: #FFFFFF; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 12px; color: #795548;">
                <i class="fa-solid fa-circle-info" style="color: #E59819;"></i> Các cột trống không có sản phẩm sẽ không hiển thị trên thanh Header.
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeHeaderMenuModal()">Hủy Bỏ</button>
                <button type="button" class="btn btn-primary" id="btn-save-header-menu" onclick="saveHeaderMenuConfig()" style="background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%); border: none; color: #3E2723; font-weight: 800; font-size: 14px; padding: 10px 22px; border-radius: 8px; box-shadow: 0 4px 16px rgba(229, 152, 25, 0.35); cursor: pointer;">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu Cấu Hình Menu Header
                </button>
            </div>
        </div>
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

    let currentCategoriesList = [];

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
                currentCategoriesList = list;
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
                                onclick="confirmTogglePin(${cat.id})" 
                                title="${isPinned ? 'Đang ghim trên Header (Click để bỏ ghim)' : (isActive ? 'Ghim danh mục lên Header' : 'Danh mục đang Tạm ẩn (Không thể ghim)')}">
                            <i class="fa-solid fa-thumbtack" style="pointer-events: none;"></i>
                        </button>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 6px; align-items: center;">
                            <button type="button" class="btn-icon header-config-btn" onclick="openHeaderMenuModal(${cat.id})" title="Cấu hình Menu Header (Tối đa 4 cột to × 3 sản phẩm)" style="background: linear-gradient(135deg, #FFF9EC 0%, #FDF0D5 100%); color: #B86F00; border: 1.5px solid #F6D89B; border-radius: 8px; width: 34px; height: 34px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(229, 152, 25, 0.2);">
                                <i class="fa-solid fa-table-columns" style="pointer-events: none;"></i>
                            </button>
                            <button type="button" class="btn-icon edit" onclick="editCategory(${cat.id})" title="Chỉnh sửa danh mục">
                                <i class="fa-solid fa-pen-to-square" style="pointer-events: none;"></i>
                            </button>
                            <button type="button" class="btn-icon delete" onclick="deleteCategory(${cat.id})" title="Xóa / Tạm ẩn">
                                <i class="fa-solid fa-trash-can" style="pointer-events: none;"></i>
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

    async function confirmTogglePin(id) {
        const cat = currentCategoriesList.find(c => c.id == id);
        if (!cat) return;
        const name = cat.name;
        const isActive = cat.is_active === true || cat.is_active === 1 || cat.status === 'ACTIVE';
        const isPinned = Boolean(cat.is_pinned);

        if (!isPinned) {
            // RÀNG BUỘC: Nếu trạng thái là TẠM ẨN thì không cho phép ghim danh mục lên Header
            if (!isActive) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Không cho phép ghim',
                    html: `
                        <div style="font-size: 15px; line-height: 1.6; color: #4E342E; margin-bottom: 12px;">
                            Danh mục <strong>"${name}"</strong> hiện đang ở trạng thái <span style="color: #D32F2F; font-weight: 800; background: #FFEBEE; padding: 2px 8px; border-radius: 4px;">Tạm ẩn</span>.
                        </div>
                        <div style="font-size: 13.5px; color: #C62828; font-weight: 600; background: #FFF8E1; padding: 12px; border-radius: 8px; border-left: 4px solid #FFA000; text-align: left; line-height: 1.5;">
                            <i class="fa-solid fa-triangle-exclamation" style="color: #FFA000; margin-right: 6px;"></i>
                            Không thể ghim danh mục tạm ẩn lên thanh Header. Vui lòng chuyển trạng thái danh mục sang <strong>Kích hoạt</strong> trước khi ghim!
                        </div>
                    `,
                    confirmButtonColor: '#8D6E63',
                    confirmButtonText: 'Đã hiểu'
                });
                return;
            }

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
                    if (data.is_inactive) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Không cho phép ghim',
                            html: `
                                <div style="font-size: 15px; line-height: 1.6; color: #4E342E; margin-bottom: 12px;">
                                    ${data.message}
                                </div>
                            `,
                            confirmButtonColor: '#8D6E63',
                            confirmButtonText: 'Đã hiểu'
                        });
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
        const catName = (document.getElementById('cat-name').value || '').trim();
        
        if (!catName) {
            Swal.fire({
                icon: 'warning',
                title: 'Bé Gấu nhắc bạn nè! 🐻',
                html: 'Vui lòng nhập <b>Tên danh mục</b> nha!',
                confirmButtonColor: '#8D6E63'
            });
            document.getElementById('cat-name').focus();
            return;
        }

        const statusVal = document.getElementById('cat-modal-status').value;
        const body = {
            name: catName,
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
                Swal.fire({ icon: 'success', title: 'Thành công!', text: data.message, timer: 1500, showConfirmButton: false });
                closeCategoryModal();
                loadCategories();
            } else {
                let errHtml = data.message || 'Không thể lưu danh mục';
                if (data.errors) {
                    errHtml = Object.values(data.errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Bé Gấu nhắc bạn nè! 🐻',
                    html: errHtml,
                    confirmButtonColor: '#8D6E63'
                });
            }
        } catch (err) {
            Swal.fire('Lỗi kết nối', 'Có lỗi xảy ra khi kết nối máy chủ', 'error');
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

    // =========================================================
    // QUẢN LÝ CẤU HÌNH MENU HEADER (TỐI ĐA 4 CỘT TO, MỖI CỘT TỐI ĐA 3 SP)
    // =========================================================
    let currentHeaderCatId = null;
    let currentHeaderColumns = [];
    let currentCategoryProducts = [];

    async function openHeaderMenuModal(catId, catName) {
        currentHeaderCatId = catId;
        if (!catName) {
            const found = currentCategoriesList.find(c => c.id == catId);
            catName = found ? found.name : '';
        }

        const modal = document.getElementById('header-menu-modal');
        if (!modal) return;

        document.getElementById('header-menu-cat-name').innerText = catName;
        document.getElementById('megamenu-columns-grid').innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 2.5rem; color: #8D6E63;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: #E65100;"></i>
                <p style="margin-top: 10px;">Đang tải cấu hình menu header...</p>
            </div>
        `;

        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });

        try {
            const res = await fetch(`/api/admin/categories/${catId}/header-menu`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (data.success) {
                if (data.category && data.category.name) {
                    document.getElementById('header-menu-cat-name').innerText = data.category.name;
                }
                currentCategoryProducts = data.products || [];
                currentHeaderColumns = (data.category.header_menu_config && data.category.header_menu_config.length > 0)
                    ? data.category.header_menu_config
                    : [];

                // Nếu chưa có cột nào, tự tạo 1 cột đầu tiên để admin nhập
                if (currentHeaderColumns.length === 0) {
                    currentHeaderColumns = [
                        { title: 'BỘ SƯU TẬP NỔI BẬT', items: [] }
                    ];
                }

                renderMegamenuColumns();
            } else {
                Swal.fire('Lỗi', data.message || 'Không thể lấy dữ liệu menu header', 'error');
                closeHeaderMenuModal();
            }
        } catch (err) {
            Swal.fire('Lỗi', 'Không thể kết nối máy chủ', 'error');
            closeHeaderMenuModal();
        }
    }

    function closeHeaderMenuModal() {
        const modal = document.getElementById('header-menu-modal');
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
        }
        currentHeaderCatId = null;
        currentHeaderColumns = [];
        currentCategoryProducts = [];
    }

    function updateColumnCounterBadge() {
        const badge = document.getElementById('col-counter-badge');
        const btnAdd = document.getElementById('btn-add-column');
        const count = currentHeaderColumns.length;

        if (badge) {
            badge.innerText = `${count} / 4 Cột To`;
            if (count >= 4) {
                badge.style.background = '#FFEBEE';
                badge.style.color = '#C62828';
                badge.style.borderColor = '#FFCDD2';
            } else {
                badge.style.background = '#FFF3E0';
                badge.style.color = '#E65100';
                badge.style.borderColor = '#FFCC80';
            }
        }

        if (btnAdd) {
            btnAdd.disabled = (count >= 4);
            btnAdd.style.opacity = (count >= 4) ? '0.5' : '1';
            btnAdd.style.cursor = (count >= 4) ? 'not-allowed' : 'pointer';
        }
    }

    function renderMegamenuColumns() {
        const container = document.getElementById('megamenu-columns-grid');
        updateColumnCounterBadge();

        if (currentHeaderColumns.length === 0) {
            container.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: #FFFFFF; border-radius: 12px; border: 2px dashed #D7CCC8;">
                    <i class="fa-solid fa-table-columns" style="font-size: 32px; color: #BCAAA4; margin-bottom: 12px;"></i>
                    <h4 style="color: #5D4037; margin: 0 0 6px 0;">Chưa có cột menu nào</h4>
                    <p style="color: #8D6E63; font-size: 13px; margin: 0 0 16px 0;">Bấm "Thêm Cột To" hoặc "Tự động tạo mẫu" để cấu hình menu header cho danh mục này.</p>
                    <button type="button" class="btn btn-primary" onclick="addNewColumn()" style="background: #8D6E63;">
                        <i class="fa-solid fa-plus"></i> Thêm Cột Đầu Tiên
                    </button>
                </div>
            `;
            return;
        }

        container.innerHTML = currentHeaderColumns.map((col, cIdx) => {
            const items = col.items || [];
            
            // Xây dựng 3 slot sản phẩm
            let productSlotsHtml = '';
            for (let slot = 0; slot < 3; slot++) {
                const curItem = items[slot] || null;
                const curPId = curItem ? curItem.product_id : '';
                const curCustomName = curItem ? (curItem.name || '') : '';

                let optionsHtml = `<option value="">-- Chọn sản phẩm (${slot + 1}/3) --</option>`;
                currentCategoryProducts.forEach(p => {
                    const selected = (p.id == curPId) ? 'selected' : '';
                    optionsHtml += `<option value="${p.id}" ${selected}>${p.name} (#${p.id})</option>`;
                });

                productSlotsHtml += `
                    <div style="background: #FAF7F2; padding: 10px; border-radius: 8px; border: 1px solid #EFE6DC; margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 11.5px; font-weight: 700; color: #8D6E63; margin-bottom: 4px;">
                            <span><i class="fa-solid fa-paw" style="color: #E59819;"></i> Sản phẩm ${slot + 1}</span>
                            ${curItem ? `<span style="color: #2E7D32;"><i class="fa-solid fa-check"></i> Đã chọn</span>` : `<span style="color: #BDBDBD;">Trống</span>`}
                        </div>
                        <select class="select-control" style="font-size: 12px; padding: 6px 10px; margin-bottom: 4px; background: #FFFFFF;" onchange="updateProductSlot(${cIdx}, ${slot}, this.value)">
                            ${optionsHtml}
                        </select>
                        <input type="text" class="input-control" placeholder="Tên hiển thị tùy chỉnh (để trống lấy tên gốc)" value="${escapeQuote(curCustomName)}" style="font-size: 11.5px; padding: 5px 8px; background: #FFFFFF;" onchange="updateCustomNameSlot(${cIdx}, ${slot}, this.value)">
                    </div>
                `;
            }

            return `
                <div class="megamenu-column-card" style="background: #FFFFFF; border-radius: 12px; border: 1.5px solid #F6D89B; padding: 14px; box-shadow: 0 4px 14px rgba(229, 152, 25, 0.1); display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #FAF0DA;">
                        <span style="font-weight: 900; font-size: 11.5px; padding: 3px 10px; border-radius: 5px; background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%); color: #3E2723; letter-spacing: 0.5px; box-shadow: 0 2px 6px rgba(229, 152, 25, 0.25);">
                            CỘT ${cIdx + 1}
                        </span>
                        <button type="button" onclick="removeColumn(${cIdx})" title="Xóa cột này" style="background: transparent; border: none; color: #E53935; cursor: pointer; font-size: 14px; padding: 2px 6px;">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 11px; font-weight: 800; color: #3E2723; text-transform: uppercase; margin-bottom: 4px;">
                            Tên Cột Menu To <span style="color: #E53935;">*</span>:
                        </label>
                        <input type="text" class="input-control" placeholder="Ví dụ: BÁN CHẠY NHẤT..." value="${escapeQuote(col.title)}" style="font-weight: 800; font-size: 13px; color: #B86F00; border: 1.5px solid #F6D89B; background: #FFFDF8;" onchange="updateColumnTitle(${cIdx}, this.value)">
                    </div>

                    <div style="font-size: 11.5px; font-weight: 800; color: #3E2723; margin-bottom: 6px; text-transform: uppercase;">
                        Tối Đa 3 Sản Phẩm:
                    </div>

                    <div style="flex: 1;">
                        ${productSlotsHtml}
                    </div>
                </div>
            `;
        }).join('');
    }

    function addNewColumn() {
        if (currentHeaderColumns.length >= 4) {
            Swal.fire('Giới hạn', 'Mỗi danh mục tối đa 4 cột menu to trên Header!', 'warning');
            return;
        }

        const defaultNames = [
            'BỘ SƯU TẬP NỔI BẬT',
            'KÍCH THƯỚC KHỦNG',
            'MÀU SẮC YÊU THÍCH',
            'COMBO QUÀ TẶNG & PHỤ KIỆN'
        ];
        const nextTitle = defaultNames[currentHeaderColumns.length] || `CỘT MENU ${currentHeaderColumns.length + 1}`;

        currentHeaderColumns.push({
            title: nextTitle,
            items: []
        });

        renderMegamenuColumns();
    }

    function removeColumn(cIdx) {
        currentHeaderColumns.splice(cIdx, 1);
        renderMegamenuColumns();
    }

    function updateColumnTitle(cIdx, val) {
        if (currentHeaderColumns[cIdx]) {
            currentHeaderColumns[cIdx].title = val.trim();
        }
    }

    function updateProductSlot(cIdx, slot, pId) {
        if (!currentHeaderColumns[cIdx]) return;
        if (!currentHeaderColumns[cIdx].items) currentHeaderColumns[cIdx].items = [];

        pId = parseInt(pId) || null;
        if (!pId) {
            currentHeaderColumns[cIdx].items[slot] = null;
        } else {
            const p = currentCategoryProducts.find(x => x.id == pId);
            const curCustom = currentHeaderColumns[cIdx].items[slot]?.name || (p ? p.name : '');
            currentHeaderColumns[cIdx].items[slot] = {
                product_id: pId,
                name: curCustom,
                url: `/products/${pId}`
            };
        }
        renderMegamenuColumns();
    }

    function updateCustomNameSlot(cIdx, slot, name) {
        if (!currentHeaderColumns[cIdx]) return;
        if (!currentHeaderColumns[cIdx].items) currentHeaderColumns[cIdx].items = [];

        if (currentHeaderColumns[cIdx].items[slot]) {
            currentHeaderColumns[cIdx].items[slot].name = name.trim();
        }
    }

    function autoGenerateSampleColumns() {
        const titles = [
            'BỘ SƯU TẬP NỔI BẬT',
            'KÍCH THƯỚC KHỦNG',
            'MÀU SẮC YÊU THÍCH',
            'COMBO QUÀ TẶNG & PHỤ KIỆN'
        ];

        currentHeaderColumns = [];
        const prods = [...currentCategoryProducts];

        for (let i = 0; i < 4; i++) {
            const chunk = prods.slice(i * 3, (i + 1) * 3);
            const items = chunk.map(p => ({
                product_id: p.id,
                name: p.name,
                url: `/products/${p.id}`
            }));

            currentHeaderColumns.push({
                title: titles[i],
                items: items
            });
        }

        renderMegamenuColumns();
        Swal.fire({
            icon: 'info',
            title: 'Đã tạo 4 cột mẫu',
            text: 'Bạn có thể chỉnh sửa lại tên cột và chọn các sản phẩm theo ý muốn trước khi lưu.',
            timer: 1800,
            showConfirmButton: false
        });
    }

    async function saveHeaderMenuConfig() {
        if (!currentHeaderCatId) return;

        // Lọc bỏ cột trống title hoặc items rỗng nếu có
        const payloadColumns = [];
        for (let c of currentHeaderColumns) {
            const title = (c.title || '').trim();
            if (!title) {
                Swal.fire('Cảnh báo', 'Vui lòng nhập tên cho tất cả các cột menu to!', 'warning');
                return;
            }

            const items = (c.items || []).filter(it => it && (it.product_id || it.name));
            payloadColumns.push({
                title: title,
                items: items.slice(0, 3)
            });
        }

        if (payloadColumns.length > 4) {
            Swal.fire('Lỗi', 'Tối đa chỉ được để 4 cột menu to trên Header!', 'error');
            return;
        }

        const btn = document.getElementById('btn-save-header-menu');
        const oldHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...`;

        try {
            const res = await fetch(`/api/admin/categories/${currentHeaderCatId}/header-menu`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ columns: payloadColumns })
            });

            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: data.message,
                    timer: 1800,
                    showConfirmButton: false
                });
                closeHeaderMenuModal();
                loadCategories();
            } else {
                Swal.fire('Lỗi', data.message || 'Không thể lưu cấu hình', 'error');
            }
        } catch (err) {
            Swal.fire('Lỗi', 'Có lỗi kết nối máy chủ', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    }
</script>
@endsection
