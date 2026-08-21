# BÁO CÁO KIỂM TRA HIỆN TRẠNG MÃ NGUỒN & TIẾN ĐỘ THỰC HIỆN
## DỰ ÁN: WEBSITE THƯƠNG MẠI ĐIỆN TỬ MẬT NGỌT BEAR
**Phân hệ phụ trách:** Đơn hàng (Order), Quản trị hệ thống (Admin) & Báo cáo Doanh thu (Analytics)
**Thành viên thực hiện:** **Anh Vũ** *(Người 4)*
**Cập nhật lần cuối:** 18/08/2026 (phiên 2 - hoàn thiện Views + Seeders + Tests)
**Tech Stack:** Laravel 13 (thực tế `composer.json`: `laravel/framework: ^13.17`), MySQL, Tailwind CSS, Blade Template, Alpine.js

---

## I. BẢNG TỔNG HỢP TIẾN ĐỘ TỔNG THỂ

| Tầng Kiến trúc (Layer) | Tỷ lệ Hoàn thành | Đánh giá Trạng thái | Ghi chú & Chi tiết |
| :--- | :---: | :---: | :--- |
| **1. Database Migrations** | **100%** | ✅ Hoàn tất | 16 bảng CSDL với đầy đủ Index, Foreign Key và Constraint. |
| **2. Eloquent Models** | **100%** | ✅ Hoàn tất | 14 Models đã định nghĩa đầy đủ Fillable, Casts và Relationships. |
| **3. Business Services** | **100%** | ✅ Hoàn tất | `OrderService.php` xử lý toàn bộ logic nghiệp vụ cốt lõi. |
| **4. Controllers (Anh Vũ)** | **100%** | ✅ Hoàn tất | Đã viết xong logic cho cả 5 Controller (Customer, Staff, Admin). |
| **5. Routing System** | **100%** | ✅ Hoàn tất | Đã tách route theo file: `customer.php`, `staff.php`, `admin.php`. |
| **6. Frontend Views (Blade)** | **100%** | ✅ **Hoàn tất phiên 2** | Đã tạo đủ **10 view** cho Customer/Staff/Admin (chi tiết mục II.5). |
| **7. Seeders (Dữ liệu mẫu)** | **100%** | ✅ **Hoàn tất phiên 2** | 6 seeder: AdminUser, Category, Product, Voucher, Order + DatabaseSeeder. |
| **8. Tests (Feature)** | **100%** | ✅ **Hoàn tất phiên 2** | `tests/Feature/OrderManagementTest.php` — 9 test, 25 assertions, PASS. |
| **9. Module của 3 Bạn khác** | **5%** | ⏳ Khung rỗng | Không thuộc phạm vi Anh Vũ, giữ nguyên. |

---

## II. CHI TIẾT CÁC PHẦN ANH VŨ ĐÃ XÂY DỰNG HOÀN CHỈNH

### 1. Database & Migrations (Đơn hàng & Lịch sử)
- `database/migrations/2026_08_17_073337_create_orders_table.php`: Chứa các trường nghiệp vụ quan trọng (`order_code`, `customer_id`, `subtotal`, `discount_amount`, `shipping_fee` = 30.000đ, `total_amount`, `order_status`, `payment_method`, `payment_status`, `stock_restored`, `cancel_reason`, `cancelled_by`, `confirmed_at`, `completed_at`).
- `database/migrations/2026_08_17_073338_create_order_details_table.php`: Snapshot thông tin sản phẩm tại thời điểm mua (`product_name`, `product_price`, `quantity`, `line_total`).
- `database/migrations/2026_08_17_073340_create_order_status_histories_table.php`: Lưu nhật ký thay đổi trạng thái (`from_status`, `to_status`, `changed_by`, `note`, `changed_at`).
- `database/migrations/2026_08_17_073339_create_payments_table.php`: Quản lý giao dịch thanh toán độc lập.

