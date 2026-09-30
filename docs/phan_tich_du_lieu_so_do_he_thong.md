# Bóc tách thông tin phục vụ thiết kế sơ đồ hệ thống Mật Ngọt Bear

> Phạm vi: source code tại thời điểm phân tích 17/09/2026. Tài liệu này mô tả implementation hiện có, chưa vẽ sơ đồ. Các nhận định quan trọng đều dẫn tới tệp làm căn cứ. `IMPLEMENTED`, `PARTIAL`, `DESIGNED_ONLY`, `NOT_FOUND` được dùng theo đúng nghĩa trong yêu cầu.

## 1. Project Overview

Mật Ngọt Bear là website thương mại điện tử bán gấu bông. Hệ thống phục vụ khách vãng lai xem sản phẩm; khách hàng quản lý tài khoản, giỏ hàng, wishlist, voucher, đặt hàng, thanh toán, theo dõi đơn, đánh giá và chat hỗ trợ; nhân viên vận hành đơn hàng, thanh toán và ca hỗ trợ; quản trị viên quản lý toàn bộ nghiệp vụ, danh mục, sản phẩm, người dùng, voucher, đánh giá, báo cáo và cấu hình thanh toán.

### Công nghệ và cách triển khai

| Thành phần | Thực tế trong project | Bằng chứng |
|---|---|---|
| Backend | PHP 8.3+, Laravel 13.17, Eloquent ORM | `composer.json`, `app/Models`, `app/Http/Controllers` |
| Frontend | Blade server-rendered, JavaScript, Alpine.js, Tailwind CSS, Vite | `resources/views`, `resources/js`, `package.json`, `vite.config.js` |
| Database | SQLite là mặc định cấu hình; Docker dùng MySQL 8 | `config/database.php`, `compose.yaml`, `.env.docker.example` |
| Session/cache/queue | Có bảng database cho session, cache và queue; queue mặc định là `database` | migrations `0001_*`, `config/queue.php` |
| File storage | Laravel local/public disk; ảnh avatar, review, chat, chứng từ | `config/filesystems.php`, các controller/service upload |
| Email | Laravel Mail; OTP đăng ký, quên mật khẩu, đổi email | `app/Mail`, các controller Auth/Profile |
| Authentication | Session auth, email verification, Google OAuth (Socialite), OTP | `routes/auth.php`, `app/Http/Controllers/Auth` |
| Deployment | Docker multi-stage: Nginx, PHP-FPM, MySQL; volume DB và storage | `Dockerfile`, `compose.yaml`, `docker/` |
| Kiểm thử | PHPUnit feature tests cho auth, user, order, payment, GHN, review, wishlist, chat | `tests/Feature` |

Hệ thống có 218 route do `php artisan route:list --json` ghi nhận. Không có SPA framework, mobile client, WebSocket server hay repository class riêng. Trong kiến trúc hiện tại, Eloquent Model và query builder đảm nhiệm vai trò truy cập dữ liệu.

## 2. Actors

| Actor | Loại | Mô tả | Quyền/khả năng | Bằng chứng |
|---|---|---|---|---|
| Guest | Người dùng chưa xác thực | Xem trang chủ, tìm/lọc/xem sản phẩm; đăng ký, đăng nhập, khôi phục mật khẩu | Chỉ các route public/guest | `routes/web.php`, `routes/auth.php`, `routes/api.php` |
| CUSTOMER | Role nội bộ | Người mua đã đăng nhập | Hồ sơ, giỏ, wishlist, voucher, checkout, thanh toán, đơn hàng, review, chat | `routes/customer.php`, `app/Http/Middleware/CheckRole.php` |
| STAFF | Role nội bộ | Nhân viên vận hành/CSKH | Đơn hàng, xác nhận/đối soát thanh toán, yêu cầu hoàn tiền, xử lý support case | `routes/staff.php` |
| ADMIN | Role nội bộ | Quản trị viên | Toàn bộ quản trị; duyệt hoàn tiền; cấu hình cổng thanh toán; takeover/assign support case | `routes/admin.php`, `routes/api.php` |
| Google OAuth | Hệ thống ngoài | Cung cấp danh tính đăng nhập | Redirect và callback OAuth | `GoogleAuthController.php`, `config/services.php` |
| SMTP/Mailer | Dịch vụ ngoài | Chuyển OTP qua email | Nhận email từ Laravel Mail | `config/mail.php`, `app/Mail` |
| GHN | Dịch vụ ngoài | Tra cứu địa giới, phí và thời gian giao hàng | REST API; có fallback ma trận vùng | `app/Services/ShippingService.php` |
| VNPAY | Cổng thanh toán | Nhận thanh toán thẻ/ATM/QR | Redirect, browser return và server IPN | `VnpayService.php`, `Customer/PaymentController.php` |
| MoMo | Cổng thanh toán | Nhận thanh toán ví điện tử | Tạo giao dịch, redirect và IPN | `MomoService.php`, `Customer/PaymentController.php` |
| SePAY/ngân hàng | Dịch vụ đối soát | Webhook và tra cứu giao dịch chuyển khoản | REST/webhook | `SepayService.php`, `Customer/PaymentController.php` |
| Scheduler | Tiến trình nền | Hủy đơn online chưa thanh toán quá 24 giờ | Chạy 15 phút/lần | `routes/console.php`, `CancelUnpaidOrdersCommand.php` |

`Controller`, `Service`, `Model` và database là participant nội bộ, không phải actor.

## 3. Functional Modules

| Module | Phạm vi thực tế | Trạng thái | Căn cứ chính |
|---|---|---|---|
| Catalog | Danh mục, sản phẩm, ảnh, biến thể, tìm kiếm/lọc, featured | IMPLEMENTED | `ProductController`, `CategoryController`, `ProductPublicController` |
| Authentication | Đăng ký OTP, đăng nhập, Google OAuth, xác minh email, quên mật khẩu OTP, logout | IMPLEMENTED | `routes/auth.php`, controller trong `Auth/` |
| Profile & security | Sửa hồ sơ/avatar/email, đổi mật khẩu, xóa tài khoản | IMPLEMENTED | `ProfileController`, `ProfileEmailController`, `PasswordController` |
| Cart | Thêm/sửa/xóa/clear, chọn biến thể, đếm sản phẩm | IMPLEMENTED | `Customer/CartController.php` |
| Wishlist | Toggle, danh sách, xóa, clear | IMPLEMENTED | hai `WishlistController`, `WishlistService` |
| Voucher | Quản trị voucher; áp dụng theo đơn/vận chuyển và theo category/product/variant | IMPLEMENTED | `VoucherController`, `Voucher.php` |
| Checkout & shipping | Chọn dòng giỏ hàng, địa chỉ, phí GHN/fallback, voucher, tạo đơn | IMPLEMENTED | `CheckoutController`, `ShippingService`, `OrderService` |
| Order lifecycle | Xem đơn, cập nhật trạng thái, hủy/yêu cầu hủy, nhận hàng, đổi trả, đặt lại | IMPLEMENTED | ba `OrderController`, `OrderService`, `Order.php` |
| Payment | COD, bank transfer/VietQR/SePAY, MoMo, VNPAY, retry, đối soát, refund | IMPLEMENTED | payment controllers/services/models |
| Review | Điều kiện đánh giá theo đơn, tạo/sửa/xóa mềm, ảnh, admin ẩn/hiện | IMPLEMENTED | `ReviewService`, review controllers |
| Support chat | Conversation, support case, tin nhắn/ảnh, nhận ca, bàn giao, gán, đóng/mở | IMPLEMENTED | `ChatService`, chat controllers |
| User management | Tạo/sửa/xóa mềm, khóa/mở người dùng | IMPLEMENTED | `Admin/UserController.php` |
| Dashboard & report | Thống kê vận hành, doanh thu, xuất dữ liệu | IMPLEMENTED | `DashboardService`, dashboard/report controllers |
| Placeholder admin/staff | Trang customers/staff/order-status/page tổng quát | DESIGNED_ONLY | closure trả `admin.placeholder`/`staff.placeholder` trong routes |

