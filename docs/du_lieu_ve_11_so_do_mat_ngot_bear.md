# DỮ LIỆU ĐẦU VÀO ĐỂ VẼ 11 SƠ ĐỒ HỆ THỐNG MẬT NGỌT BEAR

> **Học phần:** Phát triển hệ thống thương mại điện tử  
> **Đề tài:** Xây dựng website thương mại điện tử kinh doanh gấu nhồi bông  
> **Tên hệ thống:** Mật Ngọt Bear  
> **Thời điểm đối chiếu source code:** 19/09/2026  
> **Mục đích:** Tài liệu này cung cấp dữ liệu để một thành viên khác có thể vẽ các sơ đồ trong Chương 2 mà không phải tự suy diễn nghiệp vụ.

## 1. Nguyên tắc khi vẽ

1. Chỉ sử dụng actor, chức năng, class, bảng dữ liệu và dịch vụ ngoài được nêu trong tài liệu này.
2. Không thêm Repository, DTO, WebSocket, ứng dụng mobile hoặc AI chatbot vì source code hiện tại không có các thành phần này.
3. Kiến trúc xử lý thực tế:

```text
Browser/Blade
→ Laravel Route
→ Middleware xác thực/phân quyền
→ Controller
→ Service nếu nghiệp vụ có Service
→ Eloquent Model/Query Builder
→ MySQL hoặc SQLite
```

4. Chat hỗ trợ sử dụng HTTP polling, không sử dụng WebSocket.
5. Các trang `admin/customers`, `admin/staff` và `staff/order-status` hiện chỉ là trang placeholder, không vẽ như chức năng hoàn chỉnh.
6. Trong biểu đồ Use Case, Controller, Service và Database không phải là actor.

## 2. Danh sách actor thống nhất

| Actor | Loại | Chức năng chính |
|---|---|---|
| Guest | Người dùng chưa đăng nhập | Xem, tìm kiếm và lọc sản phẩm; đăng ký; đăng nhập; khôi phục mật khẩu |
| Customer | Khách hàng đã đăng nhập | Quản lý tài khoản, wishlist, giỏ hàng, voucher, checkout, thanh toán, đơn hàng, đánh giá và chat hỗ trợ |
| Staff | Nhân viên cửa hàng | Xử lý đơn hàng, thanh toán, đối soát COD, gửi yêu cầu hoàn tiền và xử lý ca hỗ trợ |
| Admin | Quản trị viên | Quản lý toàn bộ hệ thống, sản phẩm, danh mục, người dùng, voucher, đơn hàng, thanh toán, đánh giá, hỗ trợ và báo cáo |
| Google OAuth | Hệ thống ngoài | Cung cấp danh tính đăng nhập Google |
| SMTP/Mailer | Hệ thống ngoài | Gửi OTP đăng ký, quên mật khẩu và đổi email |
| GHN | Hệ thống ngoài | Tra cứu địa giới, phí vận chuyển và thời gian giao hàng |
| VNPAY | Hệ thống ngoài | Xử lý thanh toán VNPAY qua redirect, return và IPN |
| MoMo | Hệ thống ngoài | Xử lý thanh toán MoMo qua API, redirect và IPN |
| SePAY/Ngân hàng | Hệ thống ngoài | Gửi webhook hoặc cung cấp dữ liệu đối soát chuyển khoản |
| Scheduler | Actor tự động | Hủy đơn online chưa thanh toán và làm hết hạn yêu cầu hủy quá thời gian |

## 3. Các trạng thái nghiệp vụ cần dùng

### 3.1. Trạng thái đơn hàng

```text
PENDING → CONFIRMED → PREPARING → SHIPPING → COMPLETED
    │          │            │          │
    └──────────┴────────────┴──────────┴──→ CANCELLED (khi đủ điều kiện)

COMPLETED → RETURNED (khi yêu cầu trả hàng được chấp nhận)
```

Các trạng thái hợp lệ: `PENDING`, `CONFIRMED`, `PREPARING`, `SHIPPING`, `COMPLETED`, `RETURNED`, `CANCELLED`.

### 3.2. Trạng thái thanh toán

Các trạng thái chính: `UNPAID`, `PENDING`, `PAID`, `FAILED`, `REFUNDED`.

### 3.3. Trạng thái yêu cầu

Yêu cầu hủy, trả hàng và hoàn tiền sử dụng các trạng thái: `PENDING`, `APPROVED`, `REJECTED`.

