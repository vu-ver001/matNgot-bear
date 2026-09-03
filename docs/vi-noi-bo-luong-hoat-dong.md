# Ví Nội Bộ Mật Ngọt Bear — Luồng Hoạt Động & Đặc Tả Nghiệp Vụ (v2.1)

> Tài liệu mô tả chi tiết cách **Ví nội bộ (Internal Wallet / Store Credit)** hoạt động trong hệ thống Mật Ngọt Bear: bản chất dòng tiền, cú pháp nạp, cơ chế duyệt an toàn (chống gian lận), kịch bản thanh toán/hoàn tiền và đặc tả kỹ thuật chi tiết.
>
> **Thay đổi so với v2 → v2.1 (sau review đối chiếu mã nguồn thực tế):**
> 1. Sửa lỗi sổ cái: hoàn tiền mặt (không qua ví) **không được ghi** vào `wallet_transactions` — chỉ cập nhật `payments`/`orders`, tránh phá vỡ tính toàn vẹn kế toán của ví.
> 2. Bổ sung nút **"Hủy yêu cầu nạp"** để khách chủ động hủy giao dịch `PENDING`, không phải chờ hết hạn 30 phút mới nạp lại được.
> 3. Job tự động hết hạn **chỉ áp dụng cho trạng thái `PENDING`**, tuyệt đối không tự động expire `WAITING_APPROVAL` (tránh khách mất tiền thật mà hệ thống báo hết hạn); bổ sung nút **[Kích hoạt lại & Duyệt nạp]** cho trường hợp chuyển khoản muộn.
> 4. Sửa tên middleware role đúng theo enum thực tế của hệ thống: `role:CUSTOMER` (viết hoa), không phải `role:customer`.
> 5. Cập nhật cách đăng ký route riêng cho môi trường local theo đúng kiến trúc **Laravel 11** (không còn `RouteServiceProvider`).
> 6. Bổ sung quy định ép kiểu tường minh khi so sánh `orders.total_amount` (kiểu `decimal`) với `wallet_balance` (kiểu `unsignedBigInteger`) trong `WalletService::payOrder`.

---

## 1. Ví nội bộ là gì?

**Ví nội bộ KHÔNG phải là tài khoản ngân hàng và KHÔNG chứa tiền mặt thật.**

Ví đóng vai trò là **sổ cái ghi nợ (Store Credit Ledger)** trong cơ sở dữ liệu của cửa hàng — cho biết cửa hàng đang giữ của khách hàng bao nhiêu tiền để phục vụ việc mua sắm sau này.

| Khái niệm | Bản chất kỹ thuật & Nghiệp vụ |
|-----------|--------------------------------|
| **Số dư ví** `wallet_balance` | Trường số nguyên trong bảng `users`, lưu theo **đơn vị xu/đồng nguyên** (ví dụ: `150000` VNĐ, không dùng số thực để tránh sai số làm tròn) |
| **Giao dịch ví** `wallet_transactions` | Lịch sử biến động: Nạp (`DEPOSIT`), Trừ thanh toán (`PAYMENT`), Hoàn trả (`REFUND`), có lưu số dư trước & sau giao dịch |
| **Tiền thật** | Nằm trong **tài khoản ngân hàng MB `0377466205`** hoặc **ví MoMo `0377466205`** của chủ cửa hàng |
| **Quyền rút tiền** | **Không hỗ trợ rút tiền mặt (No Cash-Out)** đối với số dư có được từ khuyến mãi/thưởng. Riêng hoàn tiền cho đơn hủy có ngoại lệ — xem Mục 3.5. |

> **Quy tắc pháp lý & nghiệp vụ:** Việc hạn chế rút tiền mặt là điểm mấu chốt để hệ thống tuân thủ mô hình **Ví đóng (Closed-loop Wallet)**, không vi phạm các quy định về trung gian thanh toán của Ngân hàng Nhà nước. Tuy nhiên, chính sách hoàn tiền khi hủy đơn (Mục 3.5) cần tuân thủ quy định bảo vệ người tiêu dùng về hoàn trả đúng phương thức thanh toán gốc.

---

## 2. Dòng tiền thật & Cú pháp nạp tiền

Tiền thật được chuyển trực tiếp vào tài khoản của shop thông qua các kênh có sẵn trong dự án:

```
Khách hàng  ── chuyển tiền THẬT (VietQR / MoMo / VNPAY) ──▶  Tài khoản chủ shop
 (App Bank)                                                    MB 0377466205
                                                               "NGUYỄN NGỌC ANH"
```

### 2.1. Cú pháp nạp tiền (Tránh nhầm lẫn dòng tiền)

