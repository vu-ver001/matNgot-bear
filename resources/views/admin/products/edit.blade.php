@extends('layouts.admin-dashboard')

@php $currentPage = 'products'; @endphp

@section('page-title', 'Chỉnh Sửa Sản Phẩm #' . $product->id)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-products.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-product-form.css') }}">
@endsection

@section('content')

<!-- Header Breadcrumb & Title -->
<div class="form-page-header">
    <div>
        <div class="form-breadcrumb">
            <a href="{{ route('admin.products.index') }}">
                <i class="fa-solid fa-boxes-stacked"></i> Quản Lý Sản Phẩm
            </a>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
            <span>Chỉnh sửa sản phẩm #{{ $product->id }}</span>
        </div>
        <h1 class="form-page-title">
            <span>Chỉnh Sửa: {{ $product->name }}</span>
            <span class="form-header-badge"><i class="fa-solid fa-tag"></i> ID: #{{ $product->id }}</span>
        </h1>
    </div>
    <div class="form-page-actions">
        <a href="{{ route('admin.products.index') }}" class="btn-soft-back">
            <i class="fa-solid fa-arrow-left"></i> Quay Lại
        </a>
    </div>
</div>

<form id="edit-product-form" onsubmit="handleUpdateProduct(event)" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="product-form-layout">

        <!-- CỘT TRÁI: THÔNG TIN SẢN PHẨM & BẢNG SẢN PHẨM CON (BIẾN THỂ) -->
        <div>
            <!-- 1. THÔNG TIN CHUNG SẢN PHẨM CHA -->
            <div class="form-card">
                <div class="form-section-title">
                    <div class="form-section-title-left">
                        <div class="form-section-icon-wrap">
                            <i class="fa-solid fa-cube"></i>
                        </div>
                        <div>
                            <div class="form-section-title-text">Thông Tin Chung Sản Phẩm Cha</div>
                            <div class="form-hint" style="margin-top: 2px;">Tên gọi, danh mục và mô tả hiển thị chính của gấu bông</div>
                        </div>
                    </div>
                    <span class="form-section-badge">
                        Mã ID: #{{ $product->id }}
                    </span>
                </div>

                <div class="form-group">
                    <label class="form-label">Tên Sản Phẩm Gấu Bông <span class="req">*</span></label>
                    <input type="text" id="prod-name" name="name" class="input-control" required value="{{ $product->name }}" placeholder="Ví dụ: Gấu Bông Teddy Áo Len Cổ Điển...">
                    <div class="form-hint">Tên hiển thị chính trên website và tiêu đề trang chi tiết sản phẩm.</div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Danh Mục Sản Phẩm <span class="req">*</span></label>
                        <select id="prod-category" name="category_id" class="select-control" required>
                            <option value="">-- Chọn Danh Mục Gấu Bông --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Trạng Thái Kinh Doanh <span class="req">*</span></label>
                        <input type="hidden" id="prod-status" name="status" value="{{ $product->status === 'ACTIVE' ? 'ACTIVE' : 'INACTIVE' }}">
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 6px; padding: 7px 12px; background: #FFF9F3; border: 1px solid #EFEBE9; border-radius: 8px; width: fit-content;">
                            <div class="switch-toggle-box" onclick="toggleParentFormStatus()" title="Bấm để bật/tắt trạng thái kinh doanh">
                                <div class="switch-toggle-track {{ $product->status === 'ACTIVE' ? 'active' : '' }}" id="parent-status-track">
                                    <span class="switch-toggle-thumb"></span>
                                </div>
                            </div>
                            <span id="parent-status-label" style="font-size: 13px; font-weight: 700; color: {{ $product->status === 'ACTIVE' ? '#10B981' : '#8D6E63' }};">
                                {{ $product->status === 'ACTIVE' ? 'Đang kinh doanh (Bật)' : 'Tạm ẩn (Tắt)' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Chất Liệu Gấu Bông</label>
                    <input type="text" id="prod-material" name="material" class="input-control" value="{{ $product->material }}" placeholder="Ví dụ: 100% Bông PP 3D xoắn tinh khiết đàn hồi 4 chiều, vải nhung tuyết mịn...">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Mô Tả Chi Tiết Sản Phẩm</label>
                    <textarea id="prod-description" name="description" class="input-control" style="min-height: 100px;" placeholder="Mô tả ưu điểm nổi bật, xuất xứ, độ an toàn và cảm giác khi ôm...">{{ $product->description }}</textarea>
                </div>
            </div>

            <!-- 2. QUẢN LÝ CHI TIẾT SẢN PHẨM CON (BIẾN THỂ) -->
            <div class="variants-wrapper">
                <div class="variants-header-bar">
                    <div>
                        <div class="variants-header-title">
                            <div class="form-section-icon-wrap" style="background: linear-gradient(135deg, #FFF3E0 0%, #FFE0B2 100%); border-color: #FFCC80; color: #E65100;">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <span>Chi Tiết Các Sản Phẩm Con (Biến Thể Phân Loại)</span>
                        </div>
                        <div class="variants-header-desc">
                            Quản lý các phân loại (Kích thước &amp; Màu sắc) có giá bán, kho và ảnh tương ứng. Tự động đồng bộ với CSDL.
                        </div>
                    </div>

                    <!-- Nút công cụ (Gộp thành 1 nút Tạo nhanh / Áp dụng hàng loạt) -->
                    <div class="variant-tools-group">
                        <button type="button" class="btn-tool btn-tool-magic" onclick="openCombinedBulkModal()" title="Tự động tạo ma trận kết hợp Size & Màu hoặc áp dụng thông số hàng loạt">
                            <i class="fa-solid fa-bolt"></i> Tạo nhanh / Áp dụng hàng loạt
                        </button>
                        <button type="button" class="btn-tool btn-tool-add" onclick="addNewVariantRow()" title="Thêm thủ công 1 dòng sản phẩm con">
                            <i class="fa-solid fa-plus"></i> Thêm 1 Sản Phẩm Con
                        </button>
                    </div>
                </div>

                <!-- BẢNG TƯƠNG TÁC SẢN PHẨM CON -->
                <div class="variants-table-box">
                    <table class="variants-table">
                        <thead>
                            <tr>
                                <th style="width: 44px; text-align: center;">Ảnh <span style="color:#C62828;">*</span></th>
                                <th style="width: 17%; text-align: left;">Kích Thước <span style="color:#C62828;">*</span></th>
                                <th style="width: 18%; text-align: left;">Màu Sắc <span style="color:#C62828;">*</span></th>
                                <th style="width: 15%; text-align: left;">Giá Gốc <span style="color:#C62828;">*</span></th>
                                <th style="width: 18%; text-align: left;">Giá Sale &amp; Hẹn Giờ</th>
                                <th style="width: 14%; text-align: left;">Tồn Kho <span style="color:#C62828;">*</span></th>
                                <th style="width: 52px; text-align: center;">Bật/Tắt</th>
                                <th style="width: 48px; text-align: center;">Mặc Định</th>
                                <th style="width: 32px; text-align: center;">Xóa</th>
                            </tr>
                        </thead>
                        <tbody id="variants-tbody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- THANH TỔNG KẾT THỜI GIAN THỰC -->
                <div class="variants-summary-bar">
                    <div class="summary-stat-item">
                        <span style="color: #795548; font-weight: 600;">Tổng số phân loại:</span>
                        <span class="stat-badge" id="sum-count">0</span>
                    </div>
                    <div class="summary-stat-item">
                        <span style="color: #795548; font-weight: 600;">Khoảng giá sản phẩm:</span>
                        <span class="stat-badge" id="sum-price" style="color: #E65100;">0 đ</span>
                    </div>
                    <div class="summary-stat-item">
                        <span style="color: #795548; font-weight: 600;">Tổng tồn kho tất cả con:</span>
                        <span class="stat-badge" id="sum-stock" style="color: #2E7D32;">0 chiếc</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: BỘ SƯU TẬP ẢNH CHUNG & HOÀN TẤT LƯU -->
        <div>
            <!-- 3. BỘ SƯU TẬP ẢNH SẢN PHẨM CHUNG (TỐI ĐA 6 ẢNH) -->
            <div class="form-card">
                <div class="form-section-title">
                    <div class="form-section-title-left">
                        <div class="form-section-icon-wrap brown">
                            <i class="fa-solid fa-images"></i>
                        </div>
                        <div>
                            <div class="form-section-title-text">Bộ Ảnh Chung</div>
                            <div class="form-hint" style="margin-top: 2px;">Ảnh đại diện &amp; thư viện ngoài shop</div>
                        </div>
                    </div>
                    <span id="gallery-counter-badge" class="form-section-badge">
                        0 / 6 ảnh
                    </span>
                </div>

                <input type="file" id="general-file-input" multiple accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" style="display: none;" onchange="handleGeneralFiles(event)">

                <div class="upload-box-area" onclick="document.getElementById('general-file-input').click()">
                    <div class="upload-box-icon">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div style="font-size: 14px; font-weight: 800; color: var(--pf-brown-deep); margin-bottom: 4px;">
                        Bấm để tải thêm ảnh mới
                    </div>
                    <div style="font-size: 11.5px; color: var(--pf-brown-subtle); line-height: 1.4;">
                        Tối đa 6 ảnh (JPG, PNG, WEBP, GIF)<br>Ảnh đầu tiên sẽ là <strong>Ảnh đại diện</strong> chính.
                    </div>
                </div>

                <div class="gallery-preview-grid" id="general-gallery-grid">
                    <!-- Preview cards loaded via JS -->
                </div>
            </div>

            <!-- 4. HỘP HOÀN TẤT & LƯU -->
            <div class="form-card" style="background: var(--pf-beige-surface); border-color: var(--pf-beige-border);">
                <div style="margin-bottom: 1.25rem;">
                    <div style="font-weight: 900; font-size: 15px; color: var(--pf-brown-deep); margin-bottom: 4px;">
                        Hoàn tất cập nhật
                    </div>
                    <div style="font-size: 12px; color: var(--pf-brown-subtle); line-height: 1.4;">
                        Mọi thay đổi về phân loại con, ảnh và giá sẽ được lưu ngay lập tức.
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button type="submit" class="btn-gold-save" id="btn-submit" style="width: 100%; justify-content: center; padding: 12px; font-size: 14px;">
                        <i class="fa-solid fa-floppy-disk"></i> Cập Nhật Sản Phẩm
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn-soft-back" style="justify-content: center;">
                        Hủy Bỏ
                    </a>
                </div>
            </div>

            <!-- 5. MẸO NHẬP LIỆU CHUẨN -->
            <div class="info-tip-card">
                <h4><i class="fa-solid fa-lightbulb" style="color: var(--pf-gold-dark);"></i> Mẹo Quản Trị Chuẩn</h4>
                <ul>
                    <li><i class="fa-solid fa-check"></i> Ảnh có gắn sao "Ảnh chính" sẽ hiển thị ngoài danh mục và trang chủ.</li>
                    <li><i class="fa-solid fa-check"></i> Khoảng giá và tổng tồn kho của sản phẩm cha sẽ tự động tính toán đồng bộ theo các sản phẩm con.</li>
                    <li><i class="fa-solid fa-check"></i> Có thể dùng tính năng "Tạo nhanh" để tự động kết hợp các size và màu sắc thành ma trận.</li>
                </ul>
            </div>
        </div>

    </div>
</form>

<!-- MODAL CÀI ĐẶT THỜI GIAN KHUYẾN MÃI (SALE DATES) CHO BIẾN THỂ -->
<div class="modal-backdrop" id="sale-time-modal" style="display: none; z-index: 1060;">
    <div class="modal-box" style="max-width: 440px;">
        <div class="modal-header">
            <h3 class="modal-title" style="font-size: 16px;">
                <i class="fa-regular fa-clock" style="color: #E65100;"></i> Cài Đặt Thời Gian Khuyến Mãi
            </h3>
            <button type="button" class="btn-icon" onclick="closeSaleTimeModal()" style="border: none; background: transparent;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 1.25rem;">
            <div style="font-size: 13px; color: #5D4037; margin-bottom: 14px; font-weight: 700;" id="sale-modal-variant-title">
                Phân loại: 35cm - Nâu Socola
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label class="form-label" style="margin-bottom: 0;">
                        <i class="fa-regular fa-calendar-plus" style="color: #2E7D32;"></i> Ngày & Giờ Bắt Đầu Sale <span style="color: #C62828;">*</span>
                    </label>
                    <button type="button" class="btn-time-shortcut" onclick="setModalSaleStartNow()" title="Đặt tự động là giây phút hiện tại">
                        <i class="fa-solid fa-clock"></i> Đặt hiện tại
                    </button>
                </div>
                <input type="datetime-local" id="modal-sale-start" class="input-control">
                <div class="form-hint">Nếu thay đổi ngày bắt đầu, phải chọn từ thời điểm hiện tại trở đi đến tương lai.</div>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label class="form-label" style="margin-bottom: 0;">
                        <i class="fa-regular fa-calendar-xmark" style="color: #C62828;"></i> Ngày & Giờ Kết Thúc Sale <span style="color: #C62828;">*</span>
                    </label>
                    <div class="quick-end-presets">
                        <button type="button" class="btn-time-shortcut" onclick="setModalSaleEndHours(24)" title="Cộng thêm 24 giờ">+24h</button>
                        <button type="button" class="btn-time-shortcut" onclick="setModalSaleEndDays(3)" title="Cộng thêm 3 ngày">+3 ngày</button>
                        <button type="button" class="btn-time-shortcut" onclick="setModalSaleEndDays(7)" title="Cộng thêm 7 ngày">+7 ngày</button>
                    </div>
                </div>
                <input type="datetime-local" id="modal-sale-end" class="input-control">
                <div class="form-hint">Khi đang trong thời gian sale, sẽ có <strong>bộ đếm ngược thời gian</strong> trên trang chi tiết.</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="clearSaleTimes()" style="color: #C62828; border-color: #FFCDD2; margin-right: auto;">
                <i class="fa-solid fa-trash-can"></i> Xóa Thời Gian
            </button>
            <button type="button" class="btn btn-outline" onclick="closeSaleTimeModal()">Đóng</button>
            <button type="button" class="btn btn-primary" onclick="saveSaleTimeModal()" style="background: #8D6E63;">
                Xác Nhận
            </button>
        </div>
    </div>
</div>

<!-- MODAL TẠO NHANH & ÁP DỤNG HÀNG LOẠT (GỘP THÀNH 1 POPUP DUY NHẤT) -->
<div class="modal-backdrop" id="combined-bulk-modal" style="display: none; z-index: 1060;">
    <div class="modal-box" style="max-width: 480px;">
        <div class="modal-header">
            <h3 class="modal-title" style="font-size: 16px;">
                <i class="fa-solid fa-bolt" style="color: #E65100;"></i> Tạo Nhanh / Áp Dụng Hàng Loạt
            </h3>
            <button type="button" class="btn-icon" onclick="closeCombinedBulkModal()" style="border: none; background: transparent;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 1.25rem;">
            <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.5;">
                Nhập kích thước &amp; màu sắc để <strong>tự động tạo thêm danh sách phân loại con</strong>, hoặc chỉ nhập giá &amp; tồn kho để <strong>áp dụng đồng loạt cho các dòng hiện có</strong>:
            </p>

            <!-- 1. Danh sách Kích thước -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-ruler-combined" style="color: #8D6E63;"></i> Danh sách Kích thước (phẩy cách nhau)
                </label>
                <input type="text" id="comb-sizes" class="input-control" placeholder="30cm, 40cm, 80cm...">
            </div>

            <!-- 2. Danh sách Màu sắc -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-palette" style="color: #8D6E63;"></i> Danh sách Màu sắc (phẩy cách nhau)
                </label>
                <input type="text" id="comb-colors" class="input-control" placeholder="Nâu socola, Vàng bơ, Trắng kem...">
            </div>

            <div style="border-top: 1px dashed var(--pf-beige-border); margin: 12px 0;"></div>

            <!-- 3. Giá Gốc & Giá Khuyến Mãi -->
            <div class="form-grid-2" style="margin-bottom: 12px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Giá Gốc (VNĐ)</label>
                    <input type="text" inputmode="numeric" id="comb-price" class="input-control" placeholder="550.000" onblur="if(this.value.trim()) this.value = formatCurrencyString(this.value)" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Giá Khuyến Mãi (VNĐ)</label>
                    <input type="text" inputmode="numeric" id="comb-sale-price" class="input-control" placeholder="360.000" onblur="if(this.value.trim()) this.value = formatCurrencyString(this.value)" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}">
                </div>
            </div>

            <!-- 4. Hẹn Giờ Khuyến Mãi -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label class="form-label" style="margin-bottom: 0;">
                        <i class="fa-regular fa-calendar-plus" style="color: #2E7D32;"></i> Ngày Bắt Đầu Sale
                    </label>
                    <button type="button" class="btn-time-shortcut" onclick="setCombinedSaleStartNow()" title="Đặt thời điểm hiện tại">
                        <i class="fa-solid fa-clock"></i> Đặt hiện tại
                    </button>
                </div>
                <input type="datetime-local" id="comb-sale-start" class="input-control">
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label class="form-label" style="margin-bottom: 0;">
                        <i class="fa-regular fa-calendar-xmark" style="color: #C62828;"></i> Ngày Kết Thúc Sale
                    </label>
                    <div class="quick-end-presets">
                        <button type="button" class="btn-time-shortcut" onclick="setCombinedSaleEndHours(24)">+24h</button>
                        <button type="button" class="btn-time-shortcut" onclick="setCombinedSaleEndDays(3)">+3 ngày</button>
                        <button type="button" class="btn-time-shortcut" onclick="setCombinedSaleEndDays(7)">+7 ngày</button>
                    </div>
                </div>
                <input type="datetime-local" id="comb-sale-end" class="input-control">
            </div>

            <!-- 5. Tồn Kho -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Số Lượng Tồn Kho</label>
                <input type="number" id="comb-stock" class="input-control" placeholder="20">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeCombinedBulkModal()">Đóng</button>
            <button type="button" class="btn btn-primary" onclick="executeCombinedBulkApply()" style="background: #E65100; border-color: #E65100;">
                <i class="fa-solid fa-sparkles"></i> Áp Dụng Ngay
            </button>
        </div>
    </div>
</div>

<script>
    // ==========================================
    // INITIAL SERVER DATA
    // ==========================================
    const MAX_GENERAL_IMAGES = 6;

    // Ảnh cũ đã có trong CSDL: [{ id, url, is_primary }]
    let existingImages = [
        @foreach($product->images as $img)
        {
            id: {{ $img->id }},
            url: "{{ $img->image_url }}",
            is_primary: {{ $img->is_primary ? 'true' : 'false' }}
        },
        @endforeach
    ];

    // File ảnh mới chọn từ máy tính: [{ file, previewUrl, is_primary }]
    let newFiles = [];

    // Danh sách biến thể từ CSDL
    let variantsList = [
        @foreach($product->variants as $v)
        {
            id: {{ $v->id }},
            sku: "{{ addslashes($v->sku ?? '') }}",
            size: "{{ addslashes($v->size ?? '') }}",
            color: "{{ addslashes($v->color ?? '') }}",
            price: {{ (float)$v->price }},
            sale_price: {{ $v->sale_price !== null ? (float)$v->sale_price : 'null' }},
            sale_start_at: "{{ $v->sale_start_at ? $v->sale_start_at->format('Y-m-d\TH:i') : '' }}",
            sale_end_at: "{{ $v->sale_end_at ? $v->sale_end_at->format('Y-m-d\TH:i') : '' }}",
            original_sale_start_at: "{{ $v->sale_start_at ? $v->sale_start_at->format('Y-m-d\TH:i') : '' }}",
            original_sale_end_at: "{{ $v->sale_end_at ? $v->sale_end_at->format('Y-m-d\TH:i') : '' }}",
            stock_quantity: {{ (int)$v->stock_quantity }},
            image_url: "{{ addslashes($v->image_url ?? '') }}",
            file: null,
            is_default: {{ $v->is_default ? 'true' : 'false' }},
            status: "{{ $v->status ?? 'ACTIVE' }}"
        },
        @endforeach
    ];

    let currentSaleModalIndex = null;

    // Helper định dạng ngày giờ cho input datetime-local (YYYY-MM-DDTHH:mm)
    function formatDateTimeLocal(d) {
        const pad = (n) => String(n).padStart(2, '0');
        const year = d.getFullYear();
        const month = pad(d.getMonth() + 1);
        const day = pad(d.getDate());
        const hours = pad(d.getHours());
        const minutes = pad(d.getMinutes());
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    // Các hàm đặt nhanh thời gian cho Modal biến thể
    function setModalSaleStartNow() {
        const now = new Date();
        document.getElementById('modal-sale-start').value = formatDateTimeLocal(now);
    }

    function setModalSaleEndHours(h) {
        const startVal = document.getElementById('modal-sale-start').value;
        const base = startVal ? new Date(startVal) : new Date();
        const end = new Date(base.getTime() + h * 3600 * 1000);
        document.getElementById('modal-sale-end').value = formatDateTimeLocal(end);
    }

    function setModalSaleEndDays(d) {
        setModalSaleEndHours(d * 24);
    }

    // Các hàm đặt nhanh thời gian cho Modal Áp dụng hàng loạt
    function setBulkSaleStartNow() {
        const now = new Date();
        document.getElementById('bulk-sale-start').value = formatDateTimeLocal(now);
    }

    function setBulkSaleEndHours(h) {
        const startVal = document.getElementById('bulk-sale-start').value;
        const base = startVal ? new Date(startVal) : new Date();
        const end = new Date(base.getTime() + h * 3600 * 1000);
        document.getElementById('bulk-sale-end').value = formatDateTimeLocal(end);
    }

    function setBulkSaleEndDays(d) {
        setBulkSaleEndHours(d * 24);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Đảm bảo có ít nhất 1 biến thể
        if (!variantsList.length) {
            variantsList = [{
                id: null,
                sku: '{{ "PRD" . $product->id . "-DEFAULT" }}',
                size: '{{ addslashes($product->size ?: "Tiêu chuẩn") }}',
                color: '{{ addslashes($product->color ?: "Tự nhiên") }}',
                price: {{ (float)$product->price }},
                sale_price: {{ $product->sale_price ? (float)$product->sale_price : 'null' }},
                sale_start_at: '',
                sale_end_at: '',
                original_sale_start_at: '',
                original_sale_end_at: '',
                stock_quantity: {{ (int)$product->stock_quantity }},
                image_url: '',
                file: null,
                is_default: true,
                status: 'ACTIVE'
            }];
        }

        renderGeneralGallery();
        renderVariantsTable();
    });

    // ==========================================
    // GENERAL IMAGES GALLERY
    // ==========================================
    function handleGeneralFiles(e) {
        const files = Array.from(e.target.files).filter(f => f.type.startsWith('image/'));
        if (!files.length) return;

        const total = existingImages.length + newFiles.length + files.length;
        if (total > MAX_GENERAL_IMAGES) {
            const slots = MAX_GENERAL_IMAGES - (existingImages.length + newFiles.length);
            Swal.fire('Giới hạn ảnh', `Tối đa chỉ được tải 6 ảnh cho mỗi sản phẩm (còn trống ${Math.max(0, slots)} ảnh).`, 'warning');
            return;
        }

        files.forEach(f => {
            const isFirst = (existingImages.length === 0 && newFiles.length === 0);
            newFiles.push({
                file: f,
                previewUrl: URL.createObjectURL(f),
                is_primary: isFirst
            });
        });

        renderGeneralGallery();
        e.target.value = '';
    }

    function renderGeneralGallery() {
        const grid = document.getElementById('general-gallery-grid');
        const badge = document.getElementById('gallery-counter-badge');
        const total = existingImages.length + newFiles.length;
        badge.innerText = `${total} / ${MAX_GENERAL_IMAGES} ảnh`;

        if (!total) {
            grid.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; color: var(--text-light); font-size: 11.5px; padding: 1rem; border: 1px dashed var(--border); border-radius: 8px;">Chưa có ảnh nào</div>`;
            return;
        }

        let html = '';

        // Render existing images
        existingImages.forEach((img, idx) => {
            const isFromVariant = variantsList.some(v => v.image_url && v.image_url === img.url);
            html += `
                <div class="preview-card ${img.is_primary ? 'is-primary' : ''}">
                    <img src="${img.url}" class="preview-thumb" alt="Ảnh cũ">
                    <div class="preview-actions">
                        <button type="button" class="btn-badge-primary" onclick="setPrimaryExisting(${idx})">
                            ${img.is_primary ? '<i class="fa-solid fa-star"></i> Bìa' : 'Chọn bìa'}
                        </button>
                        <button type="button" class="btn-del-thumb" onclick="removeExistingImage(${idx})" title="${isFromVariant ? 'Ảnh của sản phẩm con (không thể xóa tại đây)' : 'Xóa ảnh này'}" style="${isFromVariant ? 'background: rgba(121, 85, 72, 0.9);' : ''}">
                            <i class="${isFromVariant ? 'fa-solid fa-lock' : 'fa-solid fa-trash-can'}"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        // Render new files
        newFiles.forEach((item, idx) => {
            const isFromVariant = !!item.sourceVariantUid;
            html += `
                <div class="preview-card ${item.is_primary ? 'is-primary' : ''}">
                    <img src="${item.previewUrl}" class="preview-thumb" alt="Ảnh mới">
                    <div class="preview-actions">
                        <button type="button" class="btn-badge-primary" onclick="setPrimaryNew(${idx})">
                            ${item.is_primary ? '<i class="fa-solid fa-star"></i> Bìa' : 'Chọn bìa'}
                        </button>
                        <button type="button" class="btn-del-thumb" onclick="removeNewFile(${idx})" title="${isFromVariant ? 'Ảnh của sản phẩm con (không thể xóa tại đây)' : 'Bỏ ảnh này'}" style="${isFromVariant ? 'background: rgba(121, 85, 72, 0.9);' : ''}">
                            <i class="${isFromVariant ? 'fa-solid fa-lock' : 'fa-solid fa-trash-can'}"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;
    }

    function setPrimaryExisting(idx) {
        existingImages.forEach((img, i) => img.is_primary = (i === idx));
        newFiles.forEach(f => f.is_primary = false);
        renderGeneralGallery();
    }

    function setPrimaryNew(idx) {
        existingImages.forEach(img => img.is_primary = false);
        newFiles.forEach((f, i) => f.is_primary = (i === idx));
        renderGeneralGallery();
    }

    function removeExistingImage(idx) {
        const item = existingImages[idx];
        const isFromVariant = variantsList.some(v => v.image_url && v.image_url === item.url);
        if (isFromVariant) {
            Swal.fire({
                icon: 'warning',
                title: 'Bé Gấu nhắc bạn nè! 🐻',
                html: 'Không thể xóa vì đây là ảnh của sản phẩm con!<br><span style="font-size: 12.5px; color: #8D6E63;">(Bạn chỉ xóa được những ảnh vừa thêm tại mục Bộ Ảnh Chung thôi nha)</span>',
                confirmButtonColor: '#8D6E63'
            });
            return;
        }
        const wasPrimary = existingImages[idx].is_primary;
        existingImages.splice(idx, 1);
        if (wasPrimary) {
            if (existingImages.length > 0) existingImages[0].is_primary = true;
            else if (newFiles.length > 0) newFiles[0].is_primary = true;
        }
        renderGeneralGallery();
    }

    function removeNewFile(idx) {
        const item = newFiles[idx];
        if (item && item.sourceVariantUid) {
            Swal.fire({
                icon: 'warning',
                title: 'Bé Gấu nhắc bạn nè! 🐻',
                html: 'Không thể xóa vì đây là ảnh của sản phẩm con!<br><span style="font-size: 12.5px; color: #8D6E63;">(Bạn chỉ xóa được những ảnh vừa thêm tại mục Bộ Ảnh Chung thôi nha)</span>',
                confirmButtonColor: '#8D6E63'
            });
            return;
        }
        const wasPrimary = newFiles[idx].is_primary;
        newFiles.splice(idx, 1);
        if (wasPrimary) {
            if (existingImages.length > 0) existingImages[0].is_primary = true;
            else if (newFiles.length > 0) newFiles[0].is_primary = true;
        }
        renderGeneralGallery();
    }

    // ==========================================
    // ==========================================
    // CURRENCY FORMATTING HELPERS
    // ==========================================
    function formatCurrencyString(val) {
        if (val === null || val === undefined || val === '') return '';
        const digits = String(val).replace(/\D/g, '');
        if (!digits) return '';
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function parseCurrencyToNumber(val) {
        if (val === null || val === undefined || val === '') return 0;
        const digits = String(val).replace(/\D/g, '');
        return digits ? parseFloat(digits) : 0;
    }

    function handleSizeBlur(idx, input) {
        if (!variantsList[idx]) return;
        const val = input.value.trim();
        if (!val) {
            input.style.borderColor = '';
            input.style.backgroundColor = '';
            return;
        }
        if (!/^\d+(\.\d+)?cm$/i.test(val)) {
            input.style.borderColor = '#E53935';
            input.style.backgroundColor = '#FFEBEE';
            Swal.fire({
                icon: 'warning',
                title: 'Kích thước không hợp lệ',
                html: `Kích thước <b>"${escapeHtml(val)}"</b> không đúng định dạng!<br><br>Kích thước bắt buộc phải là số kèm đơn vị <b>"cm"</b> viết liền nhau (ví dụ: <b>30cm, 45cm</b>).<br><span style="color:#C62828;">(Dạng có khoảng trắng như <i>45 cm</i> hoặc thiếu chữ <i>cm</i> đều bị lỗi)</span>.`,
                confirmButtonColor: '#8D6E63'
            });
        } else {
            input.style.borderColor = '';
            input.style.backgroundColor = '';
            variantsList[idx].size = val;
        }
    }

    function handlePriceBlur(idx, field, input) {
        if (!variantsList[idx]) return;
        const raw = input.value.trim();
        if (!raw) {
            input.value = '';
            variantsList[idx][field] = (field === 'sale_price' ? null : 0);
        } else {
            const num = parseCurrencyToNumber(raw);
            input.value = formatCurrencyString(num);
            variantsList[idx][field] = num;
        }
        updateSummaryStats();
    }

    function handlePriceKeydown(e, input) {
        if (e.key === 'Enter') {
            e.preventDefault();
            input.blur();
        }
    }

    function toggleParentFormStatus() {
        const input = document.getElementById('prod-status');
        const track = document.getElementById('parent-status-track');
        const label = document.getElementById('parent-status-label');
        const isNowActive = (input.value !== 'ACTIVE');
        input.value = isNowActive ? 'ACTIVE' : 'INACTIVE';
        track.classList.toggle('active', isNowActive);
        label.innerText = isNowActive ? 'Đang kinh doanh (Bật)' : 'Tạm ẩn (Tắt)';
        label.style.color = isNowActive ? '#10B981' : '#8D6E63';
    }

    function toggleVariantStatusRow(idx) {
        if (!variantsList[idx]) return;
        variantsList[idx].status = (variantsList[idx].status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE');
        renderVariantsTable();
    }

    // ==========================================
    // VARIANTS TABLE RENDERING & ACTIONS
    // ==========================================
    function renderVariantsTable() {
        const tbody = document.getElementById('variants-tbody');
        if (!variantsList.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" style="text-align: center; padding: 2.5rem; color: #8D6E63;">
                        <i class="fa-solid fa-layer-group" style="font-size: 28px; color: #BCAAA4; margin-bottom: 8px;"></i>
                        <div>Chưa có sản phẩm con nào. Hãy bấm <strong>"+ Thêm 1 sản phẩm con"</strong> hoặc <strong>"⚡ Tạo Nhanh Theo Size & Màu"</strong>.</div>
                    </td>
                </tr>
            `;
            updateSummaryStats();
            return;
        }

        tbody.innerHTML = variantsList.map((v, idx) => {
            let timeBtnLabel = '<i class="fa-regular fa-clock"></i> Lịch sale';
            let timeBtnClass = '';

            if (v.sale_start_at || v.sale_end_at) {
                const now = new Date();
                const start = v.sale_start_at ? new Date(v.sale_start_at) : null;
                const end = v.sale_end_at ? new Date(v.sale_end_at) : null;

                if (start && now < start) {
                    timeBtnClass = 'upcoming';
                    timeBtnLabel = '<i class="fa-solid fa-bolt"></i> Sắp sale';
                } else if ((!start || now >= start) && (!end || now <= end)) {
                    timeBtnClass = 'active';
                    timeBtnLabel = '<i class="fa-solid fa-fire"></i> Đang sale';
                } else {
                    timeBtnLabel = '<i class="fa-solid fa-check"></i> Đã cài đặt';
                }
            }

            // Thumbnail display
            const hasVariantImg = !!(v.file || (v.image_url && !v.image_url.includes('placehold.co')));
            let thumbSrc = hasVariantImg ? v.image_url : 'https://placehold.co/100x100/fdf6e2/8d6e63?text=%2B+%E1%BA%A2nh*';

            const formattedPrice = formatCurrencyString(v.price);
            const formattedSalePrice = v.sale_price ? formatCurrencyString(v.sale_price) : '';
            const isRowActive = (v.status === 'ACTIVE');

            return `
                <tr class="${v.is_default ? 'is-default-row' : ''}">
                    <td style="text-align: center;">
                        <input type="file" id="var-file-${idx}" accept="image/*" style="display:none;" onchange="handleVariantFileSelect(${idx}, event)">
                        <div class="variant-thumb-wrap" onclick="triggerVariantFile(${idx})" title="${hasVariantImg ? 'Bấm để đổi ảnh riêng cho phân loại này' : 'Bắt buộc: Bấm để chọn ảnh cho phân loại này'}" style="${!hasVariantImg ? 'border: 1.5px dashed #E53935; background: #FFEBEE;' : ''}">
                            <img src="${thumbSrc}" class="variant-thumb-img" id="var-img-preview-${idx}">
                            <div class="variant-thumb-overlay">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                        </div>
                    </td>
                    <td>
                        <input type="text" class="v-input" value="${escapeHtml(v.size || '')}" placeholder="30cm, 40cm,..." onblur="handleSizeBlur(${idx}, this)" oninput="updateVariantField(${idx}, 'size', this.value)" required>
                    </td>
                    <td>
                        <input type="text" class="v-input" value="${escapeHtml(v.color || '')}" placeholder="Nâu socola, Vàng bơ,..." oninput="updateVariantField(${idx}, 'color', this.value)" required>
                    </td>
                    <td>
                        <input type="text" inputmode="numeric" class="v-input v-input-num" value="${formattedPrice}" placeholder="550.000" onblur="handlePriceBlur(${idx}, 'price', this)" onkeydown="handlePriceKeydown(event, this)" required>
                    </td>
                    <td>
                        <input type="text" inputmode="numeric" class="v-input v-input-num" value="${formattedSalePrice}" placeholder="360.000" onblur="handlePriceBlur(${idx}, 'sale_price', this)" onkeydown="handlePriceKeydown(event, this)">
                        <div style="text-align: left;">
                            <button type="button" class="variant-time-btn ${timeBtnClass}" onclick="openSaleTimeModal(${idx})" title="Cài đặt ngày giờ bắt đầu và kết thúc khuyến mãi">
                                ${timeBtnLabel}
                            </button>
                        </div>
                    </td>
                    <td>
                        <input type="number" class="v-input v-input-num" value="${v.stock_quantity !== '' && v.stock_quantity !== null && v.stock_quantity !== undefined ? v.stock_quantity : ''}" min="0" placeholder="20" oninput="updateVariantField(${idx}, 'stock_quantity', this.value)" required>
                    </td>
                    <td style="text-align: center;">
                        <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px;">
                            <div class="switch-toggle-box" onclick="toggleVariantStatusRow(${idx})" title="Bấm để ${isRowActive ? 'tạm tắt' : 'bật'} phân loại này">
                                <div class="switch-toggle-track ${isRowActive ? 'active' : ''}">
                                    <span class="switch-toggle-thumb"></span>
                                </div>
                            </div>
                            <span style="font-size: 11px; font-weight: 700; color: ${isRowActive ? '#10B981' : '#8D6E63'};">
                                ${isRowActive ? 'Bật' : 'Tắt'}
                            </span>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <input type="radio" name="default_variant" class="variant-radio-default" ${v.is_default ? 'checked' : ''} onchange="setDefaultVariant(${idx})" title="Chọn làm phân loại mặc định">
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-icon delete" style="width: 26px; height: 26px; border-radius: 5px;" onclick="removeVariantRow(${idx})" title="Xóa phân loại này">
                            <i class="fa-solid fa-trash-can" style="font-size: 11px; pointer-events: none;"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        updateSummaryStats();
    }

    function escapeHtml(str) {
        return (str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function updateVariantField(idx, field, val) {
        if (!variantsList[idx]) return;
        variantsList[idx][field] = val;
        if (field === 'size') {
            const inputEl = document.querySelector(`input[onblur*="handleSizeBlur(${idx}"]`);
            if (inputEl && /^\d+(\.\d+)?cm$/i.test(val.trim())) {
                inputEl.style.borderColor = '';
                inputEl.style.backgroundColor = '';
            }
        }
        if (field === 'price' || field === 'stock_quantity' || field === 'sale_price') {
            updateSummaryStats();
        }
    }

    function setDefaultVariant(idx) {
        variantsList.forEach((v, i) => v.is_default = (i === idx));
        renderVariantsTable();
    }

    function addNewVariantRow() {
        const prodName = document.getElementById('prod-name').value.trim();
        const nextIdx = variantsList.length + 1;
        const prefix = prodName ? prodName.split(' ')[0].toUpperCase() : 'BEAR';

        variantsList.push({
            id: null,
            _uid: 'var_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
            sku: `${prefix}-${nextIdx}`,
            size: '',
            color: '',
            price: '',
            sale_price: null,
            sale_start_at: '',
            sale_end_at: '',
            original_sale_start_at: '',
            original_sale_end_at: '',
            stock_quantity: '',
            image_url: '',
            file: null,
            is_default: variantsList.length === 0,
            status: 'ACTIVE'
        });

        renderVariantsTable();
    }

    function removeVariantRow(idx) {
        if (variantsList.length <= 1) {
            Swal.fire('Cảnh báo', 'Mỗi sản phẩm phải có ít nhất một phân loại con!', 'warning');
            return;
        }

        const vUid = variantsList[idx]._uid;
        const wasDefault = variantsList[idx].is_default;
        variantsList.splice(idx, 1);
        if (wasDefault && variantsList.length > 0) {
            variantsList[0].is_default = true;
        }

        // Tự động đồng bộ xóa ảnh tương ứng trong newFiles nếu có
        if (vUid) {
            const syncedIdx = newFiles.findIndex(item => item.sourceVariantUid === vUid);
            if (syncedIdx !== -1) {
                removeNewFile(syncedIdx);
            }
        }

        renderVariantsTable();
    }

    function triggerVariantFile(idx) {
        document.getElementById(`var-file-${idx}`).click();
    }

    function handleVariantFileSelect(idx, e) {
        const file = e.target.files[0];
        if (!file) return;

        const v = variantsList[idx];
        if (!v) return;

        v._uid = v._uid || ('var_' + (v.id || idx) + '_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7));
        v.file = file;
        const previewUrl = URL.createObjectURL(file);
        v.image_url = previewUrl;

        const imgEl = document.getElementById(`var-img-preview-${idx}`);
        if (imgEl) {
            imgEl.src = previewUrl;
            const thumbWrap = imgEl.closest('.variant-thumb-wrap');
            if (thumbWrap) {
                thumbWrap.style.border = '';
                thumbWrap.style.background = '';
            }
        }

        // YÊU CẦU 5: Tự động đưa ảnh vừa thêm vào "Bộ Ảnh Chung"
        // Nếu thay ảnh sản phẩm con 1 thì nó cũng tự động đồng bộ sửa lại bộ ảnh chung, thay ảnh cũ là ảnh mới
        const existingGalleryIdx = newFiles.findIndex(item => item.sourceVariantUid === v._uid);
        if (existingGalleryIdx !== -1) {
            const wasPrimary = newFiles[existingGalleryIdx].is_primary;
            newFiles[existingGalleryIdx] = {
                file: file,
                previewUrl: previewUrl,
                is_primary: wasPrimary,
                sourceVariantUid: v._uid
            };
        } else {
            const total = existingImages.length + newFiles.length;
            if (total < MAX_GENERAL_IMAGES) {
                const isFirst = (existingImages.length === 0 && newFiles.length === 0);
                newFiles.push({
                    file: file,
                    previewUrl: previewUrl,
                    is_primary: isFirst,
                    sourceVariantUid: v._uid
                });
            } else {
                let replaceIdx = newFiles.findIndex(item => !item.is_primary);
                if (replaceIdx !== -1) {
                    newFiles[replaceIdx] = {
                        file: file,
                        previewUrl: previewUrl,
                        is_primary: false,
                        sourceVariantUid: v._uid
                    };
                }
            }
        }

        renderGeneralGallery();
    }

    function handleSizeBlur(idx, input) {
        if (!variantsList[idx]) return;
        const raw = input.value;
        if (!raw.trim()) {
            input.style.borderColor = '';
            input.style.backgroundColor = '';
            return;
        }
        // Bắt buộc dạng số kèm "cm" viết liền (vd 45cm), có khoảng trắng (vd 45 cm) bị bắt lỗi
        const sizeRegex = /^\d+(\.\d+)?cm$/i;
        if (!sizeRegex.test(raw.trim())) {
            input.style.borderColor = '#D32F2F';
            input.style.backgroundColor = '#FFEBEE';
            Swal.fire({
                icon: 'warning',
                title: 'Kích thước không hợp lệ',
                html: `Kích thước <b>"${escapeHtml(raw)}"</b> không đúng định dạng!<br><br>Kích thước bắt buộc phải là số kèm đơn vị <b>"cm"</b> viết liền (ví dụ: <b>30cm, 45cm</b>).<br><span style="color:#C62828;">(Dạng có khoảng trắng như <i>45 cm</i> hoặc thiếu <i>cm</i> đều không hợp lệ)</span>.`,
                confirmButtonColor: '#8D6E63'
            });
        } else {
            input.style.borderColor = '';
            input.style.backgroundColor = '';
        }
    }

    function updateSummaryStats() {
        document.getElementById('sum-count').innerText = `${variantsList.length} phân loại`;

        const prices = variantsList.map(v => parseFloat(v.price) || 0).filter(p => p > 0);
        if (prices.length > 0) {
            const minP = Math.min(...prices);
            const maxP = Math.max(...prices);
            if (minP === maxP) {
                document.getElementById('sum-price').innerText = new Intl.NumberFormat('vi-VN').format(minP) + ' đ';
            } else {
                document.getElementById('sum-price').innerText = `${new Intl.NumberFormat('vi-VN').format(minP)} đ - ${new Intl.NumberFormat('vi-VN').format(maxP)} đ`;
            }
        } else {
            document.getElementById('sum-price').innerText = '0 đ';
        }

        const totalStock = variantsList.reduce((acc, v) => acc + (parseInt(v.stock_quantity) || 0), 0);
        document.getElementById('sum-stock').innerText = `${new Intl.NumberFormat('vi-VN').format(totalStock)} chiếc`;
    }

    function autoSuggestSKUs() {
        const prodName = document.getElementById('prod-name').value.trim();
        if (!prodName) return;
        const words = prodName.split(/\s+/).map(w => w[0] ? w[0].toUpperCase() : '').join('').slice(0, 5);

        variantsList.forEach((v, idx) => {
            if (!v.sku || v.sku.startsWith('BEAR') || v.sku.startsWith('PRD')) {
                const s = v.size ? v.size.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : idx + 1;
                v.sku = `${words}-${s}`;
            }
        });
    }

    // ==========================================
    // CÁC HÀM HỖ TRỢ THỜI GIAN NHANH (SHORTCUTS)
    // ==========================================
    function formatDateTimeLocal(d) {
        const pad = (n) => String(n).padStart(2, '0');
        const year = d.getFullYear();
        const month = pad(d.getMonth() + 1);
        const day = pad(d.getDate());
        const hours = pad(d.getHours());
        const minutes = pad(d.getMinutes());
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    function setModalSaleStartNow() {
        const now = new Date();
        document.getElementById('modal-sale-start').value = formatDateTimeLocal(now);
    }

    function setModalSaleEndHours(h) {
        const startVal = document.getElementById('modal-sale-start').value;
        const base = startVal ? new Date(startVal) : new Date();
        const end = new Date(base.getTime() + h * 3600 * 1000);
        document.getElementById('modal-sale-end').value = formatDateTimeLocal(end);
    }

    function setModalSaleEndDays(d) {
        const startVal = document.getElementById('modal-sale-start').value;
        const base = startVal ? new Date(startVal) : new Date();
        const end = new Date(base.getTime() + d * 24 * 3600 * 1000);
        document.getElementById('modal-sale-end').value = formatDateTimeLocal(end);
    }

    function setCombinedSaleStartNow() {
        const now = new Date();
        document.getElementById('comb-sale-start').value = formatDateTimeLocal(now);
    }

    function setCombinedSaleEndHours(h) {
        const startVal = document.getElementById('comb-sale-start').value;
        const base = startVal ? new Date(startVal) : new Date();
        const end = new Date(base.getTime() + h * 3600 * 1000);
        document.getElementById('comb-sale-end').value = formatDateTimeLocal(end);
    }

    function setCombinedSaleEndDays(d) {
        const startVal = document.getElementById('comb-sale-start').value;
        const base = startVal ? new Date(startVal) : new Date();
        const end = new Date(base.getTime() + d * 24 * 3600 * 1000);
        document.getElementById('comb-sale-end').value = formatDateTimeLocal(end);
    }

    // ==========================================
    // TẠO NHANH / ÁP DỤNG HÀNG LOẠT (POPUP KẾT HỢP)
    // ==========================================
    function openCombinedBulkModal() {
        const m = document.getElementById('combined-bulk-modal');
        m.style.display = 'flex';
        m.classList.add('show');
    }

    function closeCombinedBulkModal() {
        const m = document.getElementById('combined-bulk-modal');
        m.classList.remove('show');
        setTimeout(() => m.style.display = 'none', 200);
    }

    function executeCombinedBulkApply() {
        const sizesRaw = document.getElementById('comb-sizes').value.trim();
        const colorsRaw = document.getElementById('comb-colors').value.trim();
        const pRaw = document.getElementById('comb-price').value.trim();
        const spRaw = document.getElementById('comb-sale-price').value.trim();
        const stRaw = document.getElementById('comb-stock').value.trim();
        const start = document.getElementById('comb-sale-start').value;
        const end = document.getElementById('comb-sale-end').value;

        const p = pRaw ? parseCurrencyToNumber(pRaw) : '';
        const sp = spRaw ? parseCurrencyToNumber(spRaw) : '';
        const st = stRaw !== '' ? parseInt(stRaw) : '';

        if (sp !== '' && parseFloat(sp) > 0) {
            if (p !== '' && parseFloat(sp) >= parseFloat(p)) {
                Swal.fire('Giá sale không hợp lệ', 'Giá khuyến mãi phải nhỏ hơn giá gốc!', 'warning');
                return;
            }
            if (!start || !end) {
                Swal.fire('Thiếu thời gian khuyến mãi', 'Khi nhập Giá Khuyến Mãi, BẮT BUỘC phải nhập cả Ngày bắt đầu và Ngày kết thúc sale!', 'warning');
                return;
            }
        }

        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            const nowBuffer = new Date(Date.now() - 60000);

            if (startDate < nowBuffer) {
                Swal.fire('Thời gian không hợp lệ', 'Ngày bắt đầu sale phải từ thời điểm hiện tại trở đi đến tương lai (không chọn quá khứ)!', 'warning');
                return;
            }
            if (endDate <= startDate) {
                Swal.fire('Thời gian không hợp lệ', 'Ngày kết thúc sale phải diễn ra sau ngày bắt đầu!', 'warning');
                return;
            }
        }

        const sizes = sizesRaw ? sizesRaw.split(/[,;\n]/).map(s => s.trim()).filter(Boolean) : [];
        const colors = colorsRaw ? colorsRaw.split(/[,;\n]/).map(c => c.trim()).filter(Boolean) : [];

        // Validate định dạng cm viết liền cho toàn bộ kích thước
        if (sizes.length > 0) {
            for (let s of sizes) {
                if (!/^\d+(\.\d+)?cm$/i.test(s)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kích thước không hợp lệ',
                        html: `Kích thước <b>"${escapeHtml(s)}"</b> không đúng định dạng!<br><br>Kích thước bắt buộc phải là số kèm đơn vị <b>"cm"</b> viết liền (ví dụ: <b>30cm, 45cm</b>).<br><span style="color:#C62828;">(Dạng có khoảng trắng như <i>45 cm</i> hoặc thiếu <i>cm</i> đều không hợp lệ)</span>.`,
                        confirmButtonColor: '#8D6E63'
                    });
                    return;
                }
            }
        }

        // TRƯỜNG HỢP 1: CÓ NHẬP KÍCH THƯỚC HOẶC MÀU SẮC -> TẠO TỔ HỢP BIẾN THỂ MỚI
        if (sizes.length > 0 || colors.length > 0) {
            const prodName = document.getElementById('prod-name').value.trim();
            const prefix = prodName ? prodName.split(/\s+/).map(w => w[0] ? w[0].toUpperCase() : '').join('').slice(0, 4) : 'BEAR';

            const listSizes = sizes.length > 0 ? sizes : [''];
            const listColors = colors.length > 0 ? colors : [''];
            const newVariants = [];
            let isFirst = true;

            listSizes.forEach(sz => {
                listColors.forEach(cl => {
                    const szCode = sz ? sz.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : 'STD';
                    const clCode = cl ? cl.split(/\s+/).map(w => w[0] ? w[0].toUpperCase() : '').join('') : 'DEF';

                    newVariants.push({
                        id: null,
                        _uid: 'var_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
                        sku: `${prefix}-${szCode}-${clCode}`,
                        size: sz,
                        color: cl,
                        price: p !== '' ? p : '',
                        sale_price: sp !== '' ? sp : null,
                        sale_start_at: start || '',
                        sale_end_at: end || '',
                        original_sale_start_at: '',
                        original_sale_end_at: '',
                        stock_quantity: st !== '' ? st : '',
                        image_url: '',
                        file: null,
                        is_default: isFirst,
                        status: 'ACTIVE'
                    });
                    isFirst = false;
                });
            });

            variantsList = newVariants;
            renderVariantsTable();
            closeCombinedBulkModal();

            Swal.fire({
                icon: 'success',
                title: 'Tạo thành công!',
                text: `Đã tạo ${newVariants.length} phân loại sản phẩm con (${listSizes.filter(Boolean).length || 1} Size × ${listColors.filter(Boolean).length || 1} Màu) kèm giá & tồn kho!`,
                timer: 1800,
                showConfirmButton: false
            });
            return;
        }

        // TRƯỜNG HỢP 2: KHÔNG NHẬP SIZE/MÀU MÀ NHẬP GIÁ/TỒN KHO -> ÁP DỤNG HÀNG LOẠT CHO CÁC DÒNG HIỆN CÓ
        if (p !== '' || sp !== '' || st !== '' || (start && end)) {
            if (!variantsList.length) {
                Swal.fire('Chưa có sản phẩm con', 'Bảng chưa có phân loại con nào để áp dụng!', 'warning');
                return;
            }

            let applied = 0;
            variantsList.forEach(v => {
                if (p !== '') v.price = parseFloat(p);
                if (sp !== '') {
                    v.sale_price = sp ? parseFloat(sp) : null;
                    if (start) v.sale_start_at = start;
                    if (end) v.sale_end_at = end;
                }
                if (st !== '') v.stock_quantity = parseInt(st);
                applied++;
            });

            renderVariantsTable();
            closeCombinedBulkModal();

            Swal.fire({
                icon: 'success',
                title: 'Đã cập nhật!',
                text: `Đã áp dụng hàng loạt thông số mới cho ${applied} phân loại con!`,
                timer: 1600,
                showConfirmButton: false
            });
            return;
        }

        Swal.fire('Chưa có dữ liệu', 'Vui lòng nhập kích thước, màu sắc để tạo mới hoặc giá/tồn kho để áp dụng hàng loạt!', 'info');
    }

    // ==========================================
    // MODAL HẸN GIỜ SALE CHO BIẾN THỂ
    // ==========================================
    function openSaleTimeModal(idx) {
        currentSaleModalIndex = idx;
        const v = variantsList[idx];
        document.getElementById('sale-modal-variant-title').innerText = `Phân loại: ${v.size || 'Size chuẩn'} - ${v.color || 'Màu sắc'}`;
        
        const inputStart = document.getElementById('modal-sale-start');
        const inputEnd = document.getElementById('modal-sale-end');
        
        inputStart.value = v.sale_start_at ? v.sale_start_at.slice(0, 16) : '';
        inputEnd.value = v.sale_end_at ? v.sale_end_at.slice(0, 16) : '';

        const m = document.getElementById('sale-time-modal');
        m.style.display = 'flex';
        m.classList.add('show');
    }

    function closeSaleTimeModal() {
        const m = document.getElementById('sale-time-modal');
        m.classList.remove('show');
        setTimeout(() => m.style.display = 'none', 200);
        currentSaleModalIndex = null;
    }

    function saveSaleTimeModal() {
        if (currentSaleModalIndex === null || !variantsList[currentSaleModalIndex]) return;

        const v = variantsList[currentSaleModalIndex];
        const start = document.getElementById('modal-sale-start').value;
        const end = document.getElementById('modal-sale-end').value;
        const hasSalePrice = v.sale_price && parseFloat(v.sale_price) > 0;

        // Bắt buộc nhập cả hai ngày nếu đã có giá sale
        if (hasSalePrice && (!start || !end)) {
            Swal.fire('Thiếu thời gian khuyến mãi', 'Phân loại này đã có Giá Khuyến Mãi, BẮT BUỘC phải nhập cả Ngày bắt đầu và Ngày kết thúc sale!', 'warning');
            return;
        }

        if (start && !end) {
            Swal.fire('Thiếu ngày kết thúc', 'Vui lòng chọn ngày giờ kết thúc khuyến mãi!', 'warning');
            return;
        }
        if (!start && end) {
            Swal.fire('Thiếu ngày bắt đầu', 'Vui lòng chọn ngày giờ bắt đầu khuyến mãi!', 'warning');
            return;
        }

        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            const nowBuffer = new Date(Date.now() - 60000);

            // Kiểm tra xem ngày bắt đầu có bị thay đổi so với giá trị ban đầu trong CSDL hay không
            const origStart = (v.original_sale_start_at || '').slice(0, 16);
            const curStart = (start || '').slice(0, 16);
            const isStartChanged = (curStart !== origStart);

            if (isStartChanged && startDate < nowBuffer) {
                Swal.fire('Thời gian không hợp lệ', 'Ngày & Giờ bắt đầu sale đã được thay đổi, do đó phải từ thời điểm hiện tại trở đi đến tương lai (không chọn thời gian trong quá khứ)!', 'warning');
                return;
            }

            if (endDate <= startDate) {
                Swal.fire('Thời gian không hợp lệ', 'Ngày & Giờ kết thúc sale phải diễn ra sau ngày bắt đầu!', 'warning');
                return;
            }
        }

        variantsList[currentSaleModalIndex].sale_start_at = start;
        variantsList[currentSaleModalIndex].sale_end_at = end;

        renderVariantsTable();
        closeSaleTimeModal();
    }

    function clearSaleTimes() {
        if (currentSaleModalIndex === null || !variantsList[currentSaleModalIndex]) return;
        variantsList[currentSaleModalIndex].sale_start_at = '';
        variantsList[currentSaleModalIndex].sale_end_at = '';
        renderVariantsTable();
        closeSaleTimeModal();
    }

    // ==========================================
    // SUBMISSION HANDLER
    // ==========================================
    function submitProductForm() {
        document.getElementById('edit-product-form').requestSubmit();
    }

    async function handleUpdateProduct(e) {
        e.preventDefault();

        const name = document.getElementById('prod-name').value.trim();
        const category_id = document.getElementById('prod-category').value;
        const status = document.getElementById('prod-status').value;
        const material = document.getElementById('prod-material').value.trim();
        const description = document.getElementById('prod-description').value.trim();

        if (!name) {
            Swal.fire({
                icon: 'warning',
                title: 'Bé Gấu nhắc bạn nè! 🐻',
                html: 'Vui lòng nhập <b>Tên sản phẩm</b> nha!',
                confirmButtonColor: '#8D6E63'
            });
            document.getElementById('prod-name').focus();
            return;
        }
        if (!category_id) {
            Swal.fire({
                icon: 'warning',
                title: 'Bé Gấu nhắc bạn nè! 🐻',
                html: 'Vui lòng chọn <b>Danh mục sản phẩm</b> nha!',
                confirmButtonColor: '#8D6E63'
            });
            document.getElementById('prod-category').focus();
            return;
        }

        if (!variantsList.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Bé Gấu nhắc bạn nè! 🐻',
                html: 'Sản phẩm phải có ít nhất 1 phân loại con nha!',
                confirmButtonColor: '#8D6E63'
            });
            return;
        }

        // Validate variants
        const nowBuffer = new Date(Date.now() - 60000);
        for (let i = 0; i < variantsList.length; i++) {
            const v = variantsList[i];
            const num = i + 1;
            const label = `Phân loại #${num} (${v.size || 'Size'} - ${v.color || 'Màu'})`;

            // Ràng buộc Cột Ảnh bắt buộc
            const hasImg = !!(v.file || (v.image_url && !v.image_url.includes('placehold.co')));
            if (!hasImg) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bé Gấu nhắc bạn nè! 🐻',
                    html: `Vui lòng tải ảnh cho <b>${label}</b> nha!<br><span style="font-size: 12.5px; color: #8D6E63;">(Hãy bấm vào icon máy ảnh ở cột <b>Ảnh *</b> của phân loại này)</span>`,
                    confirmButtonColor: '#8D6E63'
                });
                return;
            }

            if (!v.size) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bé Gấu nhắc bạn nè! 🐻',
                    html: `Vui lòng nhập <b>Kích thước</b> cho <b>${label}</b> nha!`,
                    confirmButtonColor: '#8D6E63'
                });
                return;
            }
            if (!/^\d+(\.\d+)?cm$/i.test(v.size.trim())) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bé Gấu nhắc bạn nè! 🐻',
                    html: `Kích thước của <b>${label}</b> (<b>"${escapeHtml(v.size)}"</b>) chưa đúng định dạng rồi ạ!<br><br>Kích thước bắt buộc phải là số kèm đơn vị <b>"cm"</b> viết liền nhau (ví dụ: <b>30cm, 45cm</b>).<br><span style="color:#C62828;">(Dạng có khoảng trắng như <i>45 cm</i> hoặc thiếu chữ <i>cm</i> đều bị lỗi nha!)</span>`,
                    confirmButtonColor: '#8D6E63'
                });
                return;
            }
            if (!v.color) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bé Gấu nhắc bạn nè! 🐻',
                    html: `Vui lòng nhập <b>Màu sắc</b> cho <b>${label}</b> nha!`,
                    confirmButtonColor: '#8D6E63'
                });
                return;
            }
            const vPrice = parseCurrencyToNumber(v.price);
            const vSalePrice = v.sale_price ? parseCurrencyToNumber(v.sale_price) : null;

            if (vPrice <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bé Gấu nhắc bạn nè! 🐻',
                    html: `Vui lòng nhập <b>Giá gốc</b> cho <b>${label}</b> nha!`,
                    confirmButtonColor: '#8D6E63'
                });
                return;
            }
            if (v.stock_quantity === '' || v.stock_quantity === null || v.stock_quantity === undefined || parseInt(v.stock_quantity) < 0 || isNaN(parseInt(v.stock_quantity))) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bé Gấu nhắc bạn nè! 🐻',
                    html: `Vui lòng nhập <b>Số lượng tồn kho</b> cho <b>${label}</b> nha!`,
                    confirmButtonColor: '#8D6E63'
                });
                return;
            }
            if (vSalePrice && vSalePrice > 0) {
                if (vSalePrice >= vPrice) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Bé Gấu nhắc bạn nè! 🐻',
                        html: `Giá khuyến mãi của <b>${label}</b> phải nhỏ hơn giá gốc nha!`,
                        confirmButtonColor: '#8D6E63'
                    });
                    return;
                }
                if (!v.sale_start_at || !v.sale_end_at) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Bé Gấu nhắc bạn nè! 🐻',
                        html: `<b>${label}</b> đã nhập giá khuyến mãi thì bạn nhớ cài đặt cả Ngày bắt đầu và Ngày kết thúc sale nha!`,
                        confirmButtonColor: '#8D6E63'
                    });
                    return;
                }
            }
            if (v.sale_start_at && v.sale_end_at) {
                const start = new Date(v.sale_start_at);
                const end = new Date(v.sale_end_at);
                const origStart = (v.original_sale_start_at || '').slice(0, 16);
                const curStart = (v.sale_start_at || '').slice(0, 16);
                const isStartChanged = (curStart !== origStart);

                if (isStartChanged && start < nowBuffer) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Bé Gấu nhắc bạn nè! 🐻',
                        html: `Ngày bắt đầu sale của <b>${label}</b> đã được thay đổi, do đó phải từ thời điểm hiện tại trở đi nha!`,
                        confirmButtonColor: '#8D6E63'
                    });
                    return;
                }
                if (end <= start) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Bé Gấu nhắc bạn nè! 🐻',
                        html: `Ngày kết thúc sale của <b>${label}</b> phải sau ngày bắt đầu nha!`,
                        confirmButtonColor: '#8D6E63'
                    });
                    return;
                }
            }
        }

        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', name);
        formData.append('category_id', category_id);
        formData.append('status', status);
        if (material) formData.append('material', material);
        if (description) formData.append('description', description);

        // Danh sách ảnh cũ giữ lại
        existingImages.forEach(img => {
            formData.append('kept_image_ids[]', img.id);
        });

        // Chỉ định ảnh chính
        const primaryExisting = existingImages.find(img => img.is_primary);
        const primaryNewIdx = newFiles.findIndex(f => f.is_primary);

        if (primaryExisting) {
            formData.append('primary_type', 'existing');
            formData.append('primary_id', primaryExisting.id);
        } else if (primaryNewIdx !== -1) {
            formData.append('primary_type', 'new');
            formData.append('primary_index', primaryNewIdx);
        }

        // File ảnh cha mới
        newFiles.forEach(item => {
            formData.append('image_files[]', item.file);
        });

        // Đóng gói mảng biến thể
        variantsList.forEach((v, idx) => {
            if (v.id) formData.append(`variants[${idx}][id]`, v.id);
            formData.append(`variants[${idx}][sku]`, v.sku || '');
            formData.append(`variants[${idx}][size]`, v.size || '');
            formData.append(`variants[${idx}][color]`, v.color || '');
            formData.append(`variants[${idx}][price]`, parseCurrencyToNumber(v.price));
            if (v.sale_price) formData.append(`variants[${idx}][sale_price]`, parseCurrencyToNumber(v.sale_price));
            if (v.sale_start_at) formData.append(`variants[${idx}][sale_start_at]`, v.sale_start_at);
            if (v.sale_end_at) formData.append(`variants[${idx}][sale_end_at]`, v.sale_end_at);
            formData.append(`variants[${idx}][stock_quantity]`, v.stock_quantity ?? 0);
            formData.append(`variants[${idx}][is_default]`, v.is_default ? '1' : '0');
            formData.append(`variants[${idx}][status]`, v.status || 'ACTIVE');

            // File ảnh riêng của biến thể nếu có
            if (v.file) {
                formData.append(`variant_images[${idx}]`, v.file);
            } else if (v.image_url && !v.image_url.startsWith('blob:')) {
                formData.append(`variants[${idx}][image_url]`, v.image_url);
            }
        });

        const submitBtn = document.getElementById('btn-submit');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang cập nhật dữ liệu...';

        try {
            const res = await fetch('{{ route("admin.products.update", $product->id) }}', {
                method: 'POST', // POST with _method=PUT for multipart
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
                let errorMsg = data.message || 'Không thể cập nhật sản phẩm';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Bé Gấu nhắc bạn nè! 🐻',
                    html: errorMsg,
                    confirmButtonColor: '#8D6E63'
                });
            }
        } catch (err) {
            Swal.fire('Lỗi kết nối', 'Có lỗi xảy ra khi kết nối máy chủ. Vui lòng thử lại!', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
</script>
@endsection