### 3.4. Trạng thái hỗ trợ khách hàng

```text
WAITING → IN_PROGRESS → CLOSED
   ↑             │          │
   └── bàn giao ─┘          └── mở lại → WAITING hoặc IN_PROGRESS
```

## 4. Hình 2.1 – Biểu đồ tiến trình nghiệp vụ mua hàng trực tuyến

### 4.1. Swimlane nên sử dụng

```text
Khách hàng | Website Mật Ngọt Bear | GHN | Cổng thanh toán | Nhân viên/Admin
```

### 4.2. Luồng chính

1. Khách hàng truy cập website.
2. Hệ thống hiển thị danh mục và danh sách sản phẩm đang hoạt động.
3. Khách hàng tìm kiếm, lọc và xem chi tiết sản phẩm.
4. Khách hàng chọn sản phẩm, biến thể và số lượng.
5. Khách hàng thêm sản phẩm vào giỏ hàng.
6. Khách hàng chọn các dòng sản phẩm cần thanh toán.
7. Hệ thống yêu cầu đăng nhập nếu người dùng chưa xác thực.
8. Khách hàng nhập thông tin người nhận và địa chỉ giao hàng.
9. Hệ thống gửi thông tin địa chỉ, khối lượng hoặc giá trị cần thiết đến GHN.
10. GHN trả về phương thức, phí và thời gian dự kiến giao hàng.
11. Nếu GHN lỗi, hệ thống sử dụng phương án tính phí dự phòng.
12. Khách hàng chọn voucher đơn hàng hoặc voucher vận chuyển.
13. Hệ thống kiểm tra thời hạn, trạng thái, phạm vi và giới hạn sử dụng voucher.
14. Khách hàng chọn phương thức thanh toán.
15. Hệ thống kiểm tra lại sản phẩm, biến thể, giá bán và tồn kho.
16. `OrderService` mở transaction, khóa dữ liệu tồn kho và tạo đơn hàng.
17. Hệ thống tạo chi tiết đơn, lịch sử trạng thái và bản ghi thanh toán.
18. Hệ thống trừ tồn kho, tăng lượt dùng voucher và xóa các dòng giỏ hàng đã mua.
19. Nếu thanh toán COD, hệ thống chuyển khách hàng tới trang đặt hàng thành công.
20. Nếu thanh toán online, hệ thống chuyển khách hàng tới QR hoặc cổng thanh toán.
21. Cổng thanh toán gửi kết quả về return/IPN/webhook.
22. Hệ thống xác minh chữ ký, số tiền, mã đơn và trạng thái giao dịch.
23. Hệ thống cập nhật trạng thái thanh toán và đơn hàng.
24. Nhân viên/Admin tiếp nhận và xử lý đơn theo chuỗi trạng thái.
25. Khách hàng nhận hàng, xác nhận hoàn thành và có thể đánh giá sản phẩm.

### 4.3. Các decision bắt buộc

| Decision | Nhánh đúng | Nhánh sai |
|---|---|---|
| Người dùng đã đăng nhập? | Tiếp tục checkout | Chuyển tới đăng nhập |
| Sản phẩm/biến thể còn hoạt động? | Tiếp tục | Báo không khả dụng |
| Tồn kho đủ? | Tạo đơn | Báo thiếu hàng |
| Voucher hợp lệ? | Áp dụng giảm giá | Không áp dụng và báo lý do |
| GHN phản hồi thành công? | Dùng phí GHN | Dùng phương án dự phòng |
| Phương thức là COD? | Không redirect gateway | Chuyển sang luồng thanh toán online |
| Callback hợp lệ? | Cập nhật `PAID` | Giữ/chuyển `FAILED` và ghi log |

### 4.4. Dữ liệu thay đổi

`cart_items`, `orders`, `order_details`, `order_status_histories`, `payments`, `products`, `product_variants`, `vouchers`.

## 5. Hình 2.2 – Biểu đồ Use Case tổng quát

### 5.1. System boundary

Tên khung hệ thống: **Website thương mại điện tử Mật Ngọt Bear**.

### 5.2. Use Case theo actor

