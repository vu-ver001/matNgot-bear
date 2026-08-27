@extends('layouts.admin-dashboard')

@php $currentPage = 'products'; @endphp

@section('page-title', 'Chỉnh Sửa Sản Phẩm #' . $product->id)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-products.css') }}">
<style>
    .form-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 2rem;
        box-shadow: var(--shadow-card);
        margin-bottom: 2rem;
    }
    .form-section-title {
        font-family: 'Be Vietnam Pro', sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-section-title i {
        color: #8D6E63;
        font-size: 18px;
    }
    .form-hint {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }
    .image-counter-badge {
        font-size: 12.5px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 20px;
        background: #EFEBE9;
        color: #5D4037;
        border: 1px solid #D7CCC8;
    }
    .image-counter-badge.limit {
        background: #FFEBEE;
        color: #C62828;
        border-color: #EF9A9A;
    }
    .upload-box-area {
        border: 2px dashed #BCAAA4;
        border-radius: var(--radius-lg);
        padding: 2rem 1.5rem;
        text-align: center;
        background: #FAF8F5;
        cursor: pointer;
        transition: all 0.25s ease;
        margin-bottom: 1.25rem;
    }
    .upload-box-area:hover, .upload-box-area.dragover {
        border-color: #8D6E63;
        background: #F2ECE7;
        transform: translateY(-2px);
    }
    .upload-box-icon {
        width: 50px;
        height: 50px;
        background: #EFEBE9;
        color: #5D4037;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin: 0 auto 10px auto;
        transition: all 0.2s ease;
    }
    .upload-box-area:hover .upload-box-icon {
        background: #8D6E63;
        color: #FFFFFF;
        transform: scale(1.08);
    }
    .preview-card {
        background: var(--bg-card);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        padding: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        box-shadow: var(--shadow-subtle);
        transition: all 0.2s ease;
    }
    .preview-card:hover {
        border-color: #BCAAA4;
        transform: translateY(-2px);
        box-shadow: var(--shadow-card);
    }
    .preview-card.is-primary {
        border: 2px solid #8D6E63;
        background: #F5EBE6;
    }
    .preview-thumb {
        width: 100%;
        height: 110px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        margin-bottom: 8px;
        background: #EFEBE9;
    }
    .preview-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        gap: 6px;
    }
</style>
@endsection

@section('content')

<!-- Header Breadcrumb & Title -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 4px;">
            <a href="{{ route('admin.products.index') }}" style="color: #8D6E63; text-decoration: none; font-weight: 600;">
                <i class="fa-solid fa-boxes-stacked"></i> Danh Sách Sản Phẩm
            </a>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
            <span>Chỉnh sửa #{{ $product->id }}</span>
        </div>
        <h2 style="font-family: 'Be Vietnam Pro', sans-serif; font-size: 22px; font-weight: 800; color: var(--text-main); margin: 0;">
            Chỉnh Sửa Sản Phẩm: {{ $product->name }}
        </h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Quay Lại
        </a>
    </div>
</div>

