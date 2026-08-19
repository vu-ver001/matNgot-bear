# TODO 20/08 — Anh Vũ (Người 4)

## 1. Hoàn thiện User Management cho Admin (mục duy nhất còn thiếu ~96% -> 100%)
Trang /admin/users hiện CHỈ: danh sách + lọc, đổi vai trò, khóa/mở khóa (UserController: index, updateStatus, updateRole).

Cần thêm:
- [x] Route: POST /admin/users (tạo), GET+PUT/PATCH /admin/users/{user}/edit (sửa), DELETE /admin/users/{user} (xóa)
- [x] Form tạo tài khoản: full_name, email, password, role (STAFF/CUSTOMER), mặc định không khóa
- [x] Form sửa: full_name, email, role, mật khẩu (để trống = giữ nguyên)
- [x] Xóa: chỉ xóa CUSTOMER chưa có đơn; STAFF đang có đơn => chặn (thông báo)
- [x] Không cho tự xóa/khóa/đổi vai trò chính mình (admin đang đăng nhập)
- [x] View: trang tạo/sửa theo theme Mật Ngọt Bear (card amber, rounded-2xl)
- [x] Validation: email unique (trừ chính nó khi sửa), password min 8 khi tạo
- [x] Tests: tạo/sửa/xóa user, chặn xóa user có đơn, chặn tự xóa mình
- [x] npm run build nếu thêm class Tailwind mới (không thêm class mới — bỏ qua)
- [x] Chạy full test MySQL trước khi commit (chạy composer test sqlite: 52/52 pass; chưa chạy MySQL thật)

## 2. Lệch spec state machine — ĐÃ CHỐT
Docs (code_audit_status.md 4.2, brainstorming_anh_vu.md 5.3) cho phép PREPARING->CANCELLED, SHIPPING->CANCELLED.
Code đã sửa theo docs: `OrderService::assertValidTransition` cho phép PREPARING->CANCELLED và SHIPPING->CANCELLED; khi hủy có giao dịch PAID thì tự hoàn tiền (refund).

## 3. Phần đang chờ đồng đội (không phải việc của mình)
routes/customer.php còn comment: storefront (P2), cart/checkout (P3), wishlist/review/chat (P1).

## 4. Fix nhỏ sau nghiệm thu — ĐÃ XONG
- [x] Modal thêm/sửa user chuyển bố cục 2 cột (grid sm:grid-cols-2), thu gọn modal max-w-xl
- [x] Bảng "Top 10 sản phẩm bán chạy" dashboard admin: số thứ tự trang 2 sai (chunk(5) giữ keys gốc của product id nên $index không phải 0-4) -> đổi sang $loop->iteration (trang 1: 1-5, trang 2: 6-10)