## 4. Use Case Extraction

### Danh sách use case

| ID | Use Case | Actor | Module | API/route chính | Trạng thái |
|---|---|---|---|---|---|
| UC-01 | Xem, tìm kiếm, lọc sản phẩm | Guest, CUSTOMER | Catalog | `GET /`, `/products`, `/api/products*` | IMPLEMENTED |
| UC-02 | Đăng ký và xác minh OTP email | Guest | Authentication | `POST /register`, `/register/send-code`, `/register/verify-code` | IMPLEMENTED |
| UC-03 | Đăng nhập thường/Google và đăng xuất | Guest, CUSTOMER/STAFF/ADMIN, Google | Authentication | `/login`, `/auth/google*`, `/logout` | IMPLEMENTED |
| UC-04 | Khôi phục/đổi mật khẩu | Guest, user | Authentication | `/forgot-password/*`, `/account/password` | IMPLEMENTED |
| UC-05 | Quản lý hồ sơ, avatar và email | user | Profile | `/profile*` | IMPLEMENTED |
| UC-06 | Quản lý wishlist | CUSTOMER | Wishlist | `/customer/wishlist*` | IMPLEMENTED |
| UC-07 | Quản lý giỏ hàng và biến thể | CUSTOMER | Cart | `/customer/cart*` | IMPLEMENTED |
| UC-08 | Xem và áp dụng voucher | CUSTOMER, STAFF | Voucher | `/customer/vouchers`, checkout | IMPLEMENTED |
| UC-09 | Tính phí vận chuyển | CUSTOMER, GHN | Shipping | `/customer/checkout/calculate-shipping` | IMPLEMENTED |
| UC-10 | Checkout và tạo đơn | CUSTOMER | Order | `POST /customer/checkout/process` | IMPLEMENTED |
| UC-11 | Thanh toán online/chuyển khoản/COD | CUSTOMER, VNPAY, MoMo, SePAY | Payment | `/customer/payment/*`, `/payment/*`, `/webhook/*` | IMPLEMENTED |
| UC-12 | Theo dõi và thao tác đơn cá nhân | CUSTOMER | Order | `/customer/orders*` | IMPLEMENTED |
| UC-13 | Đánh giá sản phẩm đã mua | CUSTOMER | Review | `/customer/reviews*` | IMPLEMENTED |
| UC-14 | Gửi yêu cầu hỗ trợ/chat | CUSTOMER | Support | `/customer/messages*` | IMPLEMENTED |
| UC-15 | Xử lý đơn hàng | STAFF, ADMIN | Order | `/staff/orders*`, `/admin/orders*` | IMPLEMENTED |
| UC-16 | Xử lý/đối soát thanh toán | STAFF, ADMIN, SePAY | Payment | `/staff/payments*`, `/admin/payments*` | IMPLEMENTED |
| UC-17 | Yêu cầu và duyệt hoàn tiền | STAFF, ADMIN | Payment | refund-request/approve/reject routes | IMPLEMENTED |
| UC-18 | Tiếp nhận và điều phối ca hỗ trợ | STAFF, ADMIN | Support | `/staff/support*`, `/admin/support*` | IMPLEMENTED |
| UC-19 | Quản lý danh mục/sản phẩm/biến thể/ảnh | ADMIN | Catalog | `/admin/products*`, `/api/admin/*` | IMPLEMENTED |
| UC-20 | Quản lý voucher | ADMIN | Voucher | `/admin/vouchers*` | IMPLEMENTED |
| UC-21 | Quản lý user và trạng thái tài khoản | ADMIN | User | `/admin/users*` | IMPLEMENTED |
| UC-22 | Kiểm duyệt review | ADMIN | Review | `/admin/reviews*` | IMPLEMENTED |
| UC-23 | Xem dashboard, báo cáo và xuất doanh thu | ADMIN | Reporting | `/admin`, `/admin/reports/revenue*` | IMPLEMENTED |
| UC-24 | Tự hủy đơn online quá hạn | Scheduler | Order | `orders:cancel-unpaid` | IMPLEMENTED |
| UC-25 | Quản lý riêng danh sách khách/nhân viên | ADMIN | User | `/admin/customers`, `/admin/staff` | DESIGNED_ONLY |

### Quan hệ và phân rã nên dùng

- UC-10 `<<include>>` UC-09 vì checkout gọi tính/kiểm tra phương án vận chuyển; đồng thời bao gồm kiểm tra giỏ hàng, tồn kho và voucher.
- UC-11 mở rộng sau UC-10 theo `payment_method`; VNPAY, MoMo và chuyển khoản là các nhánh thay thế, COD không redirect cổng ngoài.
- UC-12 có các mở rộng theo trạng thái: hủy trực tiếp, yêu cầu hủy, xác nhận đã nhận, yêu cầu trả, đặt lại và in hóa đơn.
- UC-15 bao gồm xác thực phép chuyển trạng thái và ghi `order_status_histories`.
- UC-17 do STAFF tạo yêu cầu, ADMIN duyệt/từ chối; đây là hai use case liên quan qua trạng thái refund request, không nên nhập làm một actor chung.
- UC-18 nên phân rã thành nhận ca, bàn giao, takeover/revoke/assign, gửi tin và đóng/mở lại.

Nên vẽ một Use Case tổng quát với bốn actor người dùng và các hệ thống ngoài; sau đó tách: Authentication & Account, Shopping & Checkout, Order & Payment, Review & Support, Staff Operations, Admin Management.

## 5. Activity Flow Extraction

### ACT-01 — Đăng ký bằng OTP email

