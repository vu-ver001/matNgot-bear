# Deploy Mật Ngọt Bear lên Internet

Kiến trúc production dùng VPS Ubuntu, Docker Compose, MySQL nội bộ, Nginx phục vụ Laravel và Caddy làm reverse proxy tự cấp HTTPS. Chỉ cổng 80/443 của Caddy được public; MySQL và PHP-FPM không mở ra Internet.

## 1. Chuẩn bị

Cần có:

- Một VPS Ubuntu 24.04 hoặc 22.04, tối thiểu 2 vCPU, 2 GB RAM và 25 GB SSD.
- Một domain hoặc subdomain, ví dụ `shop.example.com`.
- Bản ghi DNS `A` của domain trỏ tới IPv4 public của VPS. Nếu dùng IPv6, thêm bản ghi `AAAA` đúng địa chỉ.
- Cổng TCP 22, 80, 443 và UDP 443 được firewall/cloud firewall cho phép.

Không đặt Cloudflare ở chế độ proxy trong lần cấp chứng chỉ đầu nếu chưa quen cấu hình. Có thể bật lại sau khi HTTPS hoạt động.

## 2. Cài Docker trên VPS

SSH vào VPS bằng tài khoản có quyền `sudo`, sau đó cài Docker từ repository chính thức của Docker. Kiểm tra:

```bash
docker version
docker compose version
```

Cho phép firewall:

```bash
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 443/udp
sudo ufw enable
sudo ufw status
```

## 3. Đưa source lên VPS

Ví dụ clone bằng Git:

```bash
sudo mkdir -p /opt/matngotbear
sudo chown "$USER":"$USER" /opt/matngotbear
git clone <GIT_REPOSITORY_URL> /opt/matngotbear
cd /opt/matngotbear
```

Không đưa `.env.production`, database dump, file upload hoặc secret vào Git.

## 4. Tạo môi trường production

```bash
cp .env.production.example .env.production
nano .env.production
```

Sửa tối thiểu:

```dotenv
APP_DOMAIN=shop.example.com
APP_URL=https://${APP_DOMAIN}
TLS_EMAIL=admin@example.com
DB_PASSWORD=<random-password>
DB_ROOT_PASSWORD=<different-random-password>
```

Có thể tạo password ngẫu nhiên bằng:

```bash
openssl rand -base64 36
```

Điền SMTP và credential của Google/GHN/VNPAY/MoMo/SePAY nếu sử dụng. URL callback phải dùng chính `APP_URL` production.

## 5. Build và tạo APP_KEY

```bash
docker compose --env-file .env.production -f compose.production.yaml build --pull
docker compose --env-file .env.production -f compose.production.yaml up -d db
docker compose --env-file .env.production -f compose.production.yaml run --rm app php artisan key:generate --show
```

Chép nguyên giá trị `base64:...` vào `APP_KEY` trong `.env.production`. Không đổi khóa này sau khi đã có dữ liệu mã hóa/session.

## 6. Tạo hoặc nhập database

Với website mới:

```bash
docker compose --env-file .env.production -f compose.production.yaml run --rm app php artisan migrate --force
```

Chỉ chạy seeder nếu chấp nhận dữ liệu mẫu và tài khoản mẫu:

```bash
docker compose --env-file .env.production -f compose.production.yaml run --rm app php artisan db:seed --force
```

Nếu đã có database thật, import bản dump trước rồi mới chạy `migrate --force`. Không dùng `migrate:fresh` trên production.

## 7. Khởi động và kiểm tra HTTPS

```bash
docker compose --env-file .env.production -f compose.production.yaml up -d
docker compose --env-file .env.production -f compose.production.yaml ps
docker compose --env-file .env.production -f compose.production.yaml logs --tail=100 caddy web app
```

Khi DNS đã trỏ đúng và cổng 80/443 mở, Caddy tự xin chứng chỉ. Mở:

```text
https://shop.example.com
```

Kiểm tra từ VPS:

```bash
curl -I https://shop.example.com/up
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan about
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan migrate:status
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan schedule:list
```

## 8. Đăng ký callback với dịch vụ ngoài

Các URL cần cấu hình ở dashboard nhà cung cấp:

```text
Google: https://shop.example.com/auth/google/callback
VNPAY return: https://shop.example.com/payment/vnpay/return
VNPAY IPN: https://shop.example.com/payment/vnpay/ipn
MoMo return: https://shop.example.com/payment/momo/return
MoMo IPN: https://shop.example.com/payment/momo/ipn
SePAY webhook: https://shop.example.com/webhook/sepay
```

Xác minh chữ ký/token webhook bằng request thực tế trong sandbox trước khi nhận thanh toán thật.

## 9. Cập nhật

Luôn backup trước migration:

```bash
cd /opt/matngotbear
git pull --ff-only
docker compose --env-file .env.production -f compose.production.yaml build --pull
docker compose --env-file .env.production -f compose.production.yaml run --rm app php artisan migrate --force
docker compose --env-file .env.production -f compose.production.yaml up -d --remove-orphans
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan optimize
```

Nếu dùng GitHub Actions/CD, vẫn giữ `.env.production` duy nhất trên VPS hoặc trong secret manager, không ghi vào workflow log.

## 10. Backup

```bash
mkdir -p /opt/matngotbear-backups
db_container=$(docker compose --env-file .env.production -f compose.production.yaml ps -q db)
docker exec "$db_container" sh -c 'mysqldump --single-transaction --routines --triggers -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" > /tmp/db.sql'
docker cp "$db_container:/tmp/db.sql" /opt/matngotbear-backups/db.sql
docker exec "$db_container" rm -f /tmp/db.sql
```

Ngoài database, phải backup volume `matngotbear_prod_storage_data` vì chứa avatar, ảnh review/chat và chứng từ. Sao lưu `.env.production` cùng `APP_KEY` trong kho bí mật riêng.

## 11. Checklist trước khi nhận người dùng thật

- `APP_ENV=production`, `APP_DEBUG=false`, cookie HTTPS bật.
- `/switch-role/*` trả 404 trên production.
- `/api/admin/*` yêu cầu đăng nhập ADMIN.
- Không còn credential mặc định hoặc credential từng commit vào Git; rotate các key có nguy cơ đã lộ.
- Tạo tài khoản ADMIN bằng quy trình kiểm soát, không dùng mật khẩu seed/demo.
- Email OTP gửi thật và SPF/DKIM/DMARC được cấu hình.
- Test checkout, cạnh tranh tồn kho, voucher, upload và restart container.
- Test VNPAY/MoMo/SePAY sandbox, signature sai, callback lặp và amount sai.
- Có backup tự động, lưu ngoài VPS và đã thử restore.
- Bật giám sát dung lượng đĩa, uptime và log lỗi.