<form id="edit-product-form" onsubmit="handleUpdateProduct(event)" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.75rem;" class="product-form-layout">
        
        <!-- CỘT TRÁI: THÔNG TIN CHI TIẾT & ẢNH -->
        <div>
            <!-- 1. Thông tin chung -->
            <div class="form-card">
                <div class="form-section-title">
                    <i class="fa-solid fa-circle-info"></i> Thông Tin Cơ Bản
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tên Sản Phẩm <span class="req">*</span></label>
                    <input type="text" id="prod-name" name="name" class="input-control" required value="{{ old('name', $product->name) }}" placeholder="Ví dụ: Gấu Bông Teddy Socola 1m8...">
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Danh Mục <span class="req">*</span></label>
                        <select id="prod-category" name="category_id" class="select-control" required>
                            <option value="">-- Chọn danh mục gấu bông --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Trạng Thái Kinh Doanh <span class="req">*</span></label>
                        <select id="prod-status" name="status" class="select-control" required>
                            <option value="ACTIVE" {{ $product->status === 'ACTIVE' ? 'selected' : '' }}>Đang kinh doanh (Hiển thị bán)</option>
                            <option value="INACTIVE" {{ $product->status === 'INACTIVE' ? 'selected' : '' }}>Ngừng kinh doanh (Tạm ẩn)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Mô Tả Chi Tiết Sản Phẩm</label>
                    <textarea id="prod-desc" name="description" class="input-control" rows="4" placeholder="Mô tả chi tiết sản phẩm...">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            <!-- 2. Giá & Thời Gian Khuyến Mãi Ăn Liền CSDL -->
            <div class="form-card">
                <div class="form-section-title">
                    <i class="fa-solid fa-tag"></i> Thiết Lập Giá & Thời Gian Khuyến Mãi
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Giá Gốc Niêm Yết (VNĐ) <span class="req">*</span></label>
                        <input type="number" id="prod-price" name="price" class="input-control" required min="0" step="1000" value="{{ (int) $product->price }}" oninput="calculateDiscount()">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Giá Khuyến Mãi (VNĐ)</label>
                        <input type="number" id="prod-sale-price" name="sale_price" class="input-control" min="0" step="1000" value="{{ $product->sale_price ? (int) $product->sale_price : '' }}" oninput="calculateDiscount()">
                        <div class="form-hint" id="discount-hint">Để trống nếu không có chương trình giảm giá.</div>
                    </div>
                </div>

                <div class="form-grid" style="margin-top: 0.5rem; background: #FAF8F5; padding: 1.25rem; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">
                            <i class="fa-regular fa-calendar-plus" style="color: #8D6E63;"></i> Ngày Bắt Đầu Khuyến Mãi
                        </label>
                        <input type="datetime-local" id="prod-sale-start" name="sale_start_at" class="input-control" value="{{ $product->sale_start_at ? $product->sale_start_at->format('Y-m-d\TH:i') : '' }}">
                        <div class="form-hint">Thời điểm bắt đầu áp dụng mức giá giảm.</div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">
                            <i class="fa-regular fa-calendar-xmark" style="color: #C62828;"></i> Ngày Kết Thúc Khuyến Mãi
                        </label>
                        <input type="datetime-local" id="prod-sale-end" name="sale_end_at" class="input-control" value="{{ $product->sale_end_at ? $product->sale_end_at->format('Y-m-d\TH:i') : '' }}">
                        <div class="form-hint">Hết thời hạn này, giá bán sẽ tự động quay về <strong>Giá Gốc</strong>.</div>
                    </div>
                </div>
            </div>

            <!-- 3. Tải Ảnh Từ Máy Tính (Tối đa 6 ảnh) -->
            <div class="form-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-light);">
                    <div class="form-section-title" style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Tải Ảnh Sản Phẩm Từ Máy Tính
                    </div>
                    <span class="image-counter-badge" id="image-counter-badge">Đã chọn: 0 / 6 ảnh</span>
                </div>

                <!-- Ẩn input file thực tế -->
                <input type="file" id="prod-file-input" multiple accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" style="display: none;" onchange="handleFileSelect(event)">

                <!-- Hộp Bấm & Kéo thả ảnh -->
                <div class="upload-box-area" id="upload-dropzone" onclick="triggerFileInput()">
                    <div class="upload-box-icon">
                        <i class="fa-solid fa-images"></i>
                    </div>
                    <div style="font-size: 15px; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">
                        Bấm vào đây để chọn thêm ảnh từ máy tính
                    </div>
                    <div style="font-size: 12.5px; color: var(--text-muted); line-height: 1.5;">
                        Hỗ trợ <strong>chọn nhiều ảnh cùng một lúc</strong> (JPG, PNG, WEBP, GIF)<br>
                        <span style="color: #8D6E63; font-weight: 700;">* Giới hạn tổng cộng tối đa 6 ảnh cho sản phẩm</span>
                    </div>
                </div>

                <!-- Danh sách preview ảnh -->
                <div class="gallery-grid" id="product-images-grid" style="grid-template-columns: repeat(auto-fill, minmax(135px, 1fr)); gap: 12px;">
                    <!-- Render dynamically -->
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: THUỘC TÍNH & THAO TÁC LƯU -->
        <div>
            <!-- 4. Thuộc tính & Tồn kho -->
            <div class="form-card">
                <div class="form-section-title">
                    <i class="fa-solid fa-sliders"></i> Thuộc Tính & Tồn Kho
                </div>

                <div class="form-group">
                    <label class="form-label">Số Lượng Tồn Kho <span class="req">*</span></label>
                    <input type="number" id="prod-stock" name="stock_quantity" class="input-control" required min="0" value="{{ $product->stock_quantity }}">
                    <div class="form-hint">Hệ thống sẽ cảnh báo khi tồn kho &le; 5 sản phẩm.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Kích Thước (Size)</label>
                    <input type="text" id="prod-size" name="size" class="input-control" value="{{ old('size', $product->size) }}" placeholder="Ví dụ: 30cm, 1m2, 1m8...">
                </div>

                <div class="form-group">
                    <label class="form-label">Màu Sắc</label>
                    <input type="text" id="prod-color" name="color" class="input-control" value="{{ old('color', $product->color) }}" placeholder="Ví dụ: Nâu socola, Vàng bơ...">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Chất Liệu</label>
                    <input type="text" id="prod-material" name="material" class="input-control" value="{{ old('material', $product->material) }}" placeholder="Ví dụ: 100% Bông PP 3D xoắn...">
                </div>
            </div>

            <!-- 5. Hộp Lưu Hành Động -->
            <div class="form-card" style="background: #FAF8F5; border-color: #D7CCC8;">
                <div style="margin-bottom: 1.25rem;">
                    <div style="font-weight: 800; font-size: 15px; color: var(--text-main); margin-bottom: 4px;">
                        Cập nhật sản phẩm
                    </div>
                    <div style="font-size: 12.5px; color: var(--text-muted);">
                        Mã sản phẩm: <strong>#{{ $product->id }}</strong><br>
                        Đã bán: <strong>{{ $product->sold_count ?? 0 }}</strong> đơn
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button type="submit" class="btn btn-primary" id="btn-submit" style="justify-content: center; padding: 12px; font-size: 14px;">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline" style="justify-content: center;">
                        Hủy Bỏ
                    </a>
                </div>
            </div>
        </div>

    </div>
