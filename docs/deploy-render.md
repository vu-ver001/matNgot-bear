# Deploy bản demo lên Render

Repo đã có `render.yaml` để tạo một web service Docker và một PostgreSQL Free
phục vụ bản demo. Render sẽ tự deploy lại mỗi khi có commit mới trên `main`.

## Trước khi tạo service

1. Thu hồi và tạo lại mọi credential từng xuất hiện trong `.env`, `.env.example`
   hoặc lịch sử Git. Không đưa credential mới vào repository.
2. Đăng nhập Render bằng GitHub và cấp quyền đọc repository
   `vu-ver001/matNgot-bear`.

## Tạo hạ tầng

1. Trong Render chọn **New → Blueprint**.
2. Chọn repository và branch `main`.
3. Render đọc `render.yaml`; chọn gói **Free** cho web và PostgreSQL rồi
   xác nhận.
4. Khi Render hỏi biến `APP_URL`, nhập URL `https://...onrender.com` của web
   service sau khi Render cấp URL. Nếu giao diện chưa biết URL, lưu service,
   xem URL rồi cập nhật lại biến này và deploy lại.
5. Để các biến tích hợp thanh toán trống nếu chỉ trình diễn luồng COD/thanh
   toán thủ công. Không cần cấu hình Google OAuth, MoMo, VNPay, GHN hoặc SePAY
   cho bản demo cơ bản.

Container sẽ chạy migration khi khởi động. Hook `initialDeployHook` chỉ chạy
seeder demo ở lần deploy đầu tiên.

## Tài khoản demo

Seeder tạo các tài khoản mẫu với mật khẩu `password`, gồm:

- `admin@matngotbear.com`
- `staff1@matngotbear.com`
- `customer@matngotbear.test`

Đổi hoặc xóa các tài khoản này nếu đưa URL cho người ngoài sau buổi thi.

## Giới hạn gói Free

- Web service ngủ sau 15 phút không có truy cập và cần khoảng một phút để thức
  lại.
- File upload và SQLite local không bền qua restart/redeploy; ảnh mẫu nằm
  trong source vẫn được build vào image.
- PostgreSQL Free của Render hết hạn sau 30 ngày, phù hợp cho demo ngắn hạn,
  không phù hợp làm dữ liệu thật lâu dài.

Tham khảo [Render free instances](https://render.com/docs/free) và
[hướng dẫn Laravel Docker](https://render.com/docs/deploy-php-laravel-docker).