Để shop biết giao dịch trong app ngân hàng là của khách nào nạp vào ví, mỗi lệnh nạp sẽ sinh một mã định danh duy nhất:

$$\text{Cú pháp chuyển khoản:} \quad \mathbf{NAP\ [USER\_ID]\ [M\tilde{A}\_GD]}$$

*(Ví dụ: `NAP 12 WTX98234` — Khách hàng ID 12 nạp tiền theo giao dịch WTX98234)*

- **VietQR Napas 24/7:** Hệ thống tự động tạo mã QR chứa số tài khoản MB, số tiền nạp và nội dung `NAP 12 WTX98234`. Khách chỉ cần quét, toàn bộ thông tin được điền chính xác 100%.
- **MoMo:** Chuyển đến link nhận tiền kèm lời nhắn là mã nạp tiền.
- **VNPAY Sandbox (Tự động):** Khách thanh toán qua cổng sandbox, VNPAY phản hồi callback thành công thì hệ thống tự động cộng tiền — có kiểm tra chống trùng lặp (xem Mục 2.3).

### 2.2. Thời hạn & vòng đời giao dịch nạp (cập nhật v2.1)

- Mỗi giao dịch nạp ở trạng thái `PENDING` (khách **chưa bấm** "Tôi đã chuyển tiền") có **thời hạn hiệu lực 30 phút**.
- **Job tự động hết hạn `wallet:expire-pending-deposits` (chạy mỗi 5 phút) CHỈ được phép quét và chuyển sang `EXPIRED` các bản ghi thỏa mãn đồng thời:**
  ```sql
  status = 'PENDING' AND expires_at < NOW()
  ```
  **Tuyệt đối không được đụng vào các bản ghi `WAITING_APPROVAL`.** Một khi khách đã bấm "Tôi đã chuyển tiền" (tức đã có khả năng chuyển tiền thật), giao dịch chỉ được kết thúc bởi hành động của Admin (**Duyệt** hoặc **Từ chối**), không bao giờ tự động hết hạn — tránh trường hợp khách đã mất tiền thật nhưng hệ thống báo giao dịch hết hạn trong khi Admin chưa kịp xử lý (ví dụ ngoài giờ hành chính).
- **Khách chủ động hủy yêu cầu nạp (mới):** Khi giao dịch còn ở trạng thái `PENDING` (chưa bấm xác nhận chuyển tiền), khách có nút **"Hủy yêu cầu này"** → chuyển ngay sang `CANCELLED`. Điều này giải phóng "slot" giao dịch mở, cho phép khách tạo lại lệnh nạp mới ngay lập tức với số tiền hoặc phương thức khác, thay vì phải chờ đủ 30 phút mới hết hạn.
- Khách chỉ được có tối đa **1 giao dịch `PENDING`/`WAITING_APPROVAL` đang mở cùng lúc**, tránh admin phải đối soát nhiều mã cùng lúc từ một khách.
- **Xử lý chuyển khoản muộn (Late Transfer, mới):** Nếu giao dịch đã bị `EXPIRED` (do khách không xác nhận trong 30 phút) nhưng sau đó tiền thật vẫn về tài khoản shop, Admin khi đối soát sao kê ngân hàng có nút **[Kích hoạt lại & Duyệt nạp]** trên giao dịch `EXPIRED` đó để cộng tiền cho khách thủ công, không bắt khách phải tạo giao dịch mới hay liên hệ hỗ trợ.
- Nếu số tiền chuyển khoản thực tế **không khớp** với số tiền yêu cầu trong giao dịch:
  - Sai lệch nhỏ hơn hoặc bằng ngưỡng dung sai (ví dụ 1.000đ do phí ngân hàng) → Admin có thể nhập và duyệt với **số tiền thực nhận (`actual_amount`)**. Hệ thống cập nhật lại `wallet_transactions.amount = actual_amount` và cộng đúng số tiền thực nhận này vào `wallet_balance` (`balance_after = balance_before + actual_amount`).
  - Sai lệch lớn hơn → Admin bắt buộc **Từ chối** và ghi rõ lý do; khách được hướng dẫn liên hệ hỗ trợ để xử lý số tiền dư/thiếu.

### 2.3. Chống Replay Callback VNPAY (mới)

- Khi nhận callback từ VNPAY, hệ thống kiểm tra chữ ký `vnp_SecureHash`.
- **Trước khi cộng tiền**, bắt buộc kiểm tra lại trạng thái hiện tại của giao dịch trong `DB::transaction()` có `lockForUpdate()`: nếu giao dịch đã ở trạng thái `PAID`, callback được ghi nhận nhưng **không cộng tiền lần 2** (trả về HTTP 200 để VNPAY ngừng gửi lại, nhưng log cảnh báo trùng lặp).
- Mỗi `transaction_code` chỉ được xử lý callback thành công đúng 1 lần — đảm bảo idempotency.