| Actor | Use Case liên kết |
|---|---|
| Guest | Xem sản phẩm; tìm kiếm/lọc sản phẩm; đăng ký; xác minh OTP; đăng nhập; đăng nhập Google; khôi phục mật khẩu |
| Customer | Quản lý hồ sơ; quản lý mật khẩu/email; quản lý wishlist; quản lý giỏ hàng; xem voucher; checkout; thanh toán; quản lý đơn cá nhân; đánh giá; chat hỗ trợ |
| Staff | Xem Dashboard vận hành; xử lý đơn; xử lý yêu cầu hủy; xác nhận/đối soát thanh toán; gửi yêu cầu hoàn tiền; xử lý ca hỗ trợ |
| Admin | Quản lý danh mục/sản phẩm/biến thể/ảnh; quản lý voucher; quản lý người dùng; quản lý đơn; quản lý thanh toán/hoàn tiền; kiểm duyệt đánh giá; điều phối hỗ trợ; xem Dashboard/báo cáo |
| Google OAuth | Đăng nhập Google |
| SMTP/Mailer | Gửi mã OTP |
| GHN | Tính phí vận chuyển |
| VNPAY | Thanh toán VNPAY |
| MoMo | Thanh toán MoMo |
| SePAY/Ngân hàng | Xác nhận chuyển khoản |
| Scheduler | Hủy đơn chưa thanh toán quá hạn; hết hạn yêu cầu hủy |

### 5.3. Quan hệ Use Case nên thể hiện

```text
Checkout <<include>> Kiểm tra giỏ hàng
Checkout <<include>> Tính phí vận chuyển
Checkout <<include>> Kiểm tra voucher
Checkout <<include>> Kiểm tra tồn kho

Thanh toán VNPAY <<extend>> Thanh toán đơn hàng
Thanh toán MoMo <<extend>> Thanh toán đơn hàng
Thanh toán chuyển khoản <<extend>> Thanh toán đơn hàng
Thanh toán COD <<extend>> Thanh toán đơn hàng

Xử lý đơn hàng <<include>> Kiểm tra chuyển trạng thái
Xử lý đơn hàng <<include>> Ghi lịch sử trạng thái

Đánh giá sản phẩm <<include>> Kiểm tra điều kiện đánh giá
```

## 6. Hình 2.3 – Use Case phân rã quản lý tài khoản và xác thực

### 6.1. Actor

Guest, Customer, Staff, Admin, Google OAuth và SMTP/Mailer.

### 6.2. Use Case

| Use Case | Actor | Điều kiện/kết quả chính |
|---|---|---|
| Đăng ký tài khoản | Guest | Tạo tài khoản role `CUSTOMER` khi dữ liệu và OTP hợp lệ |
| Gửi OTP đăng ký | Guest, Mailer | Lưu mã dạng bảo vệ và gửi email |
| Xác minh OTP đăng ký | Guest | Kiểm tra mã, thời hạn và số lần thử |
| Đăng nhập bằng email/mật khẩu | Guest | Chỉ tài khoản hoạt động được đăng nhập |
| Đăng nhập Google | Guest, Google OAuth | Tạo/liên kết tài khoản từ email Google đã xác minh |
| Đăng xuất | Customer/Staff/Admin | Hủy session và tạo lại CSRF token |
| Quên mật khẩu | Guest | Gửi OTP, xác minh và đặt mật khẩu mới |
| Đổi mật khẩu | Customer/Staff/Admin | Kiểm tra mật khẩu hiện tại và quy tắc mật khẩu mới |
| Cập nhật hồ sơ | Customer/Staff/Admin | Cập nhật họ tên, điện thoại, địa chỉ, avatar |
| Đổi email | Customer/Staff/Admin, Mailer | Gửi và xác minh mã tại email mới |
| Xóa tài khoản cá nhân | Người dùng đã đăng nhập | Yêu cầu mật khẩu đúng; áp dụng xóa mềm khi hợp lệ |

### 6.3. Thành phần nguồn

`routes/auth.php`, `routes/web.php`, các controller trong `app/Http/Controllers/Auth`, `ProfileKT`, `PasswordKT`, `OtpService`, `User`.

### 6.4. Bảng dữ liệu

`users`, `sessions`, `email_verification_codes`, `password_reset_codes`, `email_change_codes`.

## 7. Hình 2.4 – Use Case phân rã quản lý danh mục và sản phẩm

### 7.1. Actor

Guest, Customer và Admin.

### 7.2. Use Case phía người mua

```text
Xem danh mục
Xem danh sách sản phẩm
Tìm kiếm sản phẩm
Lọc theo danh mục/giá/thuộc tính
Sắp xếp sản phẩm
Xem sản phẩm nổi bật hoặc bán chạy
Xem chi tiết sản phẩm
Xem hình ảnh và các biến thể
```