- Actor bắt đầu: Guest. Trigger: gửi form đăng ký.
- Tiền điều kiện: email chưa tồn tại; dữ liệu đạt `RegisterCustomerRequest`.
- Luồng chính: nhập thông tin → yêu cầu OTP → hệ thống tạo/lưu mã và gửi Mail → xác minh mã → tạo `users` role `CUSTOMER` → đăng nhập/điều hướng.
- Nhánh: validation lỗi; email đã tồn tại; OTP sai/hết hạn; gửi mail lỗi.
- Dữ liệu: `email_verification_codes`, `users`; có thể xóa/đánh dấu mã sau dùng.
- Thành phần: views `auth/registrationKT`, registration controllers, `Auth/SharedKT/OtpService`, `RegistrationVerificationCodeMail`.

### ACT-02 — Thêm sản phẩm vào giỏ và checkout

- Actor: CUSTOMER. Trigger: chọn sản phẩm/biến thể, sau đó checkout.
- Tiền điều kiện: đã đăng nhập role CUSTOMER; product/variant `ACTIVE`; số lượng hợp lệ và đủ kho.
- Luồng: thêm/cập nhật `cart_items` → chọn dòng mua → nhập người nhận → tính phí vận chuyển → chọn voucher/phương thức → validate lại → `OrderService::createOrder` khóa product/variant → trừ kho → tạo order/details/history → tăng lượt voucher → tạo payment → xóa dòng giỏ đã mua.
- Nhánh: biến thể bị xóa/khóa; thiếu tồn; voucher sai phạm vi/hết hạn/hết lượt; phí vận chuyển fallback; cổng online redirect thất bại.
- Hậu điều kiện: order `PENDING`, payment ban đầu `UNPAID`/`PENDING` theo luồng; snapshot tên/giá/variant nằm trong `order_details`.
- Dữ liệu: `cart_items`, `orders`, `order_details`, `order_status_histories`, `payments`, `vouchers`, `products`/`product_variants`.

### ACT-03 — Thanh toán online

- Actor: CUSTOMER; actor ngoài: VNPAY hoặc MoMo.
- Luồng: chọn phương thức → tạo/đảm bảo payment → service ký request → redirect → gateway trả browser return và gọi IPN → controller kiểm chữ ký, order, số tiền và tính idempotent → cập nhật payment/order → hiển thị kết quả.
- Nhánh: chữ ký sai; order không tồn tại; số tiền lệch; gateway từ chối; callback lặp; quá cửa sổ 24 giờ.
- Trạng thái: payment `PENDING → PAID|FAILED`; order payment status `UNPAID/PENDING → PAID|FAILED`.

### ACT-04 — Đơn hàng và yêu cầu hủy

- Actor: CUSTOMER, STAFF hoặc ADMIN.
- Luồng trạng thái chuẩn: `PENDING → CONFIRMED → PREPARING → SHIPPING → COMPLETED`; từ `COMPLETED → RETURNED` khi quy trình trả được duyệt.
- CUSTOMER hủy trực tiếp khi `PENDING`; khi `CONFIRMED` gửi `cancel_request_status=PENDING`; STAFF/ADMIN duyệt hoặc từ chối. Hủy hoàn kho và hoàn lượt voucher một lần bằng cờ `stock_restored`.
- Điều kiện đặc biệt: đơn không COD chỉ sang `SHIPPING` khi `payment_status=PAID`.
- Nhánh: chuyển trạng thái không hợp lệ; pending cancel; đơn đã thanh toán cần refund.

### ACT-05 — Xác nhận nhận hàng và yêu cầu trả

- Actor: CUSTOMER; STAFF/ADMIN xử lý.
- Tiền điều kiện: order thuộc customer, trạng thái phù hợp.
- Luồng: customer xác nhận nhận (`customer_confirmed_at`) hoặc gửi lý do trả (`return_request_status=PENDING`) → vận hành duyệt/từ chối → nếu duyệt chuyển `RETURNED` và xử lý tài chính/kho theo controller/service.
- Nhánh: trạng thái không hợp lệ, yêu cầu trùng, lý do thiếu.

### ACT-06 — Đối soát và hoàn tiền

- STAFF có thể xác nhận thủ công, đối soát COD và tạo `payment_refund_requests`.
- ADMIN có thể kiểm tra SePAY, đổi trạng thái payment, duyệt/từ chối refund và xác nhận COD đã về tài khoản.
- Refund request: `PENDING → APPROVED|REJECTED`; khi duyệt lưu `approved_by/approved_at`, khi từ chối lưu `admin_note/rejected_at`.

### ACT-07 — Đánh giá sản phẩm

- Actor: CUSTOMER. Điều kiện được kiểm tra bởi `ReviewService::checkEligibility`: đơn/sản phẩm thuộc user, trạng thái phù hợp, chưa đánh giá.
- Luồng: lấy dữ liệu order → nhập rating/comment/ảnh → validate → lưu review → có thể sửa/xóa mềm; ADMIN toggle ẩn/hiện.
- Nhánh: không đủ điều kiện; review trùng; ảnh không hợp lệ; quá điều kiện sửa (`Review::canBeEdited`).

### ACT-08 — Support case/chat

- CUSTOMER gửi nội dung/ảnh → lấy hoặc tạo conversation → lấy/tạo active support case `WAITING` → lưu message.
- STAFF nhận ca: `WAITING → IN_PROGRESS`, gửi tin, bàn giao hoặc đóng `CLOSED`; ADMIN có thêm takeover, revoke và assign.
- Frontend gọi endpoint `poll`; không có WebSocket. Service đánh dấu đã đọc và tính unread.
- Có FAQ auto-reply trong `ChatService`; đây là logic nội bộ, không thấy tích hợp AI bên ngoài.

### ACT-09 — Tự hủy đơn quá hạn

- Scheduler chạy `orders:cancel-unpaid` mỗi 15 phút, chống chạy chồng.
- `OrderService::cancelExpiredUnpaidOrders` tìm đơn thanh toán online chưa hoàn tất quá 24 giờ, hủy và khôi phục kho/voucher.

| Activity nên vẽ | Actor | Bắt đầu | Decision chính | Kết thúc | Ưu tiên |
|---|---|---|---|---|---|
| Checkout/tạo đơn | CUSTOMER | Gửi checkout | tồn kho, voucher, shipping, payment method | Order được tạo hoặc rollback | Rất cao |
| Thanh toán online | CUSTOMER, gateway | Redirect | chữ ký, amount, idempotency | PAID/FAILED | Rất cao |
| Vòng đời đơn/hủy | CUSTOMER, STAFF, ADMIN | Thao tác trạng thái | transition, payment, cancel request | trạng thái mới/history | Rất cao |
| Hoàn tiền/đối soát | STAFF, ADMIN | Tạo request/xác nhận | quyền và trạng thái | APPROVED/REJECTED/settled | Cao |
| Support case | CUSTOMER, STAFF, ADMIN | Gửi tin/nhận ca | quyền xử lý, trạng thái case | đóng/mở/bàn giao | Cao |
| Đăng ký OTP | Guest | Gửi email | OTP hợp lệ | User được tạo | Trung bình |
| Review | CUSTOMER, ADMIN | Chọn đơn/sản phẩm | eligibility | review được lưu/ẩn | Trung bình |
| Hủy đơn quá hạn | Scheduler | Mỗi 15 phút | quá 24 giờ/chưa trả | hủy và hoàn tài nguyên | Trung bình |