---

## 3. Cơ chế duyệt nạp tiền: Giải quyết lỗ hổng bảo mật

> [!CAUTION]
> **Lỗ hổng Free-Money Exploit:** Nếu hệ thống cho phép khách chỉ bấm *"TÔI ĐÃ HOÀN TẤT"* mà ví tự động nhảy số tiền, khách có thể nạp khống hàng chục triệu tiền ảo để mua sạch kho gấu bông.

Để giải quyết triệt để rủi ro trên, hệ thống áp dụng cơ chế phê duyệt 2 tầng:

### 3.1. Luồng nghiệp vụ chuẩn (Môi trường thực tế)

1. Khách tạo yêu cầu nạp → Giao dịch ở trạng thái **`PENDING`** (Chờ thanh toán & đối soát).
2. Khách quét QR chuyển khoản thật trên app ngân hàng → Bấm *"Tôi đã chuyển tiền"*. Trạng thái chuyển thành **`WAITING_APPROVAL`** (Chờ shop kiểm tra).
3. **Admin / Staff** vào mục Quản lý ví trên trang quản trị:
   - Kiểm tra thông báo biến động số dư tài khoản MB/MoMo đúng mã `NAP...` và đúng số tiền (trong ngưỡng dung sai — Mục 2.2).
   - Bấm **[Duyệt nạp]**: Hệ thống mở Transaction, **khóa lại bản ghi giao dịch (`lockForUpdate`) và kiểm tra trạng thái hiện tại phải đúng là `WAITING_APPROVAL`** trước khi cộng `wallet_balance` cho khách → Đổi trạng thái giao dịch sang **`PAID`**. Việc kiểm tra trạng thái trước khi ghi là bắt buộc để chống double-approve khi admin bấm nút nhiều lần hoặc có 2 admin cùng xử lý.
   - Nếu không nhận được tiền hoặc sai lệch: Bấm **[Từ chối]** → Chuyển thành **`REJECTED`** kèm lý do.

### 3.2. Chống double-approve (mới)

- Endpoint `approveDeposit` phải là **idempotent**: nếu giao dịch đã ở trạng thái `PAID`, gọi lại API sẽ trả về lỗi "Giao dịch đã được duyệt trước đó" mà không cộng tiền thêm.
- Sử dụng transaction DB với mức cô lập đảm bảo không có 2 request duyệt cùng một giao dịch song song thành công.

### 3.3. Hỗ trợ chấm điểm & Demo đồ án (Developer / Local Mode) — **guard 2 lớp**

- Khi chạy ở môi trường phát triển (`APP_ENV=local`), trang xác nhận nạp tiền của khách sẽ cung cấp thêm một nút phụ: **`[Demo: Giả lập Admin duyệt ngay]`** để phục vụ việc demo luồng mua sắm diễn ra tức thì mà không cần chuyển sang tab Admin.
- **Bắt buộc kiểm tra `APP_ENV` ở cả 2 lớp, không chỉ ẩn/hiện nút ở giao diện:**
  1. **Lớp giao diện:** Blade `@env('local')` để ẩn nút ở môi trường khác.
  2. **Lớp backend (bắt buộc, không được bỏ qua):** Controller xử lý hành động demo-approve phải tự kiểm tra ngay đầu hàm xử lý bằng `abort_unless(app()->environment('local'), 403);`; nếu không phải môi trường local, trả về lỗi 403 ngay lập tức — **bất kể request có được gửi trực tiếp tới endpoint hay không**.
  3. **Đăng ký route (Laravel 11):** Dự án chạy Laravel 11, vốn **không còn `RouteServiceProvider`**. Route demo-approve được khai báo có điều kiện ngay trong `routes/customer.php` (hoặc `routes/web.php`), bọc trong `if (app()->environment('local'))`, để route này **không tồn tại trên production kể cả về mặt định tuyến**:
     ```php
     if (app()->environment('local')) {
         Route::post('/wallet/demo-approve/{transaction}', [WalletController::class, 'demoApprove'])
             ->middleware(['auth', 'role:CUSTOMER'])
             ->name('customer.wallet.demo_approve');
     }
     ```
     Guard ở tầng controller (`abort_unless`) vẫn được giữ song song như một lớp phòng thủ độc lập, đề phòng route bị đăng ký nhầm.

### 3.4. VNPAY Sandbox