### 7.3. Use Case phía Admin

```text
Thêm, xem, sửa, ngừng bán và khôi phục sản phẩm
Thêm, xem, sửa và xóa danh mục
Ghim danh mục và cấu hình menu đầu trang
Quản lý hình ảnh sản phẩm và ảnh đại diện
Quản lý biến thể theo kích thước/màu sắc
Quản lý SKU, giá, giá khuyến mãi, tồn kho và trạng thái biến thể
Xem thống kê sản phẩm và biến thể
```

### 7.4. Quan hệ và điều kiện

```text
Category 1 → N Product
Product 1 → N ProductImage
Product 1 → N ProductVariant
Danh mục có sản phẩm đang hoạt động không được xóa tùy tiện
Sản phẩm/biến thể không hoạt động không được mua mới
Biến thể phải đủ tồn kho trước khi thêm vào đơn
```

### 7.5. Bảng dữ liệu

`categories`, `products`, `product_images`, `product_variants`.

## 8. Hình 2.5 – Use Case phân rã giỏ hàng, wishlist và voucher

### 8.1. Actor

Customer và Admin; Staff chỉ có quyền xem kho voucher theo route hiện tại.

### 8.2. Wishlist

```text
Xem danh sách yêu thích
Thêm hoặc bỏ yêu thích
Xóa một sản phẩm
Xóa toàn bộ danh sách
Sắp xếp danh sách
Chuyển sản phẩm khả dụng sang giỏ hàng
```

### 8.3. Giỏ hàng

```text
Xem giỏ hàng
Thêm sản phẩm/biến thể
Cập nhật số lượng
Thay đổi biến thể
Xóa một dòng giỏ hàng
Xóa toàn bộ giỏ hàng
Xem số lượng sản phẩm trên badge
Chọn các dòng đưa sang checkout
```

### 8.4. Voucher

```text
Customer/Staff: xem voucher khả dụng
Customer: áp dụng voucher đơn hàng hoặc voucher vận chuyển
Admin: tạo, sửa, bật/tắt, xóa mềm, khôi phục và xóa vĩnh viễn voucher
Admin: cấu hình phạm vi toàn hệ thống, danh mục, sản phẩm hoặc biến thể
```

### 8.5. Business rule

```text
Số lượng giỏ không vượt quá tồn kho
Mỗi user chỉ có một dòng cho cùng product và variant
Voucher phải hoạt động và nằm trong thời gian hiệu lực
Giá trị đơn phải đạt mức tối thiểu
Không vượt giới hạn tổng lượt và lượt trên mỗi người dùng
Sản phẩm trong đơn phải thuộc phạm vi áp dụng voucher
```

### 8.6. Bảng dữ liệu

`cart_items`, `wishlist_items`, `vouchers`, `voucher_categories`, `voucher_products`, `voucher_product_variants`.

## 9. Hình 2.6 – Use Case phân rã đặt hàng và vận chuyển

### 9.1. Actor

Customer và GHN.

### 9.2. Use Case

```text
Chọn dòng giỏ hàng cần mua
Nhập thông tin người nhận
Chọn địa chỉ giao hàng
Tra cứu địa giới giao hàng
Tính phương án và phí vận chuyển
Chọn voucher đơn hàng
Chọn voucher vận chuyển
Chọn phương thức thanh toán
Xác nhận đặt hàng
Xem kết quả đặt hàng
```

### 9.3. Luồng xử lý nội bộ

```text
CheckoutController::process
→ validate dữ liệu checkout
→ ShippingService tính/kiểm tra phí
→ OrderService::createOrder
→ DB::transaction + lockForUpdate
→ tạo Order
→ tạo OrderDetail snapshot
→ trừ tồn kho
→ tăng lượt dùng voucher
→ tạo OrderStatusHistory
→ tạo/đảm bảo Payment
→ xóa cart item đã mua
→ commit hoặc rollback
```

### 9.4. Ngoại lệ

Địa chỉ thiếu hoặc không hợp lệ; GHN lỗi; sản phẩm bị khóa; biến thể đã xóa; tồn kho không đủ; voucher hết hạn hoặc sai phạm vi; giá sản phẩm thay đổi; lỗi khi tạo đơn.

### 9.5. Bảng dữ liệu

`cart_items`, `orders`, `order_details`, `order_status_histories`, `payments`, `products`, `product_variants`, `vouchers`.