> **⚠️ LƯU Ý:** Enum `payment_method` trong bảng `orders` thực tế là `['COD', 'BANK_TRANSFER', 'E_WALLET', 'CARD']` — KHÔNG có `VNPAY`/`MOMO` như tài liệu brainstorm đã ghi. Khi tích hợp với Ngọc Anh cần thống nhất giá trị này.

### 2. Eloquent Models & Quan hệ dữ liệu
- **`App\Models\Order`:** `customer()`, `details()`, `payments()`, `latestPayment()`, `voucher()`, `statusHistories()`, `cancelledByUser()`.
- **`App\Models\OrderDetail`:** Quan hệ `order()`, `product()`.
- **`App\Models\OrderStatusHistory`:** Quan hệ `order()`, `changedByUser()`.
- **`App\Models\Payment`:** Quan hệ `order()`, `confirmedByUser()`.

### 3. Business Service Lõi: `App\Services\OrderService`
- `createOrder()`: Transaction + `lockForUpdate()` chống race condition, kiểm tra tồn kho, snapshot giá, tính voucher phía server, phí ship 30.000đ, ghi log khởi tạo.
- `cancelOrder()`: Chỉ hủy khi `PENDING`/`CONFIRMED`, hoàn kho idempotent qua cờ `stock_restored`.
- `updateStatus()`: Cập nhật timestamp (`confirmed_at`, `completed_at`, `cancelled_at`), tăng `sold_count` khi `COMPLETED`, hoàn kho khi `CANCELLED`.
- `createPayment()` / `confirmPayment()` / `markPaymentFailed()` / `refundPayment()`.

### 4. Controllers & Routing Đã Triển khai Xong
1. **`Customer\OrderController`:** `index()`, `show()` (chặn 403 nếu không phải chủ đơn), `cancel()`.
2. **`Staff\OrderController`:** `index()` (tìm kiếm mã đơn/tên/SĐT, lọc trạng thái), `show()`, `updateStatus()` (validate, bắt buộc lý do khi hủy).
3. **`Staff\DashboardController`:** Card thống kê vận hành + 10 đơn mới nhất.
4. **`Admin\DashboardController`:** Tổng doanh thu (chỉ `COMPLETED`+`PAID`), thống kê trạng thái, biểu đồ doanh thu 12 tháng, Top 10 sản phẩm bán chạy.
5. **`Admin\UserController`:** Danh sách user + lọc, `updateStatus()` (ACTIVE/BLOCKED), `updateRole()` (CUSTOMER/STAFF/ADMIN).
6. **`Admin\ReviewController`:** Danh sách review + lọc, toggle ẩn/hiện.
7. **`Staff\PaymentController` / `Admin\PaymentController`:** Xác nhận `PAID`/`FAILED`/`REFUNDED`.

> **⚠️ LƯU Ý:** `Admin\DashboardController@index` dùng `MONTH()`/`YEAR()` thuần SQL — chỉ chạy trên **MySQL**. Không dùng SQLite.

### 5. Frontend Views (Blade) — ✅ HOÀN TẤT PHIÊN 2 (10 views)

**Khách hàng (Customer):**
- [x] `resources/views/customer/orders/index.blade.php`: Danh sách đơn + tab lọc trạng thái, phân trang.
- [x] `resources/views/customer/orders/show.blade.php`: Chi tiết đơn, timeline trạng thái, tổng tiền, modal Hủy đơn (chỉ khi `PENDING`).

**Nhân viên (Staff):**
- [x] `resources/views/staff/dashboard/index.blade.php`: 5 card vận hành + bảng đơn mới.
- [x] `resources/views/staff/orders/index.blade.php`: Tìm kiếm + bộ lọc trạng thái đơn/thanh toán.
- [x] `resources/views/staff/orders/show.blade.php`: Chi tiết đơn + form đổi trạng thái (Alpine: hiện textarea lý do khi chọn Hủy) + xác nhận thanh toán + timeline lịch sử.