- Khách hàng có thể chọn nạp qua **Cổng VNPAY Sandbox**: Cổng thanh toán tự động kiểm tra chữ ký `vnp_SecureHash` và cộng tiền vào ví tự động trong vài giây, với cơ chế chống trùng lặp callback (Mục 2.3).

### 3.5. Chính sách hoàn tiền khi hủy đơn (làm rõ, mới)

Trước đây tài liệu quy định "mọi hoàn tiền đều đưa vào ví", nhưng điều này mâu thuẫn với chính sách người tiêu dùng nếu khách đã thanh toán bằng **tiền thật** (VNPAY/chuyển khoản trực tiếp cho đơn hàng, không qua ví). Quy tắc được làm rõ như sau:

| Phương thức thanh toán gốc của đơn hàng | Khi hủy/hoàn, tiền đi về đâu |
|---|---|
| **WALLET** (đã trừ từ ví) | Hoàn lại vào **ví** → **CÓ** ghi vào `wallet_transactions` (`type = REFUND`), vì số dư ví thực sự thay đổi. |
| **VNPAY / chuyển khoản trực tiếp cho đơn** (không qua ví), khách chọn **hoàn tiền thật** | Admin chuyển khoản trả khách qua đúng kênh gốc trong vòng 7 ngày. Chỉ cập nhật `payments.status = REFUNDED` và `orders.payment_status = REFUNDED`. **KHÔNG ghi** vào `wallet_transactions` vì `wallet_balance` không đổi. |
| **VNPAY / chuyển khoản trực tiếp cho đơn**, khách **đồng ý nhận vào ví** | Lúc này ví thực sự được cộng tiền → **CÓ** ghi vào `wallet_transactions` (`type = REFUND`, `amount = total`, `balance_after = balance_before + amount`). |
| **COD** | Không phát sinh hoàn tiền (đơn chưa thanh toán) |

> **Nguyên tắc bất biến của sổ cái:** `wallet_transactions` chỉ được ghi nhận khi và chỉ khi hành động đó **thực sự làm thay đổi `wallet_balance`**. Ghi một dòng `REFUND` vào bảng này mà không cộng tiền vào ví (trường hợp hoàn tiền mặt qua ngân hàng) sẽ phá vỡ đẳng thức đối soát:
>
> $$\sum \text{DEPOSIT} - \sum \text{PAYMENT} + \sum \text{REFUND} = \text{wallet\_balance}$$
>
> Giao diện hủy đơn phải hiển thị rõ lựa chọn "Hoàn vào ví" hoặc "Hoàn về tài khoản gốc" cho các đơn thanh toán bằng tiền thật, và luồng backend phải rẽ nhánh đúng theo bảng trên.

---

## 4. Luồng hoạt động tổng thể

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ 1. NẠP TIỀN VÀO VÍ                                                                     │
│ Khách chọn số tiền + Phương thức (VietQR / MoMo / VNPAY)                               │
│ ──▶ Hệ thống tạo giao dịch PENDING (kèm mã NAP..., hết hạn sau 30 phút) & sinh QR       │
│ ──▶ Khách chuyển khoản thật qua app ngân hàng                                          │
│ ──▶ Khách bấm "Tôi đã hoàn tất chuyển tiền" (Chuyển sang WAITING_APPROVAL)             │
│ ──▶ Admin kiểm tra tài khoản shop & bấm [DUYỆT] (khóa bản ghi, chống double-approve)    │
│      (Hoặc VNPAY Callback tự động, có chống replay)                                    │
│ ──▶ wallet_balance += amount (Trạng thái giao dịch: PAID)                              │
└──────────────────────────────────────────┬─────────────────────────────────────────────┘
                                           │
                                           ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ 2. MUA HÀNG BẰNG VÍ (CHECKOUT)                                                         │
│ Khách chọn hình thức "Thanh toán bằng ví nội bộ"                                        │
│ ──▶ Kiểm tra số dư giao diện: wallet_balance >= total_amount ?                          │
│        ├── Không đủ: Thông báo số dư thiếu & gợi ý nạp thêm                            │
│        └── Đủ số dư: Cho phép bấm [Đặt hàng]                                          │
│ ──▶ Backend khóa số dư (lockForUpdate trong DB::transaction):                           │
│        ├── Kiểm tra lại số dư lần cuối chống Race Condition                            │
│        ├── wallet_balance -= total_amount  (ràng buộc CHECK >= 0 ở DB)                  │
│        ├── Ghi lịch sử ví type = PAYMENT, status = PAID                                │
│        └── Tạo đơn hàng với payment_method = WALLET, payment_status = PAID             │
│ ──▶ Đơn hàng được xác nhận ngay (CONFIRMED), chuyển cho kho đóng gói                   │
└──────────────────────────────────────────┬─────────────────────────────────────────────┘
                                           │
                                           ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ 3. HỦY ĐƠN / HOÀN TRẢ                                                                  │