## 10. Hình 2.7 – Use Case phân rã quản lý đơn hàng

### 10.1. Actor và quyền

| Use Case | Customer | Staff | Admin |
|---|---:|---:|---:|
| Xem đơn thuộc tài khoản | Có | Không áp dụng | Không áp dụng |
| Xem danh sách đơn vận hành | Không | Có | Có |
| Xem chi tiết/hóa đơn | Đơn của mình | Có | Có |
| Cập nhật địa chỉ giao | Khi trạng thái cho phép | Không | Không |
| Hủy trực tiếp | Khi `PENDING` và đủ điều kiện | Không | Có theo nghiệp vụ |
| Gửi/rút yêu cầu hủy | Có | Không | Không |
| Duyệt/từ chối yêu cầu hủy | Không | Có | Có |
| Cập nhật trạng thái | Không | Có | Có |
| Cập nhật hàng loạt | Không | Có | Có |
| Xác nhận đã nhận | Có | Không | Không |
| Yêu cầu trả hàng | Có | Xử lý nghiệp vụ liên quan | Xử lý nghiệp vụ liên quan |
| Đặt lại đơn | Có với đơn kết thúc | Không | Không |

### 10.2. Business rule

```text
Chỉ cho phép chuyển trạng thái theo chuỗi hợp lệ
Đơn trả trước chỉ được sang SHIPPING khi đã PAID
Mỗi lần đổi trạng thái phải tạo OrderStatusHistory
Hủy đơn phải hoàn tồn kho và lượt voucher đúng một lần
Cờ stock_restored ngăn hoàn kho lặp
Đơn online chưa thanh toán quá 24 giờ bị tự động hủy
Yêu cầu hủy quá 24 giờ bị tự động từ chối
```

### 10.3. Thành phần nguồn

Customer/Staff/Admin `OrderController`, `OrderService`, `CustomerOrderPresenter`, `CancelUnpaidOrdersCommand`, `ExpireCancelRequestsCommand`.

### 10.4. Bảng dữ liệu

`orders`, `order_details`, `order_status_histories`, `payments`, `products`, `product_variants`, `vouchers`.

## 11. Hình 2.8 – Use Case phân rã thanh toán và hoàn tiền

### 11.1. Actor

Customer, Staff, Admin, VNPAY, MoMo và SePAY/Ngân hàng.

### 11.2. Use Case Customer

```text
Chọn COD
Xem QR chuyển khoản/VietQR
Tạo giao dịch VNPAY
Tạo giao dịch MoMo
Kiểm tra trạng thái thanh toán
Thử lại hoặc làm mới QR khi đủ điều kiện
Xác nhận đã chuyển khoản
Xem kết quả thanh toán
```

### 11.3. Use Case Staff

```text
Xem danh sách thanh toán trong phạm vi vận hành
Xác nhận thanh toán thủ công kèm lý do/minh chứng
Đối soát hoặc bỏ đối soát COD
Đối soát COD hàng loạt
Xuất dữ liệu COD
Gửi yêu cầu hoàn tiền toàn phần
```

### 11.4. Use Case Admin

```text
Xem toàn bộ thanh toán và KPI tài chính
Xuất dữ liệu thanh toán
Kiểm tra giao dịch SePAY
Cập nhật trạng thái thanh toán khi hợp lệ
Duyệt hoặc từ chối yêu cầu hoàn tiền
Đánh dấu COD đã quyết toán
Cấu hình thông tin cổng thanh toán
```

### 11.5. Luồng gateway chung

```text
Customer → PaymentController → Payment Service → Gateway
Gateway → return/IPN/webhook → PaymentController
→ kiểm tra chữ ký/token + order code + amount + trạng thái cũ
→ cập nhật Payment và Order theo cách idempotent
→ trả kết quả cho gateway hoặc hiển thị trang kết quả
```

### 11.6. Bảng dữ liệu

`payments`, `payment_refund_requests`, `payment_settings`, `orders`, `users`.

## 12. Hình 2.9 – Use Case phân rã đánh giá và hỗ trợ khách hàng

### 12.1. Nhánh đánh giá

Actor: Customer và Admin.

```text
Customer xem sản phẩm chờ đánh giá
Customer kiểm tra điều kiện đánh giá
Customer lấy danh sách sản phẩm trong đơn
Customer tạo đánh giá cho một hoặc nhiều sản phẩm
Customer tải tối đa 5 ảnh cho mỗi đánh giá
Customer sửa đánh giá trong điều kiện cho phép
Customer xóa mềm đánh giá của chính mình
Admin xem và ẩn/hiện đánh giá
```

