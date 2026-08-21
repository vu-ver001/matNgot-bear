# XÓA MỀM (SOFT DELETE) — MẬT NGỌT BEAR
**Người thực hiện:** Anh Vũ (Người 4) — **Ngày:** 21/08/2026
**Phạm vi:** Hạ tầng CSDL + Models (đã merge được ngay, không đụng module ai)

---

## 1. Bảng áp dụng xóa mềm

| Bảng | deleted_at | Ghi chú |
| :--- | :---: | :--- |
| `users` | ✅ | User xóa mềm **không đăng nhập lại được** (Eloquent global scope tự chặn) |
| `categories` | ✅ | `CategoryController::destroy()` tự chuyển thành xóa mềm |
| `products` | ✅ | Nút "Ngừng bán" hiện tại vẫn chỉ set `status=INACTIVE` (giữ nguyên của Khánh Vân); khi cần xóa thật chỉ cần gọi `$product->delete()` |
| `vouchers` | ✅ | Voucher xóa mềm không áp dụng được khi checkout (`Voucher::find()` tự loại) |
| `reviews` | ✅ | Review xóa mềm tự ẩn khỏi mọi query |

**KHÔNG áp dụng** cho `orders`, `order_details`, `payments`, `order_status_histories` — dữ liệu tài chính phải giữ nguyên vẹn.

## 2. Thay đổi ràng buộc unique (QUAN TRỌNG)

Migration `2026_08_21_000001_add_soft_deletes_to_core_tables.php` đã:

- **Drop unique** `users.email` → thay bằng index thường `users_email_index`
- **Drop unique** `vouchers.code` → thay bằng index thường `vouchers_code_index`
- **Drop unique** `reviews(user_id, product_id)` → thay bằng index thường (giữ index cho FK `user_id`, tránh lỗi MySQL 1553)

Lý do: unique thường chặn tái sử dụng email/mã sau khi bản ghi cũ bị xóa mềm. Tính duy nhất giờ do tầng validation đảm bảo:

```php
use Illuminate\Validation\Rule;

Rule::unique('users')->whereNull('deleted_at')          // email
Rule::unique('vouchers')->whereNull('deleted_at')       // voucher code
// Review: kiểm tra trùng bằng query whereNull('deleted_at'), KHÔNG dùng unique DB
```

`RegisteredUserController` đã cập nhật sẵn rule email theo mẫu trên.

## 3. Việc cần làm khi tích hợp (theo người)

### Anh Vũ — khi merge nhánh `feature/order-operation`
- [ ] `OrderService::restoreStock()` và chỗ tăng `sold_count` khi COMPLETED đang dùng `$detail->product` — với sản phẩm đã xóa mềm relation trả về `null` sẽ **crash**. Sửa thành:
```php
$product = $detail->product()->withTrashed()->first();
$product?->increment('stock_quantity', $detail->quantity);
```
- [ ] `createOrder()`: `Product::lockForUpdate()->find()` tự loại sản phẩm xóa mềm → khách không mua được sản phẩm đã xóa (đúng ý, không cần sửa).

### Kim Tuyến — Wishlist/Review/Chat
- [ ] `ReviewController::store()`: kiểm tra "mỗi user 1 review/sản phẩm" bằng query `Review::where('user_id',...)->where('product_id',...)->whereNull('deleted_at')->exists()` — **không còn unique DB nữa**.
- [ ] Auth: user bị xóa mềm tự mất phiên đăng nhập, không cần code thêm.

### Ngọc Anh — Voucher CRUD
- [ ] Validation `code` trong `VoucherController`: dùng `Rule::unique('vouchers', 'code')->whereNull('deleted_at')` (khi sửa thêm `->ignore($voucher->id)`).

### Khánh Vân — Product/Category
- Không bắt buộc sửa gì. Nếu muốn nút XÓA thật (thay vì ngừng bán): `$product->delete()` là xóa mềm, an toàn tuyệt đối với đơn hàng cũ (FK không vỡ).
- Lưu ý: danh mục có sản phẩm **đã xóa mềm** sẽ xóa được (count qua global scope = 0).

## 4. Đồng đội pull code về

```bash
git pull origin main
php artisan migrate        # KHÔNG cần migrate:fresh
```

## 5. Tests

- `tests/Feature/SoftDeleteTest.php` — 7 test, 20 assertions: PASS cả MySQL lẫn SQLite.
  - User xóa mềm: ẩn khỏi query, không đăng nhập được, email tái sử dụng được
  - Product: xóa mềm/khôi phục
  - Category: chặn xóa khi còn sản phẩm ACTIVE; cho xóa khi sản phẩm chỉ còn bản xóa mềm
  - Voucher: tái sử dụng mã sau xóa mềm
  - Review: tạo lại review sau khi xóa mềm review cũ
- Chạy MySQL:
```powershell
$env:DB_CONNECTION='mysql'; $env:DB_DATABASE='matngotbear_test'; $env:DB_USERNAME='root'; $env:DB_PASSWORD='24112005'
php artisan test --filter=SoftDeleteTest
```

> ⚠️ Các test Breeze cũ (`AuthenticationTest`, `ProfileTest`,...) đang FAIL do `UserFactory` sai schema (cột `name`) — lỗi có sẵn từ trước, thuộc phần auth của Kim Tuyến, xem `code_audit_status.md` mục III.