## 6. Sequence Flow Extraction

Project không có lớp Repository riêng. Trong các sequence dưới đây, service/controller gọi Eloquent Model trực tiếp; không được thêm participant Repository giả.

### SEQ-01 — Checkout và tạo đơn

| Thứ tự | Participant | Class/file thực tế | Vai trò |
|---|---|---|---|
| 1 | CUSTOMER | Browser/Blade | Gửi form |
| 2 | Checkout UI | `resources/views/customer/checkout/index.blade.php` | Thu thập dữ liệu |
| 3 | Controller | `Customer/CheckoutController::process` | Validate, chuẩn hóa payment/shipping |
| 4 | Shipping | `ShippingService` | Tính phí GHN hoặc fallback |
| 5 | Domain service | `OrderService::createOrder` | Transaction, khóa và thay đổi dữ liệu |
| 6 | Data access | Eloquent models | Query/update database |
| 7 | Payment service | `VnpayService`/`MomoService` khi phù hợp | Tạo URL/request cổng |

- Request: `POST /customer/checkout/process`; middleware `web, auth, role:CUSTOMER`; body gồm địa chỉ nhận, các cart item, voucher, shipping và payment method.
- Response: redirect success/QR/gateway hoặc validation/error redirect. Controller không dùng Response DTO riêng.
- Transaction: tạo `orders`, `order_details`, `order_status_histories`; trừ kho; tăng voucher; xử lý cart/payment. `OrderService` dùng `DB::transaction` và `lockForUpdate`.

### SEQ-02 — VNPAY callback/IPN

CUSTOMER → `PaymentController::redirectToVnpay` → `VnpayService::createPaymentUrl` → VNPAY → `/payment/vnpay/return` và `/payment/vnpay/ipn` → `PaymentController` → `Order`/`Payment` → DB → response/result. Nhánh lỗi gồm chữ ký, order code, amount, response code và giao dịch đã xử lý.

### SEQ-03 — MoMo callback/IPN

CUSTOMER → `PaymentController::redirectToMomo` → `MomoService::createPayment` → MoMo API → return/IPN → kiểm chữ ký và amount → cập nhật `payments`/`orders` → result. Endpoint tạo giao dịch dùng HTTP timeout; lỗi mạng/gateway được bắt và ghi log.

### SEQ-04 — Bank transfer/SePAY

CUSTOMER → trang QR (`VietQrService`) → ngân hàng/SePAY → public webhook `PaymentController::handleWebhook` → xác thực token/secret, parse nội dung/amount → tìm order/payment → cập nhật PAID. STAFF/ADMIN có thể gọi `SepayService` để kiểm tra giao dịch thủ công.

### SEQ-05 — Cập nhật trạng thái đơn

STAFF/ADMIN → Blade form → `Staff|Admin/OrderController::updateStatus` → `OrderService::updateOrderStatus` → `Order::canTransitionTo` → Eloquent models → `orders` và `order_status_histories` → redirect/JSON. Nhánh `SHIPPING` từ chối nếu đơn trả trước chưa PAID.

### SEQ-06 — Support chat

CUSTOMER → `customer-chat.js`/Blade → `ChatController::send` → `ChatService::customerSendMessage` → `Conversation`, `SupportCase`, `Message` → DB. STAFF/ADMIN → `staff-chat.js` → `StaffChatController` → cùng service/models. Client gọi poll định kỳ để nhận tin mới; ảnh được lưu public disk.

### SEQ-07 — Review

CUSTOMER → `ReviewKT/index.js`/Blade → `ReviewController` → `ReviewService` → `Order`, `Product`, `Review`, `User` → DB/public storage → JSON/redirect. Eligibility được kiểm tra trước create/update.

### SEQ-08 — OTP email

Guest/user → auth/profile controller → `Auth/SharedKT/OtpService` hoặc service OTP liên quan → model code → DB → Laravel `Mail` → SMTP/log mailer → người dùng → verify endpoint → model code/user → DB.

| Sequence nên vẽ | Endpoint tiêu biểu | Controller | Service | Data access | External |
|---|---|---|---|---|---|
| Checkout | `POST customer/checkout/process` | CheckoutController | OrderService, ShippingService | Eloquent models | GHN |
| VNPAY | `payment/vnpay/*` | PaymentController | VnpayService | Order, Payment | VNPAY |
| MoMo | `payment/momo/*` | PaymentController | MomoService | Order, Payment | MoMo |
| Chuyển khoản | `webhook/sepay` | PaymentController | VietQrService, SepayService | Order, Payment | SePAY/bank |
| Vòng đời đơn | `PATCH staff|admin/orders/{order}/status` | OrderController | OrderService | Order, history, stock | N/A |
| Chat support | `*/support/*`, `customer/messages*` | Chat controllers | ChatService | Conversation, case, message | N/A |
| Review | `customer/reviews*` | ReviewController | ReviewService | Review, Order, Product | public storage |

## 7. Class/Domain Model Extraction

### Class chính