│ ──▶ Hủy khi đơn chưa gửi (PENDING, CONFIRMED, PREPARING):                               │
│        Xác định payment_method gốc (Mục 3.5) → Hoàn vào ví HOẶC hoàn tiền thật          │
│ ──▶ Đơn đã gửi (SHIPPING) hoặc trả hàng (RETURNED):                                    │
│        Chỉ hoàn tiền sau khi Admin/Staff xác nhận hàng đã hoàn về kho an toàn           │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 5. Kịch bản thực tế — Ví dụ mua gấu bông 150.000đ

### Giai đoạn 1: Nạp 100.000đ vào ví

1. Khách vào trang cá nhân → Chọn **"Ví của tôi"** → Nhập số tiền **100.000đ** → Chọn **VietQR**.
2. Hệ thống sinh giao dịch mã `WTX101`, hiển thị VietQR với nội dung chuyển khoản: `NAP 12 WTX101`, hiệu lực 30 phút.
3. Khách mở app ngân hàng, quét QR, chuyển **100.000đ** thật vào tài khoản MB `0377466205`.
4. Khách bấm nút *"TÔI ĐÃ HOÀN TẤT CHUYỂN TIỀN"*.
5. Admin kiểm tra app MB thấy biến động +100.000đ với nội dung `NAP 12 WTX101`, số tiền khớp → Bấm **[Duyệt nạp]**.
6. Ví khách được cộng 100.000đ (`balance_before = 0`, `balance_after = 100000`).

### Giai đoạn 2: Đặt hàng gấu bông 150.000đ

1. Khách thêm gấu bông 150.000đ vào giỏ → Vào **Checkout**.
2. Khách thấy tùy chọn: *"Ví nội bộ (Số dư khả dụng: 100.000đ)"*.
3. Hệ thống hiển thị cảnh báo: *"Số dư ví không đủ (thiếu 50.000đ). Vui lòng nạp thêm hoặc chọn hình thức khác."*
4. Khách vào ví nạp thêm **50.000đ** (lặp lại quy trình Giai đoạn 1, tạo giao dịch mới độc lập vì giao dịch trước đã ở trạng thái `PAID`).
5. Số dư ví cập nhật thành **150.000đ**.
6. Khách quay lại Checkout → Chọn *"Thanh toán bằng ví nội bộ"* → Bấm **[Đặt hàng ngay]**.
7. Hệ thống trừ ví 150.000đ (có `lockForUpdate` chống race condition) → Số dư về **0đ**.
8. Đơn hàng chuyển ngay sang trạng thái: `order_status = CONFIRMED`, `payment_status = PAID`, `payment_method = WALLET`.

### Giai đoạn 3: Hủy đơn hàng và hoàn tiền

1. Khi đơn hàng đang ở trạng thái `PREPARING` (Đang chuẩn bị hàng), khách bấm **"Hủy đơn"** và nhập lý do: *"Muốn đổi sang mẫu gấu lớn hơn"*.
2. Vì đơn chưa giao cho bưu tá, hệ thống kiểm tra `payment_method` gốc của đơn là `WALLET` → Hoàn tiền tự động vào ví (đúng nguồn gốc, không cần khách chọn):
   - Cộng lại **150.000đ** vào ví của khách (`balance_before = 0`, `balance_after = 150000`).
   - Ghi nhận 1 giao dịch ví mới: `type = REFUND`, `status = PAID`, `payment_method = WALLET`.
   - Đơn hàng chuyển sang `CANCELLED`, `payment_status = REFUNDED`.
   - Hoàn trả số lượng tồn kho sản phẩm.
3. Khách có thể lập tức dùng 150.000đ trong ví để đặt một sản phẩm gấu bông khác.

*(Nếu đơn này được thanh toán bằng VNPAY thay vì ví, hệ thống sẽ hỏi khách chọn hoàn vào ví hay hoàn về tài khoản gốc theo Mục 3.5.)*

---

## 6. Sơ đồ kiến trúc dòng tiền & luồng dữ liệu