Điều kiện chính: đơn thuộc Customer; đơn đã hoàn thành; sản phẩm có trong đơn; còn trong thời hạn đánh giá; không tạo trùng đánh giá cho cùng sản phẩm trong cùng đơn; không sửa/xóa đánh giá của người khác.

### 12.2. Nhánh hỗ trợ khách hàng

Actor: Customer, Staff và Admin.

```text
Customer mở trang chat
Customer gửi nội dung hoặc hình ảnh
Hệ thống lấy/tạo Conversation
Hệ thống lấy/tạo SupportCase đang hoạt động
Hệ thống lưu Message
Staff xem hàng đợi và nhận ca
Staff/Admin gửi trả lời
Staff bàn giao hoặc đóng ca
Admin takeover, revoke hoặc assign ca
Customer hoặc nhân viên mở lại ca khi có nghiệp vụ mới
Frontend polling để lấy tin nhắn/trạng thái mới
```

FAQ tự động trong `ChatService` là logic nội bộ theo quy tắc, không phải dịch vụ AI bên ngoài.

### 12.3. Bảng dữ liệu

Đánh giá: `reviews`, `orders`, `order_details`, `products`, `users`.  
Hỗ trợ: `conversations`, `support_cases`, `messages`, `users`, `orders`.

## 13. Hình 2.10 – Biểu đồ lớp website Mật Ngọt Bear

### 13.1. Phạm vi khuyến nghị

Vẽ domain model chính. Có thể đặt các Service quan trọng ở một vùng riêng để tránh biểu đồ quá dày. Không cần đưa toàn bộ Controller vào biểu đồ lớp tổng thể.

### 13.2. Các lớp Model chính

| Class | Thuộc tính tiêu biểu | Quan hệ chính |
|---|---|---|
| User | id, full_name, email, phone, password, role, status, address, avatar | orders, reviews, cartItems, wishlistItems, conversations, messages |
| Category | id, name, description, is_active, is_pinned | hasMany Product; belongsToMany Voucher |
| Product | id, category_id, name, description, price, stock_quantity, status, sold_count | belongsTo Category; hasMany Variant/Image/Review/Cart/Wishlist |
| ProductVariant | id, product_id, sku, size, color, price, sale_price, stock_quantity, image_url, status | belongsTo Product; hasMany CartItem/OrderDetail |
| ProductImage | id, product_id, image_url, is_primary, sort_order | belongsTo Product |
| CartItem | id, user_id, product_id, product_variant_id, quantity | belongsTo User/Product/Variant |
| WishlistItem | id, user_id, product_id | belongsTo User/Product |
| Voucher | id, code, voucher_type, discount_type, discount_value, dates, limits, scope, status | belongsToMany Category/Product/Variant; hasMany Order |
| Order | id, order_code, customer_id, voucher_id, totals, order_status, payment_method, payment_status, request fields | belongsTo User/Voucher; hasMany Detail/Payment/History/Review |
| OrderDetail | id, order_id, product_id, product_variant_id, snapshot fields, price, quantity, line_total | belongsTo Order/Product/Variant |
| OrderStatusHistory | id, order_id, changed_by, from_status, to_status, note, changed_at | belongsTo Order/User |
| Payment | id, order_id, method, status, amount, transaction_ref, gateway_response, reconciliation fields | belongsTo Order/User; hasMany RefundRequest |
| PaymentRefundRequest | id, payment_id, order_id, requested_by, approved_by, amount, reason, status | belongsTo Payment/Order/User |
| PaymentSetting | id, key, value, group, description | Không có quan hệ domain bắt buộc |
| Review | id, product_id, user_id, order_id, rating, comment, images, is_hidden, is_edited | belongsTo Product/User/Order |
| Conversation | id, customer_id, staff_id, status | belongsTo User; hasMany Message/SupportCase |
| SupportCase | id, case_code, conversation_id, customer_id, assigned_staff_id, order_id, status, priority | belongsTo Conversation/User/Order; hasMany Message |
| Message | id, conversation_id, support_case_id, sender_id, content, images, is_read, sent_at | belongsTo Conversation/SupportCase/User |

### 13.3. Service nên thể hiện

