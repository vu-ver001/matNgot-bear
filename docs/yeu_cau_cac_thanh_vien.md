# YÊU CẦU CHUẨN BỊ CHO CÁC THÀNH VIÊN
## DỰ ÁN: WEBSITE THƯƠNG MẠI ĐIỆN TỬ MẬT NGỌT BEAR
**Người soạn:** Anh Vũ (Người 4) — **Ngày:** 18/08/2026

> Backend Order/Admin/Staff + 10 views + seeders + tests đã hoàn thiện. Dưới đây là những gì từng thành viên cần làm để hệ thống chạy mượt và tích hợp được.

---

## 🔴 KHẨN CẤP — Tất cả thành viên

### 1. Setup máy (ai chưa clone)
```bash
composer install
cp .env.example .env            # sửa DB_CONNECTION=mysql, DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate        # mỗi người tự tạo key riêng
php artisan migrate --seed      # tạo bảng + dữ liệu mẫu (có sẵn admin/staff/customer/products/orders)
npm install && npm run dev
php artisan serve
```
- DB: **MySQL** (XAMPP). KHÔNG dùng SQLite vì dashboard dùng `MONTH()/YEAR()`.

### 2. GitHub quyền
- Cần cấp quyền **Write** cho mọi thành viên (Settings → Collaborators) mới tạo được Pull Request.

### 3. Quy trình Git
- **KHÔNG push thẳng vào `main`** — mỗi người tạo nhánh `feature/...` → Pull Request.
- File chung `routes/web.php` **đã được Anh Vũ sửa 2 lỗi** (xem mục V trong `docs/code_audit_status.md`) — ai cũng cần `git pull` trước khi code, tránh conflict.

---

## 👧 Kim Tuyến (Người 1) — Auth, Wishlist, Review, Live Chat

**Phần auth (đăng ký, đăng nhập, đổi mật khẩu, xác minh email):** code sẵn (Breeze) đã có trong repo — cô tự kiểm tra toàn bộ luồng chạy trên bảng `users` hiện tại và sửa nếu thấy lỗi. Bảng `users` là của cô, tự cân nhắc sửa schema nếu cần. *(Lưu ý: Anh Vũ đã sửa tạm `UserFactory` để hệ thống chạy được — ghi rõ trong audit mục III, cô có thể yêu cầu xóa bản demo đó và làm lại.)*

**Công việc module:**
- [ ] Viết logic `WishlistController` (index, toggle) — routes đang comment sẵn trong `routes/customer.php`
- [ ] Viết logic `ReviewController` phía khách (store) — kiểm tra khách chỉ review được sản phẩm thuộc đơn hàng **COMPLETED** của chính họ (Anh Vũ đã tăng `sold_count` ở bước này)
- [ ] Viết logic `ChatController` (customer + staff) — bảng `conversations`, `messages` đã có sẵn

---

## 👩 Khánh Vân (Người 2) — Sản phẩm, Danh mục, Tồn kho, Trang chủ

**🔴 Ưu tiên 1 (chặn customer đăng nhập):**
- [ ] **Tạo route `customer.products.index`** (trang chủ/trang sản phẩm của khách) — hiện `/dashboard` tạm chuyển customer sang trang đơn hàng; khi bạn làm xong, Anh Vũ sẽ đổi lại redirect. **Nhánh xong thì báo Anh Vũ.**

**Công việc module:**
- [ ] Viết logic `ProductController` + `CategoryController` phía khách (list, search, filter, detail)
- [ ] Bật routes admin: `Route::resource('categories', ...)` + `Route::resource('products', ...)` (đang comment trong `routes/admin.php`), viết `Admin\CategoryController` + `Admin\ProductController`
- [ ] **Quy ước tồn kho:** `OrderService` tự trừ `stock_quantity` khi tạo đơn, tự hoàn kho khi hủy đơn, tự tăng `sold_count` khi `COMPLETED`. **KHÔNG tự trừ/hoàn kho ở chỗ khác** (tránh trùng lặp).

---

## 🧑 Ngọc Anh (Người 3) — Giỏ hàng, Voucher, Checkout, Thanh toán

**Công việc module:**
- [ ] Viết logic `CartController` (index, add, update, remove) — routes comment sẵn trong `routes/customer.php`
- [ ] Viết logic `CheckoutController` (index, store)
  - ⚠️ **Tại `store()`: GỌI `OrderService::createOrder($orderData, $cartItems)`** — tuyệt đối không tự insert bảng `orders` (service lo transaction, trừ kho, voucher, phí ship 30.000đ, ghi lịch sử)
- [ ] Viết logic `VoucherController` admin (CRUD) — bật `Route::resource('vouchers', ...)` trong `routes/admin.php`
- [ ] **Lưu ý enum thanh toán:** bảng `orders.payment_method` chỉ chấp nhận: `COD`, `BANK_TRANSFER`, `E_WALLET`, `CARD` (KHÔNG có VNPAY/MOMO như tài liệu brainstorm). Form checkout phải dùng đúng các giá trị này.

---

## ✅ Anh Vũ (Người 4) — đã hoàn thành, chờ tích hợp
- OrderService + 10 views (Customer/Staff/Admin) + 6 seeders + 9 feature tests
- Tài khoản test: `admin@matngotbear.com` / `staff1@matngotbear.com` / `nguyenvana@example.com` (pass: `password`)
- Sẽ làm tiếp khi nhóm xong: báo cáo doanh thu theo ngày, tạo tài khoản Staff

---

## 🔗 Điểm tích hợp chính (xem chi tiết `docs/brainstorming_anh_vu.md` mục 6)

```
Ngọc Anh (Checkout) ──Gọi OrderService::createOrder()──► Anh Vũ (Order)
Khánh Vân (Sản phẩm) ◄──trừ/hoàn stock, sold_count── Anh Vũ (Order)
Kim Tuyến (Review)   ◄──điều kiện đơn COMPLETED── Anh Vũ (Order)
```

**Khi xong phần của mình:** tạo Pull Request, tag vào nhóm để cùng test luồng tổng thể.
