@extends('layouts.admin-dashboard')

@php $currentPage = 'products'; @endphp

@section('page-title', 'Thêm Sản Phẩm Mới')

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
            <span>Thêm sản phẩm mới</span>
        </div>
        <h1 class="form-page-title">
            <span>Thêm Sản Phẩm Mới &amp; Phân Loại Biến Thể</span>
            <span class="form-header-badge"><i class="fa-solid fa-sparkles"></i> Mật Ngọt Bear</span>
        </h1>
    </div>
    <div class="form-page-actions">
        <a href="{{ route('admin.products.index') }}" class="btn-soft-back">
            <i class="fa-solid fa-arrow-left"></i> Quay Lại
        </a>
    </div>
</div>

<form id="create-product-form" onsubmit="handleStoreProduct(event)" enctype="multipart/form-data" novalidate>
    @csrf

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
                        Thông tin cơ bản
                    </span>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin-bottom: 0;">Tên Sản Phẩm Gấu Bông <span class="req">*</span></label>
                        <span id="prod-name-counter" style="font-size: 12px; font-weight: 700; color: #8D6E63; background: #FFF3E0; padding: 2px 8px; border-radius: 999px; border: 1px solid #FFE0B2;">0/120</span>
                    </div>
                    <input type="text" id="prod-name" name="name" class="input-control" maxlength="120" required placeholder="Ví dụ: Gấu Bông Teddy Áo Len Cổ Điển..." oninput="updateNameCounter(this)">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Danh Mục Sản Phẩm <span class="req">*</span></label>
                        <select id="prod-category" name="category_id" class="select-control" required>
                            <option value="">-- Chọn Danh Mục Gấu Bông --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Trạng Thái Kinh Doanh <span class="req">*</span></label>
                        <input type="hidden" id="prod-status" name="status" value="ACTIVE">
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 6px; padding: 7px 12px; background: #FFF9F3; border: 1px solid #EFEBE9; border-radius: 8px; width: fit-content;">
                            <div class="switch-toggle-box" onclick="toggleParentFormStatus()" title="Bấm để bật/tắt trạng thái kinh doanh">
                                <div class="switch-toggle-track active" id="parent-status-track">
                                    <span class="switch-toggle-thumb"></span>
                                </div>
                            </div>
                            <span id="parent-status-label" style="font-size: 13px; font-weight: 700; color: #10B981;">
                                Đang kinh doanh (Bật)
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Chất Liệu Gấu Bông</label>
                    <input type="text" id="prod-material" name="material" class="input-control" placeholder="Ví dụ: 100% Bông PP 3D xoắn tinh khiết đàn hồi 4 chiều, vải nhung tuyết mịn...">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Mô Tả Chi Tiết Sản Phẩm</label>
                    <textarea id="prod-description" name="description" class="input-control" style="min-height: 100px;" placeholder="Mô tả ưu điểm nổi bật, xuất xứ, độ an toàn và cảm giác khi ôm..."></textarea>
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
                            <span>Chi Tiết Các Sản Phẩm Con</span>
                        </div>
                        <div class="variants-header-desc">
                            Mỗi sản phẩm con đại diện cho 1 phân loại (Kích thước &amp; Màu sắc)
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
                                <th style="width: 48px; text-align: center;" title="Mỗi nhóm màu chỉ cần ít nhất 1 ảnh (các size cùng màu tự nhận chung ảnh)">Ảnh <span style="color:#C62828;">*</span></th>
                                <th style="width: 17%; text-align: left;">Kích Thước <span style="color:#C62828;">*</span></th>
                                <th style="width: 18%; text-align: left;">Màu Sắc <span style="color:#C62828;">*</span></th>
                                <th style="width: 15%; text-align: left;">Giá Gốc <span style="color:#C62828;">*</span></th>
                                <th style="width: 18%; text-align: left;">Giá Sale &amp; Hẹn Giờ</th>
                                <th style="width: 14%; text-align: left;">Tồn Kho <span style="color:#C62828;">*</span></th>
                                <th style="width: 76px; text-align: center;">Trạng thái</th>
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
            <!-- 3. BỘ SƯU TẬP ẢNH SẢN PHẨM CHÍNH (TỐI ĐA 9 ẢNH) -->
            <div class="form-card">
                <div class="form-section-title">
                    <div class="form-section-title-left">
                        <div class="form-section-icon-wrap brown">
                            <i class="fa-solid fa-images"></i>
                        </div>
                        <div>
                            <div class="form-section-title-text">Bộ Sản Phẩm Chính <span style="color: #E53935; font-weight: 800;">*</span></div>
                        </div>
                    </div>
                    <span id="gallery-counter-badge" class="form-section-badge">
                        0 / 9 ảnh
                    </span>
                </div>

                <input type="file" id="general-file-input" multiple accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" style="display: none;" onchange="handleGeneralFiles(event)">

                <div class="upload-box-area" onclick="document.getElementById('general-file-input').click()">
                    <div class="upload-box-icon">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div style="font-size: 14px; font-weight: 800; color: var(--pf-brown-deep); margin-bottom: 4px;">
                        Bấm để chọn ảnh từ máy tính
                    </div>
                    <div style="font-size: 11.5px; color: var(--pf-brown-subtle); line-height: 1.4;">
                        Tối thiểu 1 ảnh bìa, tối đa 9 ảnh (JPG, PNG, WEBP, GIF)<br>Bao gồm 1 ảnh bìa đại diện và 8 ảnh chi tiết khác.
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
                        Hoàn tất &amp; Đăng bán
                    </div>
                    <div style="font-size: 12px; color: var(--pf-brown-subtle); line-height: 1.4;">
                        Hệ thống sẽ đồng bộ các sản phẩm con vào cơ sở dữ liệu và hiển thị lên website khách hàng.
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button type="submit" class="btn-gold-save" id="btn-submit" style="width: 100%; justify-content: center; padding: 12px; font-size: 14px;">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu &amp; Đăng Sản Phẩm
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
                    <li><i class="fa-solid fa-check"></i> Ảnh có gắn sao "Bìa" của Bộ ảnh chính sẽ hiển thị làm ảnh đại diện trên trang chủ và danh mục.</li>
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
                <div class="form-hint">Thời điểm bắt đầu phải từ giây phút hiện tại trở đi đến tương lai (không chọn quá khứ).</div>
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
                Nhập kích thước &amp; màu sắc để <strong>tự động tạo danh sách phân loại con</strong>, hoặc chỉ nhập giá &amp; tồn kho để <strong>áp dụng đồng loạt cho các dòng hiện có</strong>:
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
    // STATE MANAGEMENT
    // ==========================================
    const MAX_GENERAL_IMAGES = 9;
    let generalFiles = []; // Array of { file, previewUrl, is_primary }
    
    // Mảng chứa các sản phẩm con (biến thể)
    // Structure: { id, sku, size, color, price, sale_price, sale_start_at, sale_end_at, stock_quantity, image_url, file, is_default, status }
    let variantsList = [];
    let currentSaleModalIndex = null;

    document.addEventListener('DOMContentLoaded', () => {
        // Mặc định khởi tạo 1 sản phẩm con trống, toàn bộ ô hiển thị placeholder
        variantsList = [
            {
                id: null,
                _uid: 'var_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
                sku: '',
                size: '',
                color: '',
                price: '',
                sale_price: '',
                sale_start_at: '',
                sale_end_at: '',
                stock_quantity: '',
                image_url: '',
                file: null,
                is_default: true,
                status: 'ACTIVE'
            }
        ];
        renderVariantsTable();
    });

    // ==========================================
    // GENERAL IMAGES GALLERY (TỐI ĐA 9 ẢNH)
    // ==========================================
    function handleGeneralFiles(e) {
        const files = Array.from(e.target.files).filter(f => f.type.startsWith('image/'));
        if (!files.length) return;

        const total = generalFiles.length + files.length;
        if (total > MAX_GENERAL_IMAGES) {
            const slots = MAX_GENERAL_IMAGES - generalFiles.length;
            Swal.fire('Giới hạn ảnh', `Tối đa chỉ được tải 9 ảnh cho Bộ ảnh sản phẩm chính (còn trống ${Math.max(0, slots)} ảnh).`, 'warning');
            return;
        }

        files.forEach(f => {
            const isFirst = (generalFiles.length === 0);
            generalFiles.push({
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
        badge.innerText = `${generalFiles.length} / ${MAX_GENERAL_IMAGES} ảnh`;

        if (!generalFiles.length) {
            grid.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; color: var(--text-light); font-size: 11.5px; padding: 1rem; border: 1px dashed var(--border); border-radius: 8px;">Chưa chọn ảnh nào (Bắt buộc tối thiểu 1 ảnh bìa)</div>`;
            return;
        }

        grid.innerHTML = generalFiles.map((item, idx) => `
            <div class="preview-card ${item.is_primary ? 'is-primary' : ''}">
                <img src="${item.previewUrl}" class="preview-thumb" alt="Preview">
                <div class="preview-actions">
                    <button type="button" class="btn-badge-primary" onclick="setPrimaryGeneral(${idx})">
                        ${item.is_primary ? '<i class="fa-solid fa-star"></i> Bìa' : 'Chọn bìa'}
                    </button>
                    <button type="button" class="btn-del-thumb" onclick="removeGeneralImage(${idx})" title="Xóa ảnh này khỏi bộ ảnh chính">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    function setPrimaryGeneral(idx) {
        generalFiles.forEach((f, i) => f.is_primary = (i === idx));
        renderGeneralGallery();
    }

    function removeGeneralImage(idx) {
        const wasPrimary = generalFiles[idx].is_primary;
        generalFiles.splice(idx, 1);
        if (wasPrimary && generalFiles.length > 0) {
            generalFiles[0].is_primary = true;
        }
        renderGeneralGallery();
    }

    // ==========================================
    // ==========================================
    // CURRENCY FORMATTING HELPERS
    // ==========================================
    function formatCurrencyString(val) {
        if (val === null || val === undefined || val === '') return '';
        const str = String(val).trim();
        if (str === '0' || val === 0) return '0';
        const digits = str.replace(/\D/g, '');
        if (!digits) return '';
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function parseCurrencyToNumber(val) {
        if (val === null || val === undefined || val === '') return null;
        const str = String(val).trim();
        if (str === '0' || val === 0) return 0;
        const digits = str.replace(/\D/g, '');
        return digits ? parseFloat(digits) : null;
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
            variantsList[idx][field] = (field === 'sale_price' ? null : '');
            if (field === 'sale_price') {
                variantsList[idx].sale_start_at = '';
                variantsList[idx].sale_end_at = '';
                renderVariantsTable();
            }
        } else {
            const digits = raw.replace(/\D/g, '');
            if (digits === '0') {
                input.value = '0';
                variantsList[idx][field] = 0;
            } else {
                const num = parseCurrencyToNumber(raw);
                input.value = formatCurrencyString(num);
                variantsList[idx][field] = num;
            }
            if (field === 'sale_price') {
                renderVariantsTable();
            }
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
        syncVariantsFromDom();
        if (!variantsList[idx]) return;
        variantsList[idx].status = (variantsList[idx].status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE');
        renderVariantsTable();
    }

    // ==========================================
    // VARIANTS TABLE RENDERING & ACTIONS
    // ==========================================
    const PLACEHOLDER_THUMB = 'https://placehold.co/100x100/fdf6e2/8d6e63?text=%2B+%E1%BA%A2nh*';

    function capitalizeColor(str) {
        if (!str) return '';
        return str.trim().split(/\s+/).map(w => {
            if (!w) return '';
            return w.charAt(0).toUpperCase() + w.slice(1).toLowerCase();
        }).join(' ');
    }

    function refreshVariantThumb(idx) {
        const v = variantsList[idx];
        if (!v) return;

        const thumbWrap = document.getElementById(`var-thumb-${idx}`);
        const imgPreview = document.getElementById(`var-img-preview-${idx}`);
        if (!thumbWrap || !imgPreview) return;

        const isInherited = !!v.inheritedFromColor;
        const hasImg = !!(v.image_url && !v.image_url.includes('placehold.co'));

        if (hasImg) {
            imgPreview.src = v.image_url;
            if (isInherited) {
                thumbWrap.style.border = '1.5px solid #2E7D32';
                thumbWrap.style.background = '#F1F8E9';
                thumbWrap.title = 'Đang dùng chung ảnh theo màu ' + escapeHtml(v.color || '') + ' (Bấm để chọn ảnh riêng)';
                
                let badge = thumbWrap.querySelector('.inherited-badge');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'inherited-badge';
                    badge.title = 'Tự động kế thừa ảnh theo màu';
                    badge.style.cssText = 'position:absolute; bottom:2px; right:2px; background:#2E7D32; color:#fff; font-size:8px; padding:1px 3px; border-radius:3px; font-weight:800; z-index:2;';
                    badge.innerHTML = '<i class="fa-solid fa-link"></i>';
                    thumbWrap.appendChild(badge);
                }
            } else {
                thumbWrap.style.border = '';
                thumbWrap.style.background = '';
                thumbWrap.title = 'Bấm để đổi ảnh riêng cho phân loại này';
                const badge = thumbWrap.querySelector('.inherited-badge');
                if (badge) badge.remove();
            }
        } else {
            imgPreview.src = PLACEHOLDER_THUMB;
            thumbWrap.style.border = '1.5px dashed #E53935';
            thumbWrap.style.background = '#FFEBEE';
            thumbWrap.title = 'Bắt buộc: Bấm để chọn ảnh cho nhóm màu này';
            const badge = thumbWrap.querySelector('.inherited-badge');
            if (badge) badge.remove();
        }
    }

    function syncAllColorInheritance() {
        // 1. Bản đồ ảnh đại diện cho từng nhóm màu (chỉ lấy từ dòng có ảnh và màu còn khớp với color_origin)
        const colorOwnerMap = {};
        variantsList.forEach((v, idx) => {
            const cKey = (v.color || '').trim().toLowerCase();
            const hasValidImg = !!(v.file || (v.image_url && !v.image_url.includes('placehold.co')));
            const matchesOrigin = (!v.color_origin || v.color_origin === cKey);

            if (cKey && hasValidImg && matchesOrigin && !v.inheritedFromColor && !colorOwnerMap[cKey]) {
                colorOwnerMap[cKey] = {
                    file: v.file,
                    image_url: v.image_url,
                    ownerIdx: idx
                };
            }
        });

        // 2. Cập nhật trạng thái kế thừa ảnh cho từng dòng biến thể
        variantsList.forEach((v, idx) => {
            const cKey = (v.color || '').trim().toLowerCase();
            const hasValidImg = !!(v.file || (v.image_url && !v.image_url.includes('placehold.co')));
            const matchesOrigin = (!v.color_origin || v.color_origin === cKey);

            if (hasValidImg && matchesOrigin && !v.inheritedFromColor) {
                // Giữ nguyên ảnh tự tải lên của biến thể khi màu vẫn khớp
                v.inheritedFromColor = false;
            } else if (cKey && colorOwnerMap[cKey] && colorOwnerMap[cKey].ownerIdx !== idx) {
                // Trùng khớp chính xác 100% màu sắc với dòng có ảnh gốc
                v.file = colorOwnerMap[cKey].file;
                v.image_url = colorOwnerMap[cKey].image_url;
                v.inheritedFromColor = true;
                v.color_origin = cKey;
            } else {
                // Khác màu hoặc thêm/bớt bất kỳ ký tự nào -> XÓA ẢNH VỀ ẢNH TRỐNG NGAY LẬP TỨC!
                v.file = null;
                v.image_url = '';
                v.inheritedFromColor = false;
            }

            refreshVariantThumb(idx);
        });
    }

    function handleColorInput(idx, val) {
        if (!variantsList[idx]) return;
        variantsList[idx].color = val;
        syncAllColorInheritance();
    }

    function handleColorKeydown(e, idx, input) {
        if (e.key === 'Enter') {
            e.preventDefault();
            input.blur();
        }
    }

    function handleColorBlur(idx, input) {
        if (!variantsList[idx]) return;
        const formatted = capitalizeColor(input.value);
        input.value = formatted;
        variantsList[idx].color = formatted;
        syncAllColorInheritance();
    }

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
        const hasSalePrice = (v.sale_price !== null && v.sale_price !== '' && v.sale_price !== undefined && !isNaN(parseFloat(v.sale_price)));
            let timeBtnLabel = '<i class="fa-regular fa-clock"></i> Lịch sale';
            let timeBtnClass = '';

            // Chỉ hiển thị trạng thái Đang sale / Sắp sale khi đã có giá sale hợp lệ và có thời gian sale
            if (hasSalePrice && (v.sale_start_at || v.sale_end_at)) {
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
                    timeBtnClass = 'active';
                    timeBtnLabel = '<i class="fa-solid fa-check"></i> Đã cài đặt';
                }
            }

            // Thumbnail display & Color Inheritance
            const curColor = (v.color || '').trim().toLowerCase();
            let isInherited = !!v.inheritedFromColor;
            let thumbSrc = PLACEHOLDER_THUMB;
            const hasValidImg = !!(v.file || (v.image_url && !v.image_url.includes('placehold.co')));
            const matchesOrigin = (!v.color_origin || v.color_origin === curColor);

            if (hasValidImg && matchesOrigin) {
                thumbSrc = v.image_url;
            } else if (curColor) {
                // Tìm dòng khác có cùng màu chính xác và có ảnh
                const donor = variantsList.find((otherV, i) => 
                    i !== idx && 
                    (otherV.color || '').trim().toLowerCase() === curColor && 
                    (otherV.file || (otherV.image_url && !otherV.image_url.includes('placehold.co')))
                );
                if (donor) {
                    v.file = donor.file;
                    v.image_url = donor.image_url;
                    v.inheritedFromColor = true;
                    v.color_origin = curColor;
                    thumbSrc = donor.image_url;
                    isInherited = true;
                } else {
                    v.file = null;
                    v.image_url = '';
                    v.inheritedFromColor = false;
                    thumbSrc = PLACEHOLDER_THUMB;
                    isInherited = false;
                }
            } else {
                v.file = null;
                v.image_url = '';
                v.inheritedFromColor = false;
                thumbSrc = PLACEHOLDER_THUMB;
                isInherited = false;
            }

            const isImgCovered = !!(v.file || (v.image_url && !v.image_url.includes('placehold.co')));

            const formattedPrice = (v.price !== '' && v.price !== null && v.price !== undefined && !isNaN(v.price)) ? formatCurrencyString(v.price) : '';
            const formattedSalePrice = (v.sale_price !== '' && v.sale_price !== null && v.sale_price !== undefined && !isNaN(v.sale_price)) ? formatCurrencyString(v.sale_price) : '';
            const formattedStock = (v.stock_quantity !== '' && v.stock_quantity !== null && v.stock_quantity !== undefined) ? v.stock_quantity : '';
            const isRowActive = (v.status === 'ACTIVE');

            return `
                <tr>
                    <td style="text-align: center;">
                        <input type="file" id="var-file-${idx}" accept="image/*" style="display:none;" onchange="handleVariantFileSelect(${idx}, event)">
                        <div class="variant-thumb-wrap" id="var-thumb-${idx}" onclick="triggerVariantFile(${idx})" 
                             title="${isInherited ? 'Đang dùng chung ảnh theo màu ' + escapeHtml(v.color) + ' (Bấm để chọn ảnh riêng)' : (isImgCovered ? 'Bấm để đổi ảnh riêng cho phân loại này' : 'Bắt buộc: Bấm để chọn ảnh cho nhóm màu này')}" 
                             style="${!isImgCovered ? 'border: 1.5px dashed #E53935; background: #FFEBEE;' : (isInherited ? 'border: 1.5px solid #2E7D32; background: #F1F8E9;' : '')}">
                            <img src="${thumbSrc}" class="variant-thumb-img" id="var-img-preview-${idx}">
                            ${isInherited ? '<span class="inherited-badge" title="Tự động kế thừa ảnh theo màu" style="position:absolute; bottom:2px; right:2px; background:#2E7D32; color:#fff; font-size:8px; padding:1px 3px; border-radius:3px; font-weight:800; z-index:2;"><i class="fa-solid fa-link"></i></span>' : ''}
                            <div class="variant-thumb-overlay">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                        </div>
                    </td>
                    <td>
                        <input type="text" id="var-size-${idx}" class="v-input" value="${escapeHtml(v.size || '')}" placeholder="30cm, 40cm,..." onblur="handleSizeBlur(${idx}, this)" oninput="updateVariantField(${idx}, 'size', this.value)">
                    </td>
                    <td>
                        <input type="text" id="var-color-${idx}" class="v-input" value="${escapeHtml(v.color || '')}" placeholder="Nâu socola, Vàng bơ,..." oninput="handleColorInput(${idx}, this.value)" onkeydown="handleColorKeydown(event, ${idx}, this)" onblur="handleColorBlur(${idx}, this)">
                    </td>
                    <td>
                        <input type="text" id="var-price-${idx}" inputmode="numeric" class="v-input v-input-num" value="${formattedPrice}" placeholder="550.000" onblur="handlePriceBlur(${idx}, 'price', this)" onkeydown="handlePriceKeydown(event, this)">
                    </td>
                    <td>
                        <input type="text" id="var-sale-price-${idx}" inputmode="numeric" class="v-input v-input-num" value="${formattedSalePrice}" placeholder="360.000" onblur="handlePriceBlur(${idx}, 'sale_price', this)" onkeydown="handlePriceKeydown(event, this)">
                        <div style="text-align: left;">
                            <button type="button" class="variant-time-btn ${timeBtnClass}" onclick="openSaleTimeModal(${idx})" title="Cài đặt ngày giờ bắt đầu và kết thúc khuyến mãi">
                                ${timeBtnLabel}
                            </button>
                        </div>
                    </td>
                    <td>
                        <input type="number" id="var-stock-${idx}" class="v-input v-input-num" value="${formattedStock}" min="0" placeholder="20" oninput="updateVariantField(${idx}, 'stock_quantity', this.value)">
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
        if (field === 'color') {
            syncAllColorInheritance();
        }
        if (field === 'price' || field === 'stock_quantity' || field === 'sale_price') {
            updateSummaryStats();
        }
    }

    function setDefaultVariant(idx) {
        syncVariantsFromDom();
        variantsList.forEach((v, i) => v.is_default = (i === idx));
        renderVariantsTable();
    }

    function addNewVariantRow() {
        syncVariantsFromDom();
        const prodName = document.getElementById('prod-name').value.trim();
        const nextIdx = variantsList.length + 1;
        const prefix = prodName ? prodName.split(' ')[0].toUpperCase().replace(/[^A-Z0-9]/g, '') : 'BEAR';
        const rand = Math.random().toString(36).substring(2, 6).toUpperCase();

        variantsList.push({
            id: null,
            _uid: 'var_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
            sku: `${prefix || 'BEAR'}-V${nextIdx}-${rand}`,
            size: '',
            color: '',
            price: '',
            sale_price: null,
            sale_start_at: '',
            sale_end_at: '',
            stock_quantity: '',
            image_url: '',
            file: null,
            is_default: variantsList.length === 0,
            status: 'ACTIVE'
        });

        renderVariantsTable();
    }

    function removeVariantRow(idx) {
        syncVariantsFromDom();
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

        renderVariantsTable();
    }

    function triggerVariantFile(idx) {
        document.getElementById(`var-file-${idx}`).click();
    }

    function handleVariantFileSelect(idx, e) {
        const file = e.target.files[0];
        if (!file) return;

        syncVariantsFromDom();
        const v = variantsList[idx];
        if (!v) return;

        const curColor = (v.color || '').trim().toLowerCase();
        const displayColor = v.color ? v.color.trim() : '';
        const previewUrl = URL.createObjectURL(file);

        v._uid = v._uid || ('var_' + idx + '_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7));
        v.file = file;
        v.image_url = previewUrl;
        v.inheritedFromColor = false;
        v.color_origin = curColor;

        // Tìm các sản phẩm con khác CÙNG MÀU SẮC
        const sameColorIndices = [];
        if (curColor) {
            variantsList.forEach((otherV, otherIdx) => {
                if (otherIdx !== idx && (otherV.color || '').trim().toLowerCase() === curColor) {
                    sameColorIndices.push(otherIdx);
                }
            });
        }

        if (sameColorIndices.length > 0 && typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Đồng bộ ảnh màu sắc',
                html: `Bạn có muốn đồng bộ ảnh cho các ảnh có cùng màu sắc: <b style="color: #D32F2F;">"${escapeHtml(displayColor)}"</b> không?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2E7D32',
                cancelButtonColor: '#8D6E63',
                confirmButtonText: '<i class="fa-solid fa-check"></i> Có, đồng bộ tất cả',
                cancelButtonText: 'Không, chỉ áp dụng cho dòng này'
            }).then((result) => {
                if (result.isConfirmed) {
                    sameColorIndices.forEach(otherIdx => {
                        const targetV = variantsList[otherIdx];
                        targetV.file = file;
                        targetV.image_url = previewUrl;
                        targetV.inheritedFromColor = true;
                        targetV.color_origin = curColor;
                    });
                    renderVariantsTable();
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `Đã đồng bộ ảnh cho các sản phẩm con màu "${displayColor}"!`,
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    renderVariantsTable();
                }
            });
        } else {
            renderVariantsTable();
        }
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

        const prices = variantsList.map(v => (v.price !== '' && v.price !== null && v.price !== undefined) ? parseFloat(v.price) : NaN).filter(p => !isNaN(p) && p >= 0);
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
        const words = prodName.split(/\s+/).map(w => w[0] ? w[0].toUpperCase() : '').join('').slice(0, 5) || 'BEAR';

        variantsList.forEach((v, idx) => {
            if (!v.sku || v.sku.startsWith('BEAR') || v.sku.startsWith('PRD')) {
                const s = v.size ? v.size.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : (idx + 1);
                const rand = Math.random().toString(36).substring(2, 6).toUpperCase();
                v.sku = `${words}-${s}-${rand}`;
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

    function isVariantFilled(v) {
        if (!v) return false;
        const hasSize = !!(v.size && String(v.size).trim() !== '');
        const hasColor = !!(v.color && String(v.color).trim() !== '');
        const hasPrice = (v.price !== '' && v.price !== null && v.price !== undefined && parseFloat(v.price) > 0);
        const hasSalePrice = (v.sale_price !== null && v.sale_price !== '' && v.sale_price !== undefined && !isNaN(parseFloat(v.sale_price)));
        const hasStock = (v.stock_quantity !== '' && v.stock_quantity !== null && v.stock_quantity !== undefined && String(v.stock_quantity).trim() !== '');
        const hasImg = !!(v.file || (v.image_url && !v.image_url.includes('placehold.co')));
        const hasSaleTimes = !!(v.sale_start_at || v.sale_end_at);
        return hasSize || hasColor || hasPrice || hasSalePrice || hasStock || hasImg || hasSaleTimes;
    }

    function syncVariantsFromDom() {
        variantsList.forEach((v, idx) => {
            const sizeInput = document.getElementById(`var-size-${idx}`);
            if (sizeInput) v.size = sizeInput.value.trim();

            const colorInput = document.getElementById(`var-color-${idx}`);
            if (colorInput) {
                const formattedColor = capitalizeColor(colorInput.value);
                colorInput.value = formattedColor;
                v.color = formattedColor;
            }

            const priceInput = document.getElementById(`var-price-${idx}`);
            if (priceInput) {
                const rawP = priceInput.value.trim();
                v.price = rawP ? parseCurrencyToNumber(rawP) : '';
            }

            const salePriceInput = document.getElementById(`var-sale-price-${idx}`);
            if (salePriceInput) {
                const rawSp = salePriceInput.value.trim();
                v.sale_price = rawSp ? parseCurrencyToNumber(rawSp) : null;
            }

            const stockInput = document.getElementById(`var-stock-${idx}`);
            if (stockInput) {
                const rawSt = stockInput.value.trim();
                v.stock_quantity = rawSt !== '' ? rawSt : '';
            }
        });
    }

    // ==========================================
    // TẠO NHANH / ÁP DỤNG HÀNG LOẠT (POPUP KẾT HỢP)
    // ==========================================
    function openCombinedBulkModal() {
        syncVariantsFromDom();
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
        syncVariantsFromDom();
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

        if (sp !== '' && sp !== null && !isNaN(parseFloat(sp)) && parseFloat(sp) >= 0) {
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
        const colors = colorsRaw ? colorsRaw.split(/[,;\n]/).map(c => capitalizeColor(c)).filter(Boolean) : [];

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
                    const rand = Math.random().toString(36).substring(2, 6).toUpperCase();

                    newVariants.push({
                        id: null,
                        _uid: 'var_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
                        sku: `${prefix}-${szCode}-${clCode}-${rand}`,
                        size: sz,
                        color: cl,
                        price: p !== '' ? p : '',
                        sale_price: sp !== '' ? sp : null,
                        sale_start_at: start || '',
                        sale_end_at: end || '',
                        stock_quantity: st !== '' ? st : '',
                        image_url: '',
                        file: null,
                        is_default: isFirst,
                        status: 'ACTIVE'
                    });
                    isFirst = false;
                });
            });

            // Lọc các sản phẩm con hiện tại đã được nhập bất kỳ thông tin nào
            const filledExisting = variantsList.filter(isVariantFilled);

            if (filledExisting.length > 0) {
                // Đã có sản phẩm con có dữ liệu: giữ nguyên và thêm các sản phẩm tạo nhanh ở phía dưới
                const hasExistingDefault = filledExisting.some(v => v.is_default);
                newVariants.forEach((v, idx) => {
                    v.is_default = hasExistingDefault ? false : (idx === 0);
                });
                variantsList = [...filledExisting, ...newVariants];

                renderVariantsTable();
                closeCombinedBulkModal();

                Swal.fire({
                    icon: 'success',
                    title: 'Tạo thành công!',
                    text: `Đã thêm ${newVariants.length} phân loại sản phẩm con mới ở phía dưới các phân loại đã nhập!`,
                    timer: 1800,
                    showConfirmButton: false
                });
            } else {
                // Chưa nhập gì hoặc đã xóa hết: thay thế hoàn toàn
                if (newVariants.length > 0) {
                    newVariants[0].is_default = true;
                }
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
            }
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
                    v.sale_price = (sp !== '' && sp !== null && !isNaN(parseFloat(sp))) ? parseFloat(sp) : null;
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

        // Đồng bộ từ DOM phòng trường hợp vừa nhập chưa kịp blur
        const saleInput = document.getElementById(`var-sale-price-${idx}`);
        if (saleInput) {
            const rawVal = saleInput.value.trim();
            v.sale_price = (rawVal !== '') ? parseCurrencyToNumber(rawVal) : null;
        }
        const priceInput = document.getElementById(`var-price-${idx}`);
        if (priceInput) {
            const rawP = priceInput.value.trim();
            if (rawP !== '') v.price = parseCurrencyToNumber(rawP);
        }

        // Chỉ chặn khi ô giá sale để trống/null. 0đ là hợp lệ (tặng miễn phí)
        const salePriceRaw = v.sale_price;
        const isEmpty = (salePriceRaw === null || salePriceRaw === '' || salePriceRaw === undefined);

        if (isEmpty) {
            Swal.fire({
                icon: 'warning',
                title: 'Thiếu giá khuyến mãi',
                html: 'Bạn chưa nhập <b>Giá Khuyến Mãi</b> cho phân loại này!<br><br>Vui lòng nhập giá khuyến mãi vào ô <b>"Giá Sale &amp; Hẹn Giờ"</b> trước khi cài lịch.<br><small style="color:#888">(Nhập 0 nếu muốn tặng miễn phí trong giờ sale)</small>',
                confirmButtonColor: '#8D6E63'
            });
            currentSaleModalIndex = null;
            return;
        }

        const vSalePrice = parseCurrencyToNumber(salePriceRaw);
        const vPrice = parseCurrencyToNumber(v.price);
        if (vPrice !== null && !isNaN(vPrice) && vPrice > 0 && vSalePrice !== null && !isNaN(vSalePrice) && vSalePrice >= vPrice) {
            Swal.fire({
                icon: 'warning',
                title: 'Giá khuyến mãi không hợp lệ',
                html: 'Giá khuyến mãi phải <b>nhỏ hơn giá gốc</b>!<br>Vui lòng nhập lại giá khuyến mãi hợp lệ trước khi cài lịch.',
                confirmButtonColor: '#8D6E63'
            });
            currentSaleModalIndex = null;
            return;
        }

        document.getElementById('sale-modal-variant-title').innerText = `Phân loại: ${v.size || 'Size chuẩn'} - ${v.color || 'Màu sắc'}`;
        
        const now = new Date();
        const minTimeStr = formatDateTimeLocal(new Date(now.getTime() - 60000));
        
        const inputStart = document.getElementById('modal-sale-start');
        const inputEnd = document.getElementById('modal-sale-end');
        
        inputStart.min = minTimeStr;
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
        const hasSalePrice = (v.sale_price !== null && v.sale_price !== '' && v.sale_price !== undefined && !isNaN(parseFloat(v.sale_price)));

        // 1. Ràng buộc: Nếu chọn thời gian sale thì BẮT BUỘC phải nhập giá sale
        if ((start || end) && !hasSalePrice) {
            Swal.fire({
                icon: 'warning',
                title: 'Thiếu giá khuyến mãi',
                html: 'Bạn đã chọn thời gian sale nhưng <b>chưa nhập Giá Khuyến Mãi</b> cho phân loại này!<br><br>Vui lòng nhập giá khuyến mãi vào ô <b>"Giá Sale & Hẹn Giờ"</b> trước khi cài đặt thời gian.',
                confirmButtonColor: '#8D6E63'
            });
            return;
        }

        // 2. Ràng buộc: Bắt buộc chọn đầy đủ cả hai ngày
        if (start && !end) {
            Swal.fire('Thiếu ngày kết thúc', 'Vui lòng chọn ngày giờ kết thúc khuyến mãi!', 'warning');
            return;
        }
        if (!start && end) {
            Swal.fire('Thiếu ngày bắt đầu', 'Vui lòng chọn ngày giờ bắt đầu khuyến mãi!', 'warning');
            return;
        }
        if (hasSalePrice && (!start || !end)) {
            Swal.fire('Thiếu thời gian khuyến mãi', 'Phân loại này đã có Giá Khuyến Mãi, BẮT BUỘC phải chọn cả Ngày bắt đầu và Ngày kết thúc sale!', 'warning');
            return;
        }

        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            const nowBuffer = new Date(Date.now() - 60000);

            // Khi thêm mới: Ngày bắt đầu phải từ hiện tại trở đi
            if (startDate < nowBuffer) {
                Swal.fire('Thời gian không hợp lệ', 'Ngày & Giờ bắt đầu sale phải từ thời điểm hiện tại trở đi đến tương lai (không chọn thời gian trong quá khứ)!', 'warning');
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
        document.getElementById('create-product-form').requestSubmit();
    }

    function highlightAndNotify(el, message, title = 'Thông báo') {
        if (el) {
            el.classList.add('is-invalid');
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (typeof el.focus === 'function') el.focus();
        }
        Swal.fire({
            icon: 'warning',
            title: title,
            html: message,
            confirmButtonColor: '#8D6E63'
        });
    }

    async function handleStoreProduct(e) {
        e.preventDefault();

        // Xóa các highlight lỗi cũ
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const name = document.getElementById('prod-name').value.trim();
        const category_id = document.getElementById('prod-category').value;
        const status = document.getElementById('prod-status').value;
        const material = document.getElementById('prod-material').value.trim();
        const description = document.getElementById('prod-description').value.trim();

        if (!name) {
            highlightAndNotify(document.getElementById('prod-name'), 'Vui lòng nhập đầy đủ <b>Tên sản phẩm</b>!');
            return;
        }
        if (name.length < 5) {
            highlightAndNotify(document.getElementById('prod-name'), 'Tên sản phẩm quá ngắn! Vui lòng nhập tối thiểu <b>5 ký tự</b>.', 'Thông tin chưa hợp lệ');
            return;
        }
        if (name.length > 120) {
            highlightAndNotify(document.getElementById('prod-name'), `Tên sản phẩm hiện có <b>${name.length} ký tự</b>, vượt quá giới hạn <b>120 ký tự</b> (theo chuẩn sàn TMĐT như Shopee/TikTok Shop).<br><br>Vui lòng rút gọn lại dưới 120 ký tự để lưu sản phẩm!`, 'Thông tin chưa hợp lệ');
            return;
        }
        if (!category_id) {
            highlightAndNotify(document.getElementById('prod-category'), 'Vui lòng chọn đầy đủ <b>Danh mục sản phẩm</b>!');
            return;
        }

        // Validate Bộ ảnh sản phẩm chính: Bắt buộc tối thiểu 1 ảnh để làm ảnh bìa
        if (!generalFiles.length) {
            highlightAndNotify(document.getElementById('general-gallery-grid'), 'Vui lòng chọn tối thiểu 1 ảnh cho <b>Bộ ảnh sản phẩm chính</b> để làm <b>Ảnh bìa</b> (tối đa 9 ảnh)!', 'Thiếu ảnh bìa sản phẩm');
            return;
        }

        if (!variantsList.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Thông báo',
                html: 'Sản phẩm phải có ít nhất 1 phân loại con!',
                confirmButtonColor: '#8D6E63'
            });
            return;
        }

        // Validate variants
        const nowBuffer = new Date(Date.now() - 60000);

        // Thu thập các nhóm màu đã có ảnh tải lên hoặc có sẵn ảnh hợp lệ
        const colorImagesMap = {};
        variantsList.forEach(v => {
            const cKey = (v.color || '').trim().toLowerCase();
            const hasImg = !!(v.file || (v.image_url && !v.image_url.includes('placehold.co')));
            if (hasImg && cKey) {
                colorImagesMap[cKey] = true;
            }
        });

        for (let i = 0; i < variantsList.length; i++) {
            const v = variantsList[i];
            const num = i + 1;
            const label = `Phân loại #${num} (${v.size || 'Size'} - ${v.color || 'Màu'})`;
            const cKey = (v.color || '').trim().toLowerCase();

            // Ràng buộc ảnh thông minh theo màu: Mỗi nhóm màu chỉ cần ít nhất 1 ảnh
            const hasImg = !!(v.file || (v.image_url && !v.image_url.includes('placehold.co')));
            const colorCovered = !!(cKey && colorImagesMap[cKey]);
            if (!hasImg && !colorCovered) {
                const thumbEl = document.getElementById(`var-thumb-${i}`);
                const msg = v.color 
                    ? `Nhóm màu <b>"${escapeHtml(v.color)}"</b> chưa có ảnh!<br><span style="font-size: 12.5px; color: #8D6E63;">(Vui lòng bấm vào icon máy ảnh để chọn ít nhất 1 ảnh cho màu này, các kích thước cùng màu sẽ tự động nhận chung ảnh)</span>`
                    : `Vui lòng tải ảnh cho <b>${label}</b>!`;
                highlightAndNotify(thumbEl, msg, 'Chưa có ảnh phân loại');
                return;
            }

            if (!v.size || !v.size.trim()) {
                const sizeEl = document.getElementById(`var-size-${i}`);
                highlightAndNotify(sizeEl, `Vui lòng nhập đầy đủ <b>Kích thước</b> cho <b>${label}</b>!`);
                return;
            }
            if (!/^\d+(\.\d+)?cm$/i.test(v.size.trim())) {
                const sizeEl = document.getElementById(`var-size-${i}`);
                highlightAndNotify(sizeEl, `Kích thước của <b>${label}</b> (<b>"${escapeHtml(v.size)}"</b>) chưa đúng định dạng!<br><br>Kích thước bắt buộc phải là số kèm đơn vị <b>"cm"</b> viết liền nhau (ví dụ: <b>30cm, 45cm</b>).<br><span style="color:#C62828;">(Dạng có khoảng trắng như <i>45 cm</i> hoặc thiếu chữ <i>cm</i> đều không hợp lệ)</span>`, 'Thông tin chưa hợp lệ');
                return;
            }
            if (!v.color || !v.color.trim()) {
                const colorEl = document.getElementById(`var-color-${i}`);
                highlightAndNotify(colorEl, `Vui lòng nhập đầy đủ <b>Màu sắc</b> cho <b>${label}</b>!`);
                return;
            }
            const vPrice = parseCurrencyToNumber(v.price);
            const vSalePrice = parseCurrencyToNumber(v.sale_price);

            if (v.price === '' || v.price === null || v.price === undefined || vPrice === null || isNaN(vPrice) || vPrice < 0) {
                const priceEl = document.getElementById(`var-price-${i}`);
                highlightAndNotify(priceEl, `Vui lòng nhập đầy đủ <b>Giá gốc</b> hợp lệ cho <b>${label}</b>!`);
                return;
            }
            if (v.stock_quantity === '' || v.stock_quantity === null || v.stock_quantity === undefined || parseInt(v.stock_quantity) < 0 || isNaN(parseInt(v.stock_quantity))) {
                const stockEl = document.getElementById(`var-stock-${i}`);
                highlightAndNotify(stockEl, `Vui lòng nhập đầy đủ <b>Số lượng tồn kho</b> cho <b>${label}</b>!`);
                return;
            }

            const hasSalePrice = (vSalePrice !== null && !isNaN(vSalePrice) && vSalePrice >= 0);
            const hasSaleTime = !!(v.sale_start_at || v.sale_end_at);

            // RÀNG BUỘC HAI CHIỀU GIỮA GIÁ SALE VÀ THỜI GIAN SALE:
            // Chiều 1: Nếu đã nhập giá sale -> BẮT BUỘC phải cài đặt thời gian sale (cả bắt đầu và kết thúc)
            if (hasSalePrice) {
                if (vSalePrice >= vPrice) {
                    const salePriceEl = document.getElementById(`var-sale-price-${i}`);
                    highlightAndNotify(salePriceEl, `Giá khuyến mãi của <b>${label}</b> phải nhỏ hơn giá gốc!`, 'Thông tin chưa hợp lệ');
                    return;
                }
                if (!v.sale_start_at || !v.sale_end_at) {
                    const salePriceEl = document.getElementById(`var-sale-price-${i}`);
                    highlightAndNotify(salePriceEl, `<b>${label}</b> đã nhập Giá khuyến mãi, BẮT BUỘC phải bấm nút <b>"Lịch sale"</b> để cài đặt cả Ngày bắt đầu và Ngày kết thúc!`, 'Thiếu thời gian sale');
                    return;
                }
            }

            // Chiều 2: Nếu đã cài đặt thời gian sale -> BẮT BUỘC phải nhập giá khuyến mãi
            if (hasSaleTime) {
                if (!hasSalePrice) {
                    const salePriceEl = document.getElementById(`var-sale-price-${i}`);
                    highlightAndNotify(salePriceEl, `<b>${label}</b> đã chọn thời gian sale, BẮT BUỘC phải nhập cả <b>Giá khuyến mãi</b> hợp lệ (>= 0 đ)!`, 'Thiếu giá khuyến mãi');
                    return;
                }
                if (!v.sale_start_at || !v.sale_end_at) {
                    const salePriceEl = document.getElementById(`var-sale-price-${i}`);
                    highlightAndNotify(salePriceEl, `<b>${label}</b> phải chọn đầy đủ cả Ngày bắt đầu và Ngày kết thúc sale!`, 'Thiếu thời gian sale');
                    return;
                }
            }

            if (v.sale_start_at && v.sale_end_at) {
                const start = new Date(v.sale_start_at);
                const end = new Date(v.sale_end_at);
                if (start < nowBuffer) {
                    const salePriceEl = document.getElementById(`var-sale-price-${i}`);
                    highlightAndNotify(salePriceEl, `Ngày bắt đầu sale của <b>${label}</b> phải từ thời điểm hiện tại trở đi!`, 'Thông tin chưa hợp lệ');
                    return;
                }
                if (end <= start) {
                    const salePriceEl = document.getElementById(`var-sale-price-${i}`);
                    highlightAndNotify(salePriceEl, `Ngày kết thúc sale của <b>${label}</b> phải sau ngày bắt đầu!`, 'Thông tin chưa hợp lệ');
                    return;
                }
            }
        }

        const formData = new FormData();
        formData.append('name', name);
        formData.append('category_id', category_id);
        formData.append('status', status);
        if (material) formData.append('material', material);
        if (description) formData.append('description', description);

        // Ảnh chung của sản phẩm cha
        let primaryIdx = 0;
        generalFiles.forEach((item, idx) => {
            formData.append('image_files[]', item.file);
            if (item.is_primary) primaryIdx = idx;
        });
        formData.append('primary_index', primaryIdx);

        // Đóng gói mảng biến thể
        variantsList.forEach((v, idx) => {
            formData.append(`variants[${idx}][sku]`, v.sku || '');
            formData.append(`variants[${idx}][size]`, v.size || '');
            formData.append(`variants[${idx}][color]`, v.color || '');
            formData.append(`variants[${idx}][price]`, parseCurrencyToNumber(v.price));
            if (v.sale_price !== '' && v.sale_price !== null && v.sale_price !== undefined) formData.append(`variants[${idx}][sale_price]`, parseCurrencyToNumber(v.sale_price));
            if (v.sale_start_at) formData.append(`variants[${idx}][sale_start_at]`, v.sale_start_at);
            if (v.sale_end_at) formData.append(`variants[${idx}][sale_end_at]`, v.sale_end_at);
            formData.append(`variants[${idx}][stock_quantity]`, v.stock_quantity ?? 0);
            formData.append(`variants[${idx}][is_default]`, (idx === 0) ? '1' : '0');
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
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu và tạo sản phẩm...';

        try {
            const res = await fetch('{{ route("admin.products.store") }}', {
                method: 'POST',
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
                    text: data.message || 'Đã thêm sản phẩm mới thành công!',
                    timer: 1800,
                    showConfirmButton: false
                });
                window.location.href = "{{ route('admin.products.index') }}";
            } else {
                let errorMsg = data.message || 'Không thể tạo sản phẩm';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('<br>');
                    if (data.errors.name) {
                        const nameInput = document.getElementById('prod-name');
                        if (nameInput) {
                            nameInput.classList.add('is-invalid');
                            nameInput.focus();
                            nameInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                    if (data.errors.category_id) {
                        const catInput = document.getElementById('prod-category');
                        if (catInput) {
                            catInput.classList.add('is-invalid');
                        }
                    }
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Thông tin chưa hợp lệ',
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

    function updateNameCounter(input) {
        input.classList.remove('is-invalid');
        const counter = document.getElementById('prod-name-counter');
        if (!counter) return;
        const len = input.value.length;
        counter.innerText = `${len}/120`;
        if (len >= 120) {
            counter.style.color = '#D32F2F';
            counter.style.background = '#FFEBEE';
            counter.style.borderColor = '#FFCDD2';
        } else if (len >= 100) {
            counter.style.color = '#E65100';
            counter.style.background = '#FFF3E0';
            counter.style.borderColor = '#FFE0B2';
        } else {
            counter.style.color = '#8D6E63';
            counter.style.background = '#FFF3E0';
            counter.style.borderColor = '#FFE0B2';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const prodNameInput = document.getElementById('prod-name');
        if (prodNameInput) updateNameCounter(prodNameInput);

        // Tự động gỡ bỏ viền đỏ highlight khi người dùng bắt đầu nhập / sửa lỗi
        document.addEventListener('input', (e) => {
            if (e.target && e.target.classList && e.target.classList.contains('is-invalid')) {
                e.target.classList.remove('is-invalid');
            }
        });
        document.addEventListener('change', (e) => {
            if (e.target && e.target.classList && e.target.classList.contains('is-invalid')) {
                e.target.classList.remove('is-invalid');
            }
        });
    });
</script>
@endsection