| Service | Phụ thuộc/đối tượng sử dụng |
|---|---|
| OrderService | Order, OrderDetail, OrderStatusHistory, Payment, Product, ProductVariant, Voucher, DB transaction |
| ShippingService | GHN API và phương án phí dự phòng |
| VnpayService | Tạo URL và xác minh chữ ký VNPAY |
| MomoService | Tạo/xác minh giao dịch MoMo |
| SepayService | Tra cứu/đối soát SePAY |
| VietQrService | Tạo dữ liệu QR chuyển khoản |
| ReviewService | Review, Order, OrderDetail, Product |
| ChatService | Conversation, SupportCase, Message, User |
| WishlistService | WishlistItem, Product, User |
| DashboardService | Truy vấn tổng hợp User, Product, Order, Payment |

### 13.4. Cardinality chính

```text
Category 1 ── N Product
Product 1 ── N ProductVariant
Product 1 ── N ProductImage
User 1 ── N Order
User 1 ── N CartItem
User 1 ── N WishlistItem
User 1 ── N Review
Order 1 ── N OrderDetail
Order 1 ── N Payment
Order 1 ── N OrderStatusHistory
Order 1 ── N Review
Payment 1 ── N PaymentRefundRequest
Voucher N ── N Category/Product/ProductVariant
Conversation 1 ── N SupportCase
Conversation 1 ── N Message
SupportCase 1 ── N Message
```

## 14. Hình 2.11 – Mô hình cơ sở dữ liệu quan hệ

### 14.1. Các bảng nghiệp vụ phải có trong ERD

```text
users
categories
products
product_variants
product_images
cart_items
wishlist_items
vouchers
voucher_categories
voucher_products
voucher_product_variants
orders
order_details
order_status_histories
payments
payment_refund_requests
payment_settings
reviews
conversations
support_cases
messages
email_verification_codes
password_reset_codes
email_change_codes
```

Các bảng hạ tầng Laravel như `sessions`, `cache`, `jobs`, `failed_jobs` có thể đặt trong vùng riêng hoặc lược bỏ khỏi ERD nghiệp vụ nếu hình quá dày.

### 14.2. Khóa chính và khóa ngoại quan trọng

| Bảng | PK | FK chính |
|---|---|---|
| users | id | — |
| categories | id | — |
| products | id | category_id → categories.id |
| product_variants | id | product_id → products.id |
| product_images | id | product_id → products.id |
| cart_items | id | user_id → users.id; product_id → products.id; product_variant_id → product_variants.id |
| wishlist_items | id | user_id → users.id; product_id → products.id |
| voucher_categories | id | voucher_id → vouchers.id; category_id → categories.id |
| voucher_products | id | voucher_id → vouchers.id; product_id → products.id |
| voucher_product_variants | id | voucher_id → vouchers.id; product_variant_id → product_variants.id |
| orders | id | customer_id → users.id; voucher_id/shipping_voucher_id → vouchers.id |
| order_details | id | order_id → orders.id; product_id → products.id; product_variant_id → product_variants.id |
| order_status_histories | id | order_id → orders.id; changed_by → users.id |
| payments | id | order_id → orders.id; các trường người xác nhận/đối soát → users.id |
| payment_refund_requests | id | payment_id → payments.id; order_id → orders.id; requested_by/approved_by → users.id |
| reviews | id | product_id → products.id; user_id → users.id; order_id → orders.id |
| conversations | id | customer_id/staff_id → users.id |
| support_cases | id | conversation_id → conversations.id; customer_id/assigned_staff_id → users.id; order_id → orders.id |
| messages | id | conversation_id → conversations.id; support_case_id → support_cases.id; sender_id → users.id |
| email_change_codes | id | user_id → users.id |

### 14.3. Unique và bảng trung gian quan trọng

```text
users.email UNIQUE theo cơ chế hỗ trợ soft delete của project
product_variants.sku UNIQUE
orders.order_code UNIQUE
support_cases.case_code UNIQUE
payment_settings.key UNIQUE
cart_items: UNIQUE(user_id, product_id, product_variant_id)
wishlist_items: UNIQUE(user_id, product_id)
Các bảng voucher_categories, voucher_products và voucher_product_variants là bảng N:N
```

### 14.4. Soft delete

Các bảng/model có cơ chế xóa mềm cần thể hiện trường `deleted_at`: `users`, `categories`, `products`, `product_variants`, `vouchers`, `reviews`.

### 14.5. Schema quan hệ rút gọn

