@extends('layouts.admin-dashboard')

@php $currentPage = 'products'; @endphp

@section('page-title', 'Quản Lý Sản Phẩm')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-products.css') }}">
@endsection

@section('content')

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