| Class | Loại | Thuộc tính/trách nhiệm quan trọng | Quan hệ/method đáng vẽ |
|---|---|---|---|
| User | Entity | identity, role, status, profile, verification | orders, cartItems, wishlistItems, reviews, conversations, sentMessages |
| Category | Entity | name, active, pin/header menu config | hasMany Product; belongsToMany Voucher |
| Product | Entity | giá, sale, kho tổng hợp, status, soft delete | category, images, variants, cart/wishlist/order/review, price/stock helpers |
| ProductVariant | Entity | SKU, size, color, price/sale, stock, image, status | Product; cart/order details; vouchers; soft delete |
| ProductImage | Entity | URL, primary, sort order | belongsTo Product |
| CartItem | Entity | user/product/variant/quantity | effective price/stock/image |
| WishlistItem | Entity | user/product | belongsTo User/Product |
| Voucher | Entity/domain-rich model | type, scope, discount, date/limits/status | category/product/variant pivots, orders, validation logic |
| Order | Entity/domain-rich model | totals, shipping, order/payment/cancel/return status | transition state machine; details, payments, history, reviews |
| OrderDetail | Entity/snapshot | product and variant snapshot, quantity, prices | Order, Product, ProductVariant |
| OrderStatusHistory | Entity | from/to, changed_by, note/time | Order, User |
| Payment | Entity | method/status/amount/ref/proof, COD reconcile/settle | Order, users, refund requests |
| PaymentRefundRequest | Entity | amount, bank details, status, approval | Payment, Order, requester, approver |
| PaymentSetting | Entity/config | unique key/value/group | static get/set behavior |
| Review | Entity | rating/comment/images/hidden/edited | Product, User, Order; soft delete |
| Conversation | Entity | customer/staff/status | messages, support cases, active case |
| SupportCase | Entity/domain model | code, handler, order, status/priority/channel | conversation, customer, staff, messages; permission helpers |
| Message | Entity | content, image(s), read/sent state | Conversation, SupportCase, sender |
| OrderService | Domain/application service | create/cancel/update/expire; transaction and stock/voucher restoration | depends on order-related models |
| ShippingService | Integration service | GHN mapping/fee/leadtime and fallback | depends on HTTP/cache/config |
| ChatService | Domain service | case lifecycle, permissions, messaging, unread, FAQ | chat/order/user models |
| ReviewService | Domain service | eligibility and review/image lifecycle | review/order/product/user models |
| Payment services | Integration services | VNPAY, MoMo, SePAY, VietQR, settings | HTTP/config/payment models |

### Quan hệ class/entity

| Class A | Quan hệ | Class B | Cardinality | Bằng chứng |
|---|---|---|---|---|
| Category | hasMany | Product | 1:N | `Category::products`, `Product::category` |
| Product | hasMany | ProductVariant/ProductImage | 1:N | model relations và FK migrations |
| User | hasMany | CartItem/WishlistItem | 1:N | model relations |
| User | hasMany | Order | 1:N | `orders.customer_id` |
| Order | hasMany | OrderDetail/Payment/History/Review | 1:N | model relations |
| Product | hasMany | OrderDetail/Review | 1:N | model relations |
| Voucher | belongsToMany | Category/Product/ProductVariant | N:N | three pivot migrations/models |
| Order | belongsTo | Voucher | N:0..1, hai vai trò order/shipping | `voucher_id`, `shipping_voucher_id` |
| Conversation | belongsTo | User | N:1 customer; N:0..1 staff | conversation migration/model |
| Conversation | hasMany | SupportCase/Message | 1:N | model relations |
| SupportCase | belongsTo | Conversation/User/Order | N:1 / optional staff/order | migration/model |
| SupportCase | hasMany | Message | 1:N | `messages.support_case_id` |
| Payment | hasMany | PaymentRefundRequest | 1:N | model relation and FK |

## 8. ERD Data

Các bảng nghiệp vụ và cột chính sau là trạng thái sau chuỗi migration. `created_at/updated_at` được lược bớt khi không ảnh hưởng quan hệ; cần giữ lại khi vẽ physical schema.

| Table | PK | FK | Cột nghiệp vụ/constraint chính |
|---|---|---|---|
| users | id | — | full_name, email UNIQUE, phone, password, role enum, status enum, address, avatar, google_id, email_verified_at, last_login_at, deleted_at |
| categories | id | — | name, description, is_active, is_pinned, header menu config, deleted_at |
| products | id | category_id→categories | name, description, price/sale_price, legacy size/color/material/stock, status, sold_count, deleted_at |
| product_variants | id | product_id→products | sku UNIQUE, size, color, price/sale_price, sale dates, stock_quantity, image_url, status, deleted_at; composite indexes |
| product_images | id | product_id→products | image_url LONGTEXT, is_primary, sort_order |
| cart_items | id | user_id→users, product_id→products, product_variant_id→variants nullable | quantity; UNIQUE(user,product,variant) |
| wishlist_items | id | user_id→users, product_id→products | UNIQUE(user,product) |
| vouchers | id | — | code UNIQUE, voucher_type, discount_type/value, min/max, dates, usage limits/count, apply_scope, status, deleted_at |
| voucher_categories | id | voucher_id, category_id | unique pair, cascade |
| voucher_products | id | voucher_id, product_id | unique pair, cascade |
| voucher_product_variants | id | voucher_id, product_variant_id | unique pair, cascade |
| orders | id | customer_id, voucher_id, shipping_voucher_id, cancelled_by→users | order_code UNIQUE; recipient; subtotal/discount/shipping/total; order/payment state; cancellation/return/refund fields; timestamps; stock_restored |
| order_details | id | order_id, product_id, product_variant_id nullable | product/variant snapshot, current/original price, quantity, line_total |
| payments | id | order_id, confirmed_by, cod_reconciled_by, cod_settled_by→users | method, status, amount, transaction_ref, gateway_response, proof/note and payment/COD timestamps |
| payment_refund_requests | id | payment_id, order_id, requested_by, approved_by→users | amount, reason/proof, bank info, status enum, admin note/timestamps |
| payment_settings | id | — | key UNIQUE, value, group, description |
| order_status_histories | id | order_id, changed_by→users | from_status, to_status, note, changed_at |
| reviews | id | product_id, user_id, order_id | rating, comment, images, hidden/edited, deleted_at; UNIQUE(user,product) |
| conversations | id | customer_id, staff_id→users | status OPEN/CLOSED |
| support_cases | id | conversation_id, customer_id, assigned_staff_id, order_id | case_code UNIQUE, status, priority, channel, activity/open/close timestamps |
| messages | id | conversation_id, support_case_id, sender_id | content nullable, image_url, images JSON, is_read, read_at, sent_at |
| email_verification_codes | id | — | email/code/expiry/verification fields theo migration |
| password_reset_codes | id | — | email/code/expiry/verification fields theo migration |
| email_change_codes | id | user_id→users | old/new email, code/expiry fields |

Các bảng hạ tầng Laravel: `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `migrations`.

### Cardinality ERD

| Table A | Cardinality | Table B | FK |
|---|---|---|---|
| categories | 1:N | products | products.category_id |
| products | 1:N | product_variants | product_variants.product_id |
| products | 1:N | product_images | product_images.product_id |
| users | 1:N | cart_items/wishlist_items/orders/reviews | respective user/customer FK |
| orders | 1:N | order_details/payments/histories/reviews/refund_requests | order_id |
| products | 1:N | cart_items/wishlist_items/order_details/reviews | product_id |
| product_variants | 1:N | cart_items/order_details | product_variant_id nullable |
| vouchers | N:N | categories/products/product_variants | pivot tables |
| vouchers | 1:N | orders | voucher_id và shipping_voucher_id |
| conversations | 1:N | messages/support_cases | conversation_id |
| support_cases | 1:N | messages | messages.support_case_id nullable |
| payments | 1:N | payment_refund_requests | payment_id |

Cascade đáng chú ý: category→product, product→images/variants, user→cart/wishlist/orders, order→details/payments/history/reviews/refund; nhiều FK người xử lý dùng `nullOnDelete`. Product, variant, category, voucher, user và review có soft delete theo model/migration tương ứng.

## 9. Relational Schema

```text
USER(id PK, email UNIQUE, full_name, phone, password, role, status, address,
     avatar, google_id, email_verified_at, last_login_at, deleted_at)