```text
USERS(id PK, email, full_name, phone, password, role, status, address, avatar, deleted_at)
CATEGORIES(id PK, name, is_active, is_pinned, header_menu_config, deleted_at)
PRODUCTS(id PK, category_id FK, name, description, price, stock_quantity, status, sold_count, deleted_at)
PRODUCT_VARIANTS(id PK, product_id FK, sku, size, color, price, sale_price, stock_quantity, status, deleted_at)
PRODUCT_IMAGES(id PK, product_id FK, image_url, is_primary, sort_order)
CART_ITEMS(id PK, user_id FK, product_id FK, product_variant_id FK NULL, quantity)
WISHLIST_ITEMS(id PK, user_id FK, product_id FK)
VOUCHERS(id PK, code, voucher_type, discount_type, discount_value, dates, limits, scope, status, deleted_at)
ORDERS(id PK, order_code, customer_id FK, voucher_id FK NULL, totals, order_status, payment_method, payment_status, request fields)
ORDER_DETAILS(id PK, order_id FK, product_id FK, product_variant_id FK NULL, snapshot fields, price, quantity, line_total)
PAYMENTS(id PK, order_id FK, method, status, amount, transaction_ref, gateway_response, reconciliation fields)
PAYMENT_REFUND_REQUESTS(id PK, payment_id FK, order_id FK, requested_by FK, approved_by FK NULL, amount, reason, status)
ORDER_STATUS_HISTORIES(id PK, order_id FK, changed_by FK NULL, from_status, to_status, note, changed_at)
REVIEWS(id PK, product_id FK, user_id FK, order_id FK, rating, comment, images, is_hidden, is_edited, deleted_at)
CONVERSATIONS(id PK, customer_id FK, staff_id FK NULL, status)
SUPPORT_CASES(id PK, case_code, conversation_id FK, customer_id FK, assigned_staff_id FK NULL, order_id FK NULL, status, priority)
MESSAGES(id PK, conversation_id FK, support_case_id FK NULL, sender_id FK, content, images, is_read, sent_at)
```

## 15. Tên hình sử dụng trong báo cáo

1. Hình 2.1: Biểu đồ tiến trình nghiệp vụ mua hàng trực tuyến.
2. Hình 2.2: Biểu đồ Use Case tổng quát của hệ thống.
3. Hình 2.3: Biểu đồ Use Case phân rã quản lý tài khoản và xác thực.
4. Hình 2.4: Biểu đồ Use Case phân rã quản lý danh mục và sản phẩm.
5. Hình 2.5: Biểu đồ Use Case phân rã quản lý giỏ hàng, danh sách yêu thích và voucher.
6. Hình 2.6: Biểu đồ Use Case phân rã đặt hàng và vận chuyển.
7. Hình 2.7: Biểu đồ Use Case phân rã quản lý đơn hàng.
8. Hình 2.8: Biểu đồ Use Case phân rã quản lý thanh toán và hoàn tiền.
9. Hình 2.9: Biểu đồ Use Case phân rã đánh giá và hỗ trợ khách hàng.
10. Hình 2.10: Biểu đồ lớp website thương mại điện tử Mật Ngọt Bear.
11. Hình 2.11: Mô hình cơ sở dữ liệu quan hệ của hệ thống Mật Ngọt Bear.

## 16. File nguồn dùng để kiểm tra lại

```text
routes/web.php
routes/auth.php
routes/customer.php
routes/staff.php
routes/admin.php
routes/api.php
routes/console.php
app/Http/Controllers/**
app/Http/Middleware/CheckRole.php
app/Services/**
app/Models/**
database/migrations/**
resources/views/**
resources/js/**
tests/Feature/**
```

## 17. Checklist nghiệm thu sơ đồ

- [ ] Tên actor thống nhất giữa tất cả Use Case.
- [ ] Customer, Staff và Admin không bị gộp quyền.
- [ ] Luồng đơn hàng dùng đúng trạng thái thực tế.
- [ ] Thanh toán online có return/IPN/webhook và bước xác minh.
- [ ] Sequence/Activity không tự thêm Repository.
- [ ] Chat được thể hiện là polling, không phải WebSocket.
- [ ] Class diagram khớp Model/Service thực tế.
- [ ] ERD khớp khóa ngoại trong migration.
- [ ] Các bảng N:N của voucher được thể hiện đầy đủ.
- [ ] Các bảng có soft delete được ghi `deleted_at`.
- [ ] Các trang placeholder không được mô tả như chức năng hoàn chỉnh.