**Quản trị (Admin):**
- [x] `resources/views/admin/dashboard/index.blade.php`: 8 card thống kê + biểu đồ doanh thu 12 tháng (bar, thuần Tailwind) + Top 10 sản phẩm + đơn gần đây.
- [x] `resources/views/admin/orders/index.blade.php`: Giống staff nhưng route admin.
- [x] `resources/views/admin/orders/show.blade.php`: Chi tiết + cập nhật trạng thái + xác nhận thanh toán.
- [x] `resources/views/admin/users/index.blade.php`: Bảng user + lọc + form Khóa/Mở khóa + form đổi vai trò (khóa chính mình).
- [x] `resources/views/admin/reviews/index.blade.php`: Bảng review (sao ★, sản phẩm, khách, đơn) + toggle ẩn/hiện.

**Components dùng chung:**
- [x] `resources/views/components/order-status-badge.blade.php`
- [x] `resources/views/components/payment-status-badge.blade.php`
- [x] `resources/views/components/role-badge.blade.php`

**Navigation:**
- [x] `resources/views/layouts/navigation.blade.php`: Menu theo role (ADMIN: Dashboard/Đơn hàng/Người dùng/Đánh giá; STAFF: Dashboard/Đơn hàng; CUSTOMER: Đơn hàng của tôi) + sửa `Auth::user()->name` → `full_name`.

### 6. Seeders (Dữ liệu mẫu) — ✅ HOÀN TẤT PHIÊN 2 (6 seeder)
- [x] `AdminUserSeeder`: 1 ADMIN + 2 STAFF + 5 CUSTOMER (mật khẩu mặc định: `password`).
- [x] `CategorySeeder`: 5 danh mục gấu bông.
- [x] `ProductSeeder`: 10 sản phẩm + ảnh placeholder.
- [x] `VoucherSeeder`: 3 voucher (WELCOME10, GIAM50K, SALE20).
- [x] `OrderSeeder`: 15 đơn mẫu đủ trạng thái (PENDING x3, CONFIRMED x2, PREPARING x2, SHIPPING x2, COMPLETED x4, CANCELLED x2) — **đi qua `OrderService::createOrder()`** nên test được cả luồng trừ kho/voucher/ship.
- [x] `DatabaseSeeder`: Gọi đủ các seeder trên.

**Chạy:** `php artisan migrate --seed` → 8 users, 5 categories, 10 products, 3 vouchers, 15 orders.

**Tài khoản đăng nhập thử:**
| Role | Email | Password |
| :--- | :--- | :--- |
| Admin | admin@matngotbear.com | password |
| Staff | staff1@matngotbear.com | password |
| Customer | nguyenvana@example.com | password |

### 7. Tests — ✅ HOÀN TẤT PHIÊN 2
- [x] `tests/Feature/OrderManagementTest.php` (9 tests, 25 assertions):
  - 10 trang render 200 theo từng role.
  - Staff đổi trạng thái đơn + ghi lịch sử + xác nhận thanh toán.
  - Hủy đơn bắt buộc lý do.
  - Khách hủy đơn → hoàn tồn kho (idempotent).
  - Chặn xem đơn người khác (403).
  - Admin khóa user + toggle review.
  - Middleware role chặn khách vào `/admin` (403).

**Cách chạy (MySQL):**
```powershell
$env:DB_CONNECTION='mysql'; $env:DB_DATABASE='matngotbear_test'; $env:DB_USERNAME='root'; $env:DB_PASSWORD='24112005'
php artisan test --filter=OrderManagementTest
```
(Lưu ý: phpunit.xml mặc định dùng SQLite; dashboard dùng `MONTH()/YEAR()` nên phải chạy trên MySQL.)

---

## III. CÁC FIX ĐÃ THỰC HIỆN (PHIÊN 2) — CẦN THÔNG BÁO NHÓM