```
                  ┌────────────────────────┐
                  │       Khách hàng       │
                  └───────────┬────────────┘
                              │
              ┌───────────────┼───────────────┐
              │ Nạp tiền thật │               │ Mua hàng bằng ví
              ▼               ▼               ▼
        ┌──────────┐    ┌──────────┐    ┌──────────┐
        │  VietQR  │    │   MoMo   │    │  VNPAY   │
        │ MB Bank  │    │ Cá nhân  │    │ Sandbox  │
        └─────┬────┘    └────┬─────┘    └────┬─────┘
              │              │               │ Callback (chống replay)
              └──────┬───────┘               ▼
                     │ Tiền thật        [Tự động duyệt]
                     ▼                       │
        ┌─────────────────────────┐          │
        │   Tài khoản chủ shop    │          │
        │ MB Bank / MoMo cá nhân  │          │
        └────────────┬────────────┘          │
                     │ Đối soát              │
                     ▼                       │
        ┌─────────────────────────┐          │
        │ Admin/Staff duyệt       │          │
        │ (lockForUpdate + check  │          │
        │  trạng thái hiện tại)   │          │
        └────────────┬────────────┘          │
                     ├───────────────────────┘
                     ▼
        ┌─────────────────────────┐
        │   Cơ sở dữ liệu (DB)    │
        │  users.wallet_balance   │ ◀── Số dư hiện tại (CHECK >= 0)
        │   wallet_transactions   │ ◀── Lịch sử đối soát chi tiết
        └────────────┬────────────┘
                     │
         ┌───────────┼───────────┐
         ▼           ▼           ▼
      DEPOSIT     PAYMENT     REFUND
     (Nạp tiền)  (Trừ mua)   (Hoàn tiền)
```

---

## 7. Thiết kế Cơ sở dữ liệu (Database Schema)

### 7.1. Cập nhật bảng `users`

Bổ sung cột lưu số dư ví — dùng kiểu **unsignedBigInteger** (đơn vị đồng nguyên, không thập phân) thay vì `decimal`, tránh sai số float khi tính toán ở tầng ứng dụng:

```php
$table->unsignedBigInteger('wallet_balance')->default(0)->after('status');
```

Bổ sung ràng buộc CHECK ở tầng DB (phòng thủ lớp 2, độc lập với logic ứng dụng):

```php
// Với MySQL 8.0.16+ / PostgreSQL, dùng raw statement sau khi tạo cột:
DB::statement('ALTER TABLE users ADD CONSTRAINT chk_wallet_balance_non_negative CHECK (wallet_balance >= 0)');
```

### 7.2. Tạo mới bảng `wallet_transactions`

Bảng lưu vết toàn bộ lịch sử biến động số dư, phục vụ đối soát và kiểm toán tài chính:

```php
Schema::create('wallet_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('transaction_code', 30)->unique(); // WTX20260903XXXX
    $table->enum('type', ['DEPOSIT', 'PAYMENT', 'REFUND']);
    $table->unsignedBigInteger('amount');
    $table->unsignedBigInteger('balance_before');
    $table->unsignedBigInteger('balance_after');
    // CANCELLED: khách tự hủy khi còn PENDING | EXPIRED: job tự động, chỉ áp dụng cho PENDING quá hạn
    $table->enum('status', ['PENDING', 'WAITING_APPROVAL', 'PAID', 'REJECTED', 'CANCELLED', 'EXPIRED'])->default('PENDING');
    $table->string('payment_method', 30)->nullable(); // VIETQR, MOMO, VNPAY, WALLET
    $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
    $table->string('transfer_content', 100)->nullable(); // NAP 12 WTX...
    $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete(); // Admin duyệt
    $table->dateTime('confirmed_at')->nullable();
    $table->dateTime('expires_at')->nullable(); // Thời hạn hiệu lực giao dịch PENDING
    $table->string('note', 255)->nullable();
    $table->timestamps();

    $table->index(['user_id', 'status']);
    $table->index(['status', 'expires_at']); // phục vụ job tự động expire
});
```

### 7.3. Cập nhật Enum phương thức thanh toán

Cập nhật `orders.payment_method` và `payments.method` để hỗ trợ giá trị:
- Thêm `'WALLET'` vào enum phương thức thanh toán.

---

## 8. Các quy tắc nghiệp vụ quan trọng (Business Rules)

1. **Phân quyền sử dụng ví (enforce ở middleware, không chỉ ở tài liệu):**
   - Cột `users.role` là `enum(['CUSTOMER', 'STAFF', 'ADMIN'])`, và `CheckRole` middleware so sánh bằng `in_array()` — **phân biệt hoa/thường**. Do đó middleware phải khai báo đúng `role:CUSTOMER` (viết hoa), khớp với giá trị enum thực tế; khai báo `role:customer` (chữ thường) sẽ khiến mọi khách hàng bị lỗi 403 Forbidden.
   - Tài khoản `ADMIN` và `STAFF` đóng vai trò người kiểm soát, không mua sắm bằng ví — `WalletService::payOrder` phải tự kiểm tra role của user trước khi xử lý, không chỉ dựa vào việc ẩn UI.