</form>

<script>
    const MAX_IMAGES = 6;

    // Danh sách ảnh đã có từ server
    let existingImages = @json($product->images->map(function($img) {
        return [
            'id' => $img->id,
            'url' => $img->image_url,
            'is_primary' => (bool) $img->is_primary
        ];
    }));

    // Danh sách file ảnh mới chọn từ máy tính
    let newFiles = []; // { file, previewUrl, is_primary }

    let primarySelection = { type: 'existing', id: null, index: 0 };

    document.addEventListener('DOMContentLoaded', () => {
        calculateDiscount();
        // Xác định ảnh chính ban đầu
        const primaryExisting = existingImages.find(img => img.is_primary);
        if (primaryExisting) {
            primarySelection = { type: 'existing', id: primaryExisting.id, index: 0 };
        } else if (existingImages.length > 0) {
            existingImages[0].is_primary = true;
            primarySelection = { type: 'existing', id: existingImages[0].id, index: 0 };
        }
        renderImagesGrid();
    });

    function triggerFileInput() {
        document.getElementById('prod-file-input').click();
    }

    // Thiết lập kéo thả file
    const dropzone = document.getElementById('upload-dropzone');
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        }, false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        }, false);
    });
    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        processNewFiles(dt.files);
    });

    function handleFileSelect(e) {
        processNewFiles(e.target.files);
        e.target.value = '';
    }

    function processNewFiles(fileList) {
        if (!fileList || fileList.length === 0) return;

        const filesArray = Array.from(fileList).filter(f => f.type.startsWith('image/'));

        if (filesArray.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tệp không hợp lệ',
                text: 'Vui lòng chọn các tệp định dạng hình ảnh (JPG, PNG, WEBP, GIF)!',
                confirmButtonColor: '#8D6E63'
            });
            return;
        }

        const totalCount = existingImages.length + newFiles.length + filesArray.length;

        // Nếu vượt quá 6 ảnh -> Thông báo lỗi và chặn
        if (totalCount > MAX_IMAGES) {
            const availableSlots = MAX_IMAGES - (existingImages.length + newFiles.length);
            Swal.fire({
                icon: 'error',
                title: 'Vượt quá giới hạn ảnh!',
                text: `Bạn vừa chọn ${filesArray.length} ảnh. Tổng số ảnh tối đa là ${MAX_IMAGES} (hiện chỉ còn trống ${Math.max(0, availableSlots)} chỗ). Vui lòng xóa bớt ảnh cũ trước khi thêm!`,
                confirmButtonColor: '#8D6E63'
            });
            return;
        }

        // Thêm vào danh sách file mới
        filesArray.forEach(file => {
            const previewUrl = URL.createObjectURL(file);
            const isFirstOverall = (existingImages.length === 0 && newFiles.length === 0);
            newFiles.push({
                file: file,
                previewUrl: previewUrl,
                is_primary: isFirstOverall
            });
            if (isFirstOverall) {
                primarySelection = { type: 'new', id: null, index: 0 };
            }
        });

        renderImagesGrid();
    }

    function renderImagesGrid() {
        const grid = document.getElementById('product-images-grid');
        const badge = document.getElementById('image-counter-badge');
        const total = existingImages.length + newFiles.length;

        badge.innerText = `Đã chọn: ${total} / ${MAX_IMAGES} ảnh`;
        if (total >= MAX_IMAGES) {
            badge.classList.add('limit');
        } else {
            badge.classList.remove('limit');
        }

        if (total === 0) {
            grid.innerHTML = `
                <div id="no-images-placeholder" style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: var(--text-light); border: 2px dashed var(--border); border-radius: var(--radius-md); background: #FAF8F5;">
                    <i class="fa-regular fa-image" style="font-size: 28px; margin-bottom: 8px; color: #BCAAA4;"></i>
                    <div style="font-weight: 600; color: var(--text-muted);">Chưa có ảnh nào cho sản phẩm này.</div>
                    <div style="font-size: 12px; margin-top: 4px;">Hãy bấm vào khung phía trên để chọn ảnh từ máy tính của bạn.</div>
                </div>
            `;
            return;
        }

        let html = '';

        // 1. Render ảnh hiện có
        existingImages.forEach(img => {
            const isPrim = (primarySelection.type === 'existing' && primarySelection.id === img.id);
            html += `
                <div class="preview-card ${isPrim ? 'is-primary' : ''}">
                    <img src="${img.url}" class="preview-thumb" alt="Product Image" onerror="this.src='https://placehold.co/120x90?text=Loi+Anh'">
                    <div class="preview-actions">
                        <button type="button" class="primary-badge-btn" onclick="setExistingPrimary(${img.id})" title="${isPrim ? 'Ảnh đại diện chính' : 'Bấm để đặt làm ảnh chính'}">
                            ${isPrim ? '<i class="fa-solid fa-star"></i> Ảnh chính' : 'Đặt chính'}
                        </button>
                        <button type="button" class="btn-icon delete" style="width:26px;height:26px;font-size:11px;" onclick="removeExistingImage(${img.id})" title="Xóa ảnh này">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        // 2. Render ảnh mới tải lên từ máy tính
        newFiles.forEach((item, index) => {
            const isPrim = (primarySelection.type === 'new' && primarySelection.index === index);
            html += `
                <div class="preview-card ${isPrim ? 'is-primary' : ''}">
                    <img src="${item.previewUrl}" class="preview-thumb" alt="New Upload Preview">
                    <div class="preview-actions">
                        <button type="button" class="primary-badge-btn" onclick="setNewPrimary(${index})" title="${isPrim ? 'Ảnh đại diện chính' : 'Bấm để đặt làm ảnh chính'}">
                            ${isPrim ? '<i class="fa-solid fa-star"></i> Ảnh chính' : 'Đặt chính'}
                        </button>
                        <button type="button" class="btn-icon delete" style="width:26px;height:26px;font-size:11px;" onclick="removeNewFile(${index})" title="Xóa ảnh này">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;
    }

    function setExistingPrimary(id) {
        primarySelection = { type: 'existing', id: id, index: 0 };
        renderImagesGrid();
    }

    function setNewPrimary(index) {
        primarySelection = { type: 'new', id: null, index: index };
        renderImagesGrid();
    }

    function removeExistingImage(id) {
        const wasPrimary = (primarySelection.type === 'existing' && primarySelection.id === id);
        existingImages = existingImages.filter(img => img.id !== id);

        if (wasPrimary) {
            if (existingImages.length > 0) {
                primarySelection = { type: 'existing', id: existingImages[0].id, index: 0 };
            } else if (newFiles.length > 0) {
                primarySelection = { type: 'new', id: null, index: 0 };
            }
        }
        renderImagesGrid();
    }

    function removeNewFile(index) {
        const wasPrimary = (primarySelection.type === 'new' && primarySelection.index === index);
        URL.revokeObjectURL(newFiles[index].previewUrl);
        newFiles.splice(index, 1);

        if (wasPrimary) {
            if (existingImages.length > 0) {
                primarySelection = { type: 'existing', id: existingImages[0].id, index: 0 };
            } else if (newFiles.length > 0) {
                primarySelection = { type: 'new', id: null, index: 0 };
            }
        }
        renderImagesGrid();
    }

    function calculateDiscount() {
        const price = Number(document.getElementById('prod-price').value) || 0;
        const salePrice = Number(document.getElementById('prod-sale-price').value) || 0;
        const hint = document.getElementById('discount-hint');

        if (salePrice > 0 && price > 0) {
            if (salePrice >= price) {
                hint.innerHTML = '<span style="color: var(--danger);"><i class="fa-solid fa-triangle-exclamation"></i> Giá khuyến mãi phải nhỏ hơn giá gốc!</span>';
            } else {
                const percent = Math.round(((price - salePrice) / price) * 100);
                hint.innerHTML = `<span style="color: #2E7D32; font-weight: 700;"><i class="fa-solid fa-bolt"></i> Giảm ${percent}% so với giá gốc</span>`;
            }
        } else {
            hint.innerHTML = 'Để trống nếu không có chương trình giảm giá.';
        }
    }

    async function handleUpdateProduct(e) {
        e.preventDefault();

        const name = document.getElementById('prod-name').value.trim();
        const category_id = document.getElementById('prod-category').value;
        const status = document.getElementById('prod-status').value;
        const description = document.getElementById('prod-desc').value.trim();
        const price = Number(document.getElementById('prod-price').value);
        const salePriceVal = document.getElementById('prod-sale-price').value;
        const sale_price = salePriceVal ? Number(salePriceVal) : null;
        const sale_start_at = document.getElementById('prod-sale-start').value || null;
        const sale_end_at = document.getElementById('prod-sale-end').value || null;
        const stock_quantity = parseInt(document.getElementById('prod-stock').value, 10);
        const size = document.getElementById('prod-size').value.trim() || null;
        const color = document.getElementById('prod-color').value.trim() || null;
        const material = document.getElementById('prod-material').value.trim() || null;

        // Validate sale price
        if (sale_price !== null && sale_price >= price) {
            Swal.fire({
                icon: 'warning',
                title: 'Giá khuyến mãi không hợp lệ',
                text: 'Giá khuyến mãi phải nhỏ hơn giá gốc!',
                confirmButtonColor: '#8D6E63'
            });
            return;
        }

        // Validate sale dates
        if (sale_start_at && sale_end_at && new Date(sale_end_at) < new Date(sale_start_at)) {
            Swal.fire({
                icon: 'warning',
                title: 'Thời gian không hợp lệ',
                text: 'Ngày kết thúc khuyến mãi phải sau hoặc bằng ngày bắt đầu!',
                confirmButtonColor: '#8D6E63'
            });
            return;
        }

        const totalImages = existingImages.length + newFiles.length;
        if (totalImages > MAX_IMAGES) {
            Swal.fire({
                icon: 'error',
                title: 'Vượt quá giới hạn ảnh',
                text: `Sản phẩm chỉ được chọn tối đa ${MAX_IMAGES} ảnh!`,
                confirmButtonColor: '#8D6E63'
            });
            return;
        }

        // Tạo FormData
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', name);
        formData.append('category_id', category_id);
        formData.append('status', status);
        if (description) formData.append('description', description);
        formData.append('price', price);
        if (sale_price) formData.append('sale_price', sale_price);
        if (sale_start_at) formData.append('sale_start_at', sale_start_at);
        if (sale_end_at) formData.append('sale_end_at', sale_end_at);
        formData.append('stock_quantity', stock_quantity);
        if (size) formData.append('size', size);
        if (color) formData.append('color', color);
        if (material) formData.append('material', material);

        // Danh sách ảnh cũ giữ lại
        existingImages.forEach(img => {
            formData.append('kept_image_ids[]', img.id);
        });

        // Chỉ định ảnh chính
        formData.append('primary_type', primarySelection.type);
        if (primarySelection.type === 'existing') {
            formData.append('primary_id', primarySelection.id);
        } else {
            formData.append('primary_index', primarySelection.index);
        }

        // Các file ảnh mới tải lên
        newFiles.forEach(item => {
            formData.append('image_files[]', item.file);
        });

        const submitBtn = document.getElementById('btn-submit');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang tải ảnh và cập nhật...';

        try {
            const res = await fetch('{{ route("admin.products.update", $product->id) }}', {
                method: 'POST', // POST with _method = PUT for multipart
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: data.message || 'Đã cập nhật sản phẩm thành công!',
                    timer: 1800,
                    showConfirmButton: false
                });
                window.location.href = "{{ route('admin.products.index') }}";
            } else {
                let errorMsg = data.message || 'Không thể lưu sản phẩm';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi xác thực',
                    html: errorMsg,
                    confirmButtonColor: '#8D6E63'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi kết nối',
                text: 'Có lỗi xảy ra khi kết nối máy chủ.',
                confirmButtonColor: '#8D6E63'
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
</script>
@endsection