CATEGORY(id PK, name, description, is_active, is_pinned, header_menu_config, deleted_at)
PRODUCT(id PK, category_id FK->CATEGORY.id, name, description, price, sale_price,
        size, color, material, stock_quantity, status, sold_count, deleted_at)
PRODUCT_VARIANT(id PK, product_id FK->PRODUCT.id, sku UNIQUE, size, color, price,
                sale_price, sale_start_at, sale_end_at, stock_quantity, image_url,
                status, deleted_at)
PRODUCT_IMAGE(id PK, product_id FK->PRODUCT.id, image_url, is_primary, sort_order)
CART_ITEM(id PK, user_id FK->USER.id, product_id FK->PRODUCT.id,
          product_variant_id FK->PRODUCT_VARIANT.id NULL, quantity,
          UNIQUE(user_id, product_id, product_variant_id))
WISHLIST_ITEM(id PK, user_id FK->USER.id, product_id FK->PRODUCT.id,
              UNIQUE(user_id, product_id))
VOUCHER(id PK, code UNIQUE, voucher_type, discount_type, discount_value,
        min_order_value, max_discount_value, start_date, end_date, usage_limit,
        usage_limit_per_user, used_count, apply_scope, status, deleted_at)
VOUCHER_CATEGORY(id PK, voucher_id FK->VOUCHER.id, category_id FK->CATEGORY.id)
VOUCHER_PRODUCT(id PK, voucher_id FK->VOUCHER.id, product_id FK->PRODUCT.id)
VOUCHER_PRODUCT_VARIANT(id PK, voucher_id FK->VOUCHER.id,
                        product_variant_id FK->PRODUCT_VARIANT.id)
ORDER(id PK, order_code UNIQUE, customer_id FK->USER.id,
      voucher_id FK->VOUCHER.id NULL, shipping_voucher_id FK->VOUCHER.id NULL,
      recipient_*, subtotal, discount_amount, shipping_discount_amount,
      shipping_fee, shipping_method, total_amount, order_status, payment_method,
      payment_status, cancel_*, return_*, refund_*, stock_restored, *_at)
ORDER_DETAIL(id PK, order_id FK->ORDER.id, product_id FK->PRODUCT.id,
             product_variant_id FK->PRODUCT_VARIANT.id NULL, product/variant snapshot,
             product_price, original_unit_price, quantity, line_total)
PAYMENT(id PK, order_id FK->ORDER.id, method, status, amount, transaction_ref,
        gateway_response, proof_image, note, confirmed_by FK->USER.id NULL,
        cod_reconciled_by FK->USER.id NULL, cod_settled_by FK->USER.id NULL, *_at)
PAYMENT_REFUND_REQUEST(id PK, payment_id FK->PAYMENT.id, order_id FK->ORDER.id,
                       requested_by FK->USER.id, approved_by FK->USER.id NULL,
                       amount, reason, proof_image, bank_*, status, admin_note, *_at)
ORDER_STATUS_HISTORY(id PK, order_id FK->ORDER.id, changed_by FK->USER.id NULL,
                     from_status, to_status, note, changed_at)
REVIEW(id PK, product_id FK->PRODUCT.id, user_id FK->USER.id,
       order_id FK->ORDER.id, rating, comment, images, is_hidden, is_edited,
       deleted_at, UNIQUE(user_id, product_id))
CONVERSATION(id PK, customer_id FK->USER.id, staff_id FK->USER.id NULL, status)
SUPPORT_CASE(id PK, case_code UNIQUE, conversation_id FK->CONVERSATION.id,
             customer_id FK->USER.id, assigned_staff_id FK->USER.id NULL,
             order_id FK->ORDER.id NULL, status, priority, channel, *_at)
MESSAGE(id PK, conversation_id FK->CONVERSATION.id,
        support_case_id FK->SUPPORT_CASE.id NULL, sender_id FK->USER.id,
        content, image_url, images, is_read, read_at, sent_at)