| File | Vấn đề phát hiện | Fix đã làm |
| :--- | :--- | :--- |
| `routes/web.php` | Route `/dashboard` thiếu `->name('dashboard')` → **toàn app lỗi** "Route [dashboard] not defined" ở navigation | ✅ Thêm `->name('dashboard')` |
| `resources/views/layouts/navigation.blade.php` | Dùng `Auth::user()->name` nhưng bảng users không có cột `name` (chỉ `full_name`) | ✅ Đổi sang `full_name` + thêm menu theo role |
| `database/factories/UserFactory.php` | Factory sai schema (dùng `name`, `email_verified_at`, `remember_token` không có trong bảng users) → **mọi `User::factory()` đều fail** | ⚠️ **DEMO phần auth (Kim Tuyến):** sửa khớp schema `full_name`, `phone`, `role`, `status`, `address` + state `admin()`, `staff()`, giữ `unverified()`. **Nếu nhóm không thỏa thuận → XÓA thay đổi này để Kim Tuyến tự làm lại** (nằm trong commit riêng, dễ revert) |
| `routes/web.php` (2) | Sau khi CUSTOMER đăng nhập, `/dashboard` redirect tới `customer.products.index` (chưa tồn tại — module Khánh Vân) → **lỗi 500 sau login** | ✅ Tạm redirect sang `customer.orders.index`. ⚠️ Khi Khánh Vân làm xong module sản phẩm, đổi lại thành `customer.products.index` |

---

## IV. CÔNG VIỆC CÒN LẠI / LƯU Ý CHO PHIÊN SAU

### 1. Vấn đề thuộc Kim Tuyến (Người 1) — Chặn auth của cả hệ thống ⚠️
- Cô tự kiểm tra và đảm bảo toàn bộ luồng auth (đăng ký, đăng nhập, đổi mật khẩu, xác minh email) chạy được trên bảng `users` hiện tại. Một số tính năng đang lỗi — Anh Vũ **không tự sửa** vì đây là module của cô (trừ UserFactory đã sửa bản demo — xem mục III).
- Test `PasswordResetTest`, `EmailVerificationTest` hiện FAIL — để lại cho cô xử lý.

### 2. Tích hợp liên module (Giai đoạn 4)
- [ ] Kết nối luồng Checkout của Ngọc Anh vào `OrderService::createOrder` (thống nhất giá trị `payment_method` enum).
- [ ] Kết nối luồng Đánh giá của Kim Tuyến với kiểm tra đơn hàng `COMPLETED`.
- [ ] Kiểm thử luồng hoàn kho khi hủy đơn (đã test cơ bản trong OrderManagementTest).

### 3. Việc tiềm năng tiếp theo cho Anh Vũ
- [ ] `Admin\DashboardController`: chuyển `MONTH()/YEAR()` sang cách tương thích đa DB (hoặc giữ nguyên vì nhóm dùng MySQL).
- [ ] Báo cáo doanh thu chi tiết theo ngày (bộ lọc ngày bắt đầu - kết thúc) theo `brainstorming_anh_vu.md` mục 5.3.
- [ ] Trang quản lý tạo tài khoản Staff (form tạo mới) theo `brainstorming_anh_vu.md` mục 5.3.

### 4. Ghi chú khác
- `docs/brainstorming.md` và `docs/brainstorming_anh_vu.md` gần như trùng nội dung — có thể gộp.
- Dashboard redirect `customer.products.index` đã tạm chuyển sang `customer.orders.index` (xem mục III) — khi Khánh Vân làm xong route sản phẩm thì đổi lại.
- Views sử dụng `route('dashboard')` làm link logo — đã fix ở mục III.

---

## V. HƯỚNG DẪN SETUP CHO THÀNH VIÊN CLONE REPO

```bash
composer install
cp .env.example .env            # sửa thông tin DB_CONNECTION=mysql, DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate        # mỗi người tự tạo APP_KEY riêng
php artisan migrate --seed      # tạo bảng + dữ liệu mẫu (admin/staff/customer/products/orders)
npm install
npm run dev                     # chạy Vite (hoặc npm run build)
php artisan serve               # http://localhost:8000
```

**Quy trình Git (theo brainstorming):** Không push thẳng vào `main`; tạo nhánh `feature/...` và tạo Pull Request. Collaborator cần quyền **Write** trên GitHub (Settings → Collaborators) mới tạo PR được.