2. **Nguyên tắc thanh toán 100% (No Split Payment):**
   - Đơn hàng thanh toán bằng ví phải có `wallet_balance >= total_amount`.
   - Không cho phép thanh toán một phần bằng ví + một phần COD/Chuyển khoản nhằm tránh rủi ro khi hoàn đơn và phức tạp hóa sổ sách kế toán.
3. **Giới hạn số tiền nạp:**
   - Số tiền nạp tối thiểu mỗi lần: **10.000đ**.
   - Số tiền nạp tối đa mỗi lần: **5.000.000đ**.
   - Chỉ được có tối đa 1 giao dịch nạp đang mở (`PENDING`/`WAITING_APPROVAL`) tại một thời điểm cho mỗi khách.
4. **Bảo toàn giao dịch & Chống Race Condition:**
   - Mọi thao tác nạp tiền, trừ tiền thanh toán hoặc hoàn tiền bắt buộc phải đặt trong `DB::transaction()`.
   - Bắt buộc gọi `User::where('id', $userId)->lockForUpdate()->first()` trước khi kiểm tra số dư và trừ tiền, ngăn chặn triệt để lỗi khi người dùng mở nhiều tab checkout cùng lúc.
   - Bắt buộc `lockForUpdate()` trên bản ghi `wallet_transactions` và kiểm tra lại trạng thái hiện tại trước khi admin duyệt/từ chối, chống double-approve.
5. **Điều kiện hoàn tiền:**
   - Tự động hoàn tiền nếu đơn hàng bị hủy khi chưa bàn giao cho đơn vị vận chuyển (`PENDING`, `CONFIRMED`, `PREPARING`); hoàn vào ví hoặc về nguồn gốc theo Mục 3.5.
   - Nếu đơn hàng đã giao (`SHIPPING`) hoặc giao thành công (`COMPLETED`) sau đó phát sinh đổi trả (`RETURNED`), chỉ thực hiện hoàn tiền sau khi nhân viên kiểm kho xác nhận đã nhận lại hàng.
6. **Môi trường Demo:**
   - Chức năng "Demo duyệt ngay" chỉ tồn tại khi `APP_ENV=local`, kiểm tra bắt buộc ở cả route registration (Laravel 11, không dùng `RouteServiceProvider`) và tầng controller (Mục 3.3). Tuyệt đối không được deploy tính năng này lên môi trường production dưới bất kỳ hình thức nào.
7. **Ép kiểu tường minh khi so sánh tiền (mới):**
   - `orders.total_amount` hiện tại là kiểu `decimal(12,2)` — khi Eloquent lấy ra, PHP trả về dạng chuỗi (ví dụ `"150000.00"`). Trong khi đó `wallet_balance` và `wallet_transactions.amount` dùng `unsignedBigInteger`.
   - `WalletService::payOrder` **bắt buộc ép kiểu tường minh** trước khi so sánh/trừ số dư, không được so sánh trực tiếp giá trị chuỗi với số nguyên:
     ```php
     $orderAmount = (int) round($order->total_amount);
     if ($user->wallet_balance < $orderAmount) {
         throw new \Exception("Số dư không đủ");
     }
     $user->decrement('wallet_balance', $orderAmount);
     ```
   - Áp dụng nguyên tắc ép kiểu tương tự cho `refundOrder` khi cộng tiền hoàn về ví.

---

## 9. Kế hoạch triển khai kỹ thuật (Implementation Roadmap)

### Backend

1. **Migrations:**
   - Thêm cột `wallet_balance` (kiểu số nguyên) vào bảng `users`, kèm CHECK constraint.
   - Tạo bảng `wallet_transactions` với các trạng thái mở rộng (`EXPIRED`) và cột `expires_at`.
   - Thêm giá trị `'WALLET'` vào enum `payment_method` của bảng `orders` và `payments`.