PAYMENT_SETTING(id PK, key UNIQUE, value, group, description)
```

## 10. System Architecture

### Client và frontend

- Browser nhận HTML do Laravel Blade render. CSS/JS được Vite build; Alpine.js hỗ trợ tương tác nhẹ.
- Router là Laravel route phía server, không có React/Vue Router. JavaScript dùng `fetch`/form tới web/API endpoints.
- State chủ yếu nằm trong session, database và DOM; không có Redux/Vuex/Pinia.
- CSRF áp dụng cho web routes nhưng có danh sách loại trừ webhook/payment trong `bootstrap/app.php`.

### Backend

- Nginx → PHP-FPM → Laravel route/middleware → controller → service khi có → Eloquent Model/query builder → database.
- Authentication dựa trên session guard; authorization theo middleware `role` và kiểm tra ownership trong controller/service.
- Form Request được dùng ở một số miền auth/profile/product/category/review/chat; các controller khác validate trực tiếp.
- Exception handling trả JSON cho `/api/*` hoặc request kỳ vọng JSON.

### Database và storage

- Local có thể dùng SQLite; Docker chính thức trong repo dùng MySQL 8 qua PDO MySQL.
- Ảnh public nằm dưới `storage/app/public` và được liên kết tới `public/storage`.
- Queue database được cấu hình nhưng chưa tìm thấy Job nghiệp vụ riêng. Scheduler chạy Artisan command trực tiếp.

### External systems và giao tiếp

| Hệ thống | Giao tiếp | Dữ liệu/giá trị |
|---|---|---|
| Google OAuth | HTTPS OAuth redirect/callback qua Socialite | danh tính Google |
| SMTP | SMTP/Laravel Mail | OTP email |
| GHN | REST JSON | địa giới, phí, lead time |
| VNPAY | signed redirect + return/IPN | order, amount, transaction result |
| MoMo | REST JSON + redirect/IPN, HMAC signature | order, amount, transaction result |
| SePAY | REST/webhook JSON | lịch sử và thông báo chuyển khoản |
| VietQR | URL/QR data | tài khoản, số tiền, nội dung chuyển khoản |

## 11. Backend–Frontend Architecture

Luồng chung thực tế:

```text
Browser → Laravel Route/Middleware → Controller → Service (nếu có)
        → Eloquent Model/Query Builder → MySQL hoặc SQLite
        → Blade/JSON/Redirect → Browser
```

| Frontend feature | API/route | Controller | Service | Model/table chính |
|---|---|---|---|---|
| Home/shop/product | `/`, `/products*`, `/api/products*` | Customer/Product, Api/ProductPublic | logic controller/model | products, variants, images, categories, reviews |
| Auth | `/register*`, `/login`, `/auth/google*`, `/forgot-password*` | Auth controllers | OTP service, Socialite | users, verification/reset codes |
| Profile | `/profile*`, `/account/password` | Profile/Password controllers | OTP/Mail | users, email_change_codes |
| Cart | `/customer/cart*` | CartController | trực tiếp model | cart_items, products, variants |
| Wishlist | `/customer/wishlist*` | Wishlist controllers | WishlistService một phần | wishlist_items, products |
| Checkout | `/customer/checkout*` | CheckoutController | ShippingService, OrderService | cart/order/payment/voucher/product tables |
| Orders | `/customer/orders*` | Customer/OrderController | OrderService | orders, details, history, payment |
| Payment | `/customer/payment*`, `/payment*`, `/webhook*` | Customer/PaymentController | VNPAY, MoMo, SePAY, VietQR | payments, orders, settings |
| Reviews | `/customer/reviews*` | ReviewKT/ReviewController | ReviewService | reviews, orders, products |
| Chat | `/customer/messages*`, `*/support*` | Chat controllers | ChatService | conversations, support_cases, messages |
| Admin catalog | `/admin/products*`, `/api/admin/*` | Admin Product/Category | chủ yếu trực tiếp model | catalog tables/pivots |
| Admin users/vouchers | `/admin/users*`, `/admin/vouchers*` | Admin User/Voucher | UserService một phần/model | users, vouchers/pivots |
| Operations | `/staff/orders*`, `/admin/orders*` | Staff/Admin Order | OrderService | orders/details/history/stock |
| Finance | `/staff/payments*`, `/admin/payments*` | Staff/Admin Payment | payment/settings services | payments/refunds/settings/orders |
| Dashboard/report | `/admin`, `/staff`, `/admin/reports*` | dashboard/report controllers | DashboardService | aggregate orders/payments/users/products |

Không có DTO response chuyên biệt trên phần lớn luồng; Blade, redirect session flash và array/JSON trực tiếp là dạng response thực tế.

## 12. External Systems

- Google OAuth: IMPLEMENTED; cần credential môi trường.
- Email SMTP: IMPLEMENTED; mặc định `MAIL_MAILER=log` nếu không cấu hình SMTP, nên khả năng gửi thật phụ thuộc môi trường.
- GHN: IMPLEMENTED với fallback `REGIONAL_MATRIX`; thiếu token không chặn checkout.
- VNPAY: IMPLEMENTED ở chế độ sandbox/configurable.
- MoMo: IMPLEMENTED ở endpoint test/configurable.
- SePAY: IMPLEMENTED cho tra cứu/webhook; xác thực phụ thuộc API key/webhook secret.
- VietQR: IMPLEMENTED để tạo thông tin/QR chuyển khoản.
- WebSocket/broadcasting: NOT_FOUND. Chat dùng HTTP polling.
- Cloud object storage: NOT_FOUND trong implementation nghiệp vụ; cấu hình S3 mặc định của Laravel tồn tại nhưng upload hiện dùng public disk local.
- AI chatbot: NOT_FOUND. FAQ reply là rules/data nội bộ trong `ChatService`.

## 13. Inconsistencies / Missing Information

| Mức | Phát hiện | Bằng chứng/tác động |
|---|---|---|
| Cao | Toàn bộ nhóm `/api/admin/*` không gắn `auth` hoặc `role:ADMIN` | `routes/api.php`; `route:list` chỉ hiện middleware `api`. Các endpoint CRUD/toggle/delete catalog có thể được gọi không qua phân quyền route. |
| Cao | Route `/switch-role/{role}` công khai, tự tạo tài khoản demo và đăng nhập role được chọn | `routes/web.php`; phù hợp tiện ích dev nhưng nguy hiểm nếu triển khai production. |
| Cao | Secret/credential mẫu có giá trị mặc định trong source | `.env.example`, `config/services.php`, `PaymentSettingService.php`; cần coi là đã lộ nếu là credential thật và thay mới. |
| Trung bình | Checkout routes được khai báo hai lần trong cùng group customer | `routes/customer.php`; khai báo ngoài và bên trong `role:CUSTOMER`, route sau che/ghi đè route trước nên STAFF thực tế không checkout dù comment nói group auth chung. |
| Trung bình | Một số public webhook được loại CSRF theo wildcard rộng | `bootstrap/app.php`; an toàn phụ thuộc hoàn toàn vào signature/token trong controller. |
| Trung bình | Documentation README chủ yếu là Laravel boilerplate | `README.md`; không phản ánh đầy đủ chức năng/kiến trúc dự án. |
| Trung bình | `admin/customers`, `admin/staff`, `staff/order-status` chỉ trả placeholder | route files; phải ghi DESIGNED_ONLY, không xem là module quản lý hoàn chỉnh. |
| Thấp | `admin.vouchers.extend` và `restore` cùng gọi `VoucherController::restore` | `routes/admin.php`; tên use case/route không khớp rõ với implementation. |
| Thấp | `admin.dashboard` có hai URI và route `/admin/dashboard` có name `admin.` | `routes/admin.php`, output route list; có thể gây nhầm khi truy vết. |
| Thấp | Hai controller wishlist tồn tại | `Customer/WishlistController.php` và `Customer/WishlistKT/WishlistController.php`; các route chia trách nhiệm toggle/ids và trang CRUD, cần thể hiện đúng từng flow. |
| Thấp | `Repository` và DTO layer không tồn tại nhất quán | Controller/service gọi Eloquent trực tiếp; sơ đồ sequence/class không nên tự thêm hai layer này. |
| Cần xác minh runtime | Schema DB đang chạy có khớp toàn bộ migration hay không | Không có database dump trong repo; tài liệu lấy migration làm nguồn chuẩn. Cần `migrate:status` trên môi trường đích để xác nhận. |
| Cần xác minh runtime | External integration có credential và webhook production hoạt động hay không | Source chứng minh implementation, không chứng minh cấu hình/khả dụng môi trường. |
| Test failure | Test mặc định trang chủ chạy không tạo schema `categories` | `Tests/Feature/ExampleTest.php`; request `/` trả 500 với SQLite in-memory do thiếu bảng. |
| Test failure | Hai kiểm thử MoMo không đi qua gateway như kỳ vọng | `MomoPaymentGatewayTest`: service trả false và redirect rơi về `/customer/payment/qr/{order}` thay vì domain MoMo. |
| Test failure | Contract HTTP khi xóa category còn sản phẩm không thống nhất | `SoftDeleteTest` kỳ vọng 400, implementation trả 422. |

## 14. Recommended Diagram List

### UML

| ID | Loại | Tên sơ đồ | Phạm vi/thành phần | Lý do |
|---|---|---|---|---|
| UCD-01 | Use Case | Tổng quát hệ thống | Guest, CUSTOMER, STAFF, ADMIN và external actors | Cho thấy ranh giới hệ thống |
| UCD-02 | Use Case | Authentication & Account | đăng ký/OTP/login/Google/profile/password | Nhiều nhánh xác minh |
| UCD-03 | Use Case | Shopping & Checkout | catalog, wishlist, cart, voucher, shipping, order | Nghiệp vụ khách hàng chính |
| UCD-04 | Use Case | Order & Payment | customer/staff/admin, gateways | Quyền và trạng thái phức tạp |
| UCD-05 | Use Case | Review & Support | review, conversation, case lifecycle | Hai luồng hậu mãi |
| UCD-06 | Use Case | Administration | catalog, users, vouchers, reports, settings | Phạm vi admin lớn |
| ACT-01..09 | Activity | Các activity tại mục 5 | Giữ nguyên 9 workflow | Chứa decision/transaction đáng kể |
| SEQ-01..08 | Sequence | Các sequence tại mục 6 | Giữ nguyên participants thực tế | Truy vết đủ UI→code→DB/external |
| CLS-01 | Class | Domain model tổng thể | entity và cardinality | Mô tả cấu trúc domain |
| CLS-02 | Class | Order–Payment domain | OrderService, order/payment/refund classes | Miền phức tạp nhất |
| CLS-03 | Class | Support domain | ChatService, conversation/case/message/user | Nhiều quyền và state |

### Data Design

| ID | Loại | Tên | Phạm vi | Lý do |
|---|---|---|---|---|
| ERD-01 | ERD | ERD toàn hệ thống | các bảng nghiệp vụ mục 8 | Nguồn cho thiết kế dữ liệu |
| ERD-02 | ERD | Order–Payment–Voucher | order/details/history/payment/refund/voucher | Tách vùng dày quan hệ |
| ERD-03 | ERD | Catalog & Customer engagement | catalog/cart/wishlist/review | Dễ đọc hơn ERD tổng |
| DBS-01 | Relational schema | Physical relational schema | PK/FK/unique/index/nullable | Đối chiếu migration |

### System Design

| ID | Loại | Tên | Phạm vi | Lý do |
|---|---|---|---|---|
| ARC-01 | System Architecture | Kiến trúc tổng thể | Browser, Nginx/PHP-FPM, Laravel, DB, storage, external systems | Thể hiện deployment và tích hợp |
| ARC-02 | Backend–Frontend | Luồng request/data | Blade/JS→route→middleware→controller→service→Eloquent→DB | Phản ánh kiến trúc thực tế |
| ARC-03 | Deployment | Docker deployment | Nginx, app container, MySQL, volumes, scheduler requirement | Hữu ích cho vận hành |

## 15. Traceability Matrix

| Business Function | Actor | Use Case | Activity | Sequence | API/route | Controller/Service | Entity/Table |
|---|---|---|---|---|---|---|---|
| Browse catalog | Guest/customer | UC-01 | N/A | N/A | `/products`, `/api/products` | Product controllers | category/product/variant/image/review |
| Register | Guest | UC-02 | ACT-01 | SEQ-08 | `/register*` | registration controllers, OtpService | user/email_verification_code |
| Login | Guest | UC-03 | N/A | N/A | `/login`, `/auth/google*` | session/Google controllers | user/session |
| Profile/security | User | UC-04/05 | N/A | SEQ-08 một phần | `/profile*`, `/account/password` | profile/password controllers | user/email_change_code |
| Cart | CUSTOMER | UC-07 | ACT-02 một phần | SEQ-01 một phần | `/customer/cart*` | CartController | cart_item/product/variant |
| Wishlist | CUSTOMER | UC-06 | N/A | N/A | `/customer/wishlist*` | WishlistController/Service | wishlist_item/product |
| Voucher | CUSTOMER/ADMIN | UC-08/20 | ACT-02 | SEQ-01 | checkout, `/admin/vouchers*` | VoucherController/Model | voucher/pivots/order |
| Shipping | CUSTOMER/GHN | UC-09 | ACT-02 | SEQ-01 | calculate-shipping | CheckoutController/ShippingService | order shipping fields; external GHN |
| Checkout | CUSTOMER | UC-10 | ACT-02 | SEQ-01 | checkout/process | CheckoutController/OrderService | cart/order/detail/history/product/voucher |
| Online payment | CUSTOMER/gateway | UC-11 | ACT-03 | SEQ-02/03/04 | payment/webhook routes | PaymentController/services | payment/order/settings |
| Order lifecycle | CUSTOMER/STAFF/ADMIN | UC-12/15 | ACT-04/05 | SEQ-05 | order routes | OrderController/OrderService | order/detail/history/product/variant |
| Refund/reconcile | STAFF/ADMIN | UC-16/17 | ACT-06 | SEQ-04 một phần | staff/admin payments | PaymentController/services | payment/refund/order/settings |
| Review | CUSTOMER/ADMIN | UC-13/22 | ACT-07 | SEQ-07 | review routes | ReviewController/Service | review/order/product/user |
| Support chat | CUSTOMER/STAFF/ADMIN | UC-14/18 | ACT-08 | SEQ-06 | messages/support routes | ChatController/ChatService | conversation/case/message/user/order |
| Catalog admin | ADMIN | UC-19 | N/A | N/A | admin products, api/admin | Product/CategoryController | catalog tables |
| User admin | ADMIN | UC-21 | N/A | N/A | `/admin/users*` | UserController/UserService | user and dependent records |
| Reporting | ADMIN | UC-23 | N/A | N/A | admin dashboard/revenue | Dashboard/Report controllers | aggregate order/payment/user/product |
| Expire unpaid order | Scheduler | UC-24 | ACT-09 | N/A | Artisan command | CancelUnpaidOrdersCommand/OrderService | order/payment/stock/voucher/history |

### Kiểm tra chéo cuối

- Mọi use case `IMPLEMENTED` phía trên đều có route/controller hoặc scheduler command và model/table tương ứng; trường hợp không cần persistence được ghi N/A.
- Các trang placeholder được đánh dấu `DESIGNED_ONLY`, không đưa vào activity/sequence như chức năng hoàn chỉnh.
- Không thêm Repository, DTO, WebSocket, mobile app, AI service hay cloud storage vì source hiện tại không chứng minh các thành phần đó.
- Khi dựng sơ đồ từ tài liệu này, giữ nguyên các status: order `PENDING`, `CONFIRMED`, `PREPARING`, `SHIPPING`, `COMPLETED`, `RETURNED`, `CANCELLED`; payment `UNPAID`, `PENDING`, `PAID`, `FAILED`, `REFUNDED`; request `PENDING`, `APPROVED`, `REJECTED`; support case `WAITING`, `IN_PROGRESS`, `CLOSED`.