2. **Dịch vụ cốt lõi `WalletService`:**
   - `createDepositRequest(User $user, int $amount, string $method): WalletTransaction` — kiểm tra không có giao dịch mở khác, set `expires_at`.
   - `cancelDepositRequest(WalletTransaction $tx, User $user): void` — chỉ cho phép chính chủ (`$tx->user_id === $user->id`) khi `status = PENDING`, chuyển sang `CANCELLED`, giải phóng slot giao dịch mở.
   - `confirmCustomerTransfer(WalletTransaction $tx): void`
   - `approveDeposit(WalletTransaction $tx, int $adminId, ?int $actualAmount = null): void` — idempotent, dùng `lockForUpdate`; cập nhật `$actualAmount` nếu có dung sai lệch tiền; dùng chung cho cả duyệt bình thường lẫn duyệt "chuyển khoản muộn" từ trạng thái `EXPIRED`.
   - `rejectDeposit(WalletTransaction $tx, int $adminId, string $reason): void`
   - `payOrder(Order $order, User $customer): WalletTransaction` — kiểm tra role `CUSTOMER`, `lockForUpdate` trên user, ép kiểu `(int) round($order->total_amount)` trước khi so sánh/trừ.
   - `refundToWallet(Order $order, ?string $reason): WalletTransaction` — chuyên trách hoàn tiền vào ví (ghi `wallet_transactions`), được gọi khi đơn gốc là `WALLET` hoặc khi khách chọn nhận hoàn tiền vào ví. (Nếu khách chọn hoàn tiền mặt qua ngân hàng, `OrderService::refundPayment` tự xử lý trên bảng `payments`, không gọi `WalletService`).
   - `handleVnpayCallback(array $payload): void` — kiểm tra chữ ký + idempotency chống replay.
3. **Job định kỳ:**
   - `ExpirePendingDeposits` — chạy mỗi 5 phút, **chỉ quét `status = 'PENDING' AND expires_at < now()`**, chuyển sang `EXPIRED`. Không bao giờ đụng đến `WAITING_APPROVAL`.
4. **Bộ điều khiển (Controllers):**
   - Phía Customer: `Customer/WalletController` (Xem số dư, lịch sử, nạp tiền, xem mã QR nạp, bấm xác nhận nạp, bấm hủy yêu cầu nạp).
   - Phía Checkout: Cập nhật `Customer/CheckoutController` bổ sung phương thức `WALLET`, gọi `WalletService::payOrder`.
   - Phía Order Service: Cập nhật `OrderService::updateStatus` khi đơn `CANCELLED`/`RETURNED`: nếu hoàn vào ví thì gọi `WalletService::refundToWallet`, nếu hoàn tiền thật thì cập nhật `payments.status = REFUNDED`.
   - Phía Quản trị: `Admin/WalletTransactionController` (Danh sách giao dịch, duyệt/từ chối nạp tiền, kích hoạt lại & duyệt nạp đơn chuyển muộn).
   - Route demo-approve: đăng ký có điều kiện `if (app()->environment('local'))` trực tiếp trong `routes/customer.php` (chuẩn Laravel 11), bọc middleware `['auth', 'role:CUSTOMER']` kèm guard `abort_unless` ở controller.

### Giao diện (Frontend & Views)

1. **Trang ví khách hàng:**
   - `customer/wallet/index.blade.php`: Thẻ hiển thị số dư, form nạp tiền, bảng lịch sử nạp/trừ/hoàn có bộ lọc trạng thái.
   - `customer/wallet/deposit-qr.blade.php`: Giao diện quét mã QR nạp tiền (hiển thị VietQR/MoMo, số tiền, cú pháp nạp, đồng hồ đếm ngược 30 phút, nút *"Tôi đã hoàn tất chuyển tiền"*, nút *"Hủy yêu cầu này"* khi còn `PENDING`, và nút *"Demo duyệt ngay"* chỉ khi `@env('local')`).
2. **Tích hợp Checkout:**
   - `customer/checkout/index.blade.php`: Thêm tùy chọn *"Ví nội bộ"*, hiển thị số dư khả dụng, tự động vô hiệu hóa và thông báo số tiền cần nạp bổ sung nếu số dư không đủ.
3. **Trang hủy đơn (mới):**
   - `customer/orders/cancel.blade.php`: Với đơn thanh toán bằng tiền thật (không qua ví), hiển thị lựa chọn "Hoàn vào ví" / "Hoàn về tài khoản gốc".
4. **Trang quản trị Admin:**
   - `admin/wallets/index.blade.php`: Bảng kiểm soát giao dịch nạp tiền, nút [Duyệt] và [Từ chối] kèm popup xác nhận, hiển thị cảnh báo nếu số tiền chuyển khoản lệch so với yêu cầu; với giao dịch `EXPIRED`, hiển thị thêm nút **[Kích hoạt lại & Duyệt nạp]** cho trường hợp chuyển khoản muộn.

---

*Tài liệu v2.1 — đã bổ sung các biện pháp chống double-approve, chống replay callback, làm rõ chính sách hoàn tiền theo phương thức gốc (bảo toàn tính toàn vẹn sổ cái ví), xử lý tiền bằng số nguyên, ràng buộc CHECK ở DB, guard 2 lớp cho tính năng demo theo đúng kiến trúc Laravel 11, chức năng hủy yêu cầu nạp, xử lý chuyển khoản muộn, và đồng bộ tuyệt đối với mã nguồn thực tế (role enum, kiểu dữ liệu `orders.total_amount`).*