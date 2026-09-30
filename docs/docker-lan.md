# Chạy Mật Ngọt Bear bằng Docker trong mạng Wi-Fi

Tài liệu này dành cho một máy Windows làm máy chủ. Những máy khác chỉ cần trình duyệt và kết nối cùng mạng Wi-Fi.

## Trình bày nhanh qua Internet

Nếu chỉ cần public website trong lúc trình bày, không cần VPS hoặc domain. Chạy:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\present-online.ps1
```

Sau khi các service sẵn sàng, Cloudflare in ra URL tạm dạng `https://...trycloudflare.com`. Giữ cửa sổ PowerShell mở trong suốt buổi trình bày. Kết thúc bằng `Ctrl+C`, sau đó tắt toàn bộ dịch vụ:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\stop-presentation.ps1
```

URL thay đổi sau mỗi lần chạy. Quick Tunnel chỉ dành cho demo; Google OAuth và callback/IPN thanh toán không phù hợp vì các nhà cung cấp cần URL callback cố định.

## 1. Chuẩn bị máy chủ

1. Cài và mở Docker Desktop, chờ đến khi Docker Engine báo đang chạy. Chọn Linux containers và WSL 2 nếu Docker Desktop đề xuất.
2. Mở PowerShell tại thư mục dự án:

```powershell
Set-Location D:\HUNRE\Ecommerce\matngotbear
docker version
docker compose version
```

3. Lấy địa chỉ IPv4 của Wi-Fi hiện tại (IP hay đổi mỗi lần đổi mạng/phát lại hotspot):

```powershell
ipconfig
```

Tìm adapter Wi-Fi đang dùng chung với thiết bị khác (ví dụ hiện tại `172.20.10.3`, ví dụ cũ `192.168.1.10`). Đặt DHCP reservation trên router để địa chỉ này không đổi nếu trình bày nhiều lần.

## 2. Tạo môi trường Docker

```powershell
Copy-Item .env.docker.example .env.docker
notepad .env.docker
```

Sửa `APP_URL` thành địa chỉ thật, ví dụ `http://172.20.10.3:8080`, đặt hai mật khẩu MySQL khác nhau và giữ nguyên `DB_HOST=db`. Cổng trong `APP_URL` phải khớp `APP_PORT` (mặc định `8080`) vì `compose.yaml` map `${APP_PORT:-8080}:80` và `scripts/present-online.ps1` tự đọc `APP_PORT` từ `.env.docker`. Để trống `SESSION_DOMAIN` để cookie đúng IP/host hiện tại; `migrate --force` ở bước sau sẽ tạo sẵn bảng `sessions`, `cache`, `jobs` cho `SESSION_DRIVER/CACHE_STORE/QUEUE_CONNECTION=database`.

Tạo khóa Laravel một lần sau khi các service đã build:

```powershell
docker compose --env-file .env.docker build
docker compose --env-file .env.docker run --rm app php artisan key:generate --show
```

Chép giá trị bắt đầu bằng `base64:` vào `APP_KEY` trong `.env.docker`. Không tạo khóa mới trên hệ thống đang có dữ liệu và session.

## 3. Chạy lần đầu

```powershell
docker compose --env-file .env.docker up -d db
docker compose --env-file .env.docker ps
```

Chờ `db` có trạng thái `healthy`. Với hệ thống mới, chạy migration:

```powershell
docker compose --env-file .env.docker run --rm app php artisan migrate --force
```

Container entrypoint tự tạo liên kết `public/storage` vào volume file tải lên.

Nếu chuyển từ MySQL đang có dữ liệu, hãy dump và import trước khi chạy migration. Không dùng `migrate:fresh` vì lệnh này xóa bảng.

Ví dụ import một file dump đã tạo từ MySQL hiện tại:

```powershell
New-Item -ItemType Directory -Force .\backups | Out-Null
# Tạo file này từ máy đang chạy MySQL hiện tại, nếu chưa có:
# mysqldump -h 127.0.0.1 -u root -p --single-transaction matngotbear > .\backups\existing.sql

$dbContainer = docker compose --env-file .env.docker ps -q db
docker cp .\backups\existing.sql "${dbContainer}:/tmp/existing.sql"
docker exec $dbContainer sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" < /tmp/existing.sql'
docker exec $dbContainer rm -f /tmp/existing.sql
```

Sau khi import hoặc migration xong, khởi động toàn bộ service:

```powershell
docker compose --env-file .env.docker run --rm app php artisan migrate --force
docker compose --env-file .env.docker up -d
```

Giữ nguyên `APP_KEY` của hệ thống hiện tại nếu người dùng cũ cần tiếp tục đăng nhập; chỉ tạo khóa mới cho một hệ thống mới.

Mở trên máy chủ trước:

```text
http://localhost:8080
```

Sau đó mở trên điện thoại/laptop cùng Wi-Fi (thay bằng IP Wi-Fi hiện tại của máy chủ):

```text
http://172.20.10.3:8080
```

## 4. Mở cổng trong Windows Firewall

Chạy PowerShell bằng quyền Administrator trên mạng Wi-Fi tin cậy:

```powershell
New-NetFirewallRule `
  -DisplayName "Mat Ngot Bear LAN 8080" `
  -Direction Inbound `
  -Action Allow `
  -Protocol TCP `
  -LocalPort 8080 `
  -Profile Private `
  -RemoteAddress LocalSubnet
```

Nếu thiết bị khác không truy cập được, kiểm tra Wi-Fi có bật AP/client isolation hay đang dùng Guest network không.

## 5. Vận hành hằng ngày

```powershell
# Bật
docker compose --env-file .env.docker up -d

# Xem trạng thái
docker compose --env-file .env.docker ps

# Xem log trực tiếp
docker compose --env-file .env.docker logs -f --tail=100

# Xem log riêng của Laravel, queue hoặc scheduler
docker compose --env-file .env.docker logs -f app queue scheduler

# Dừng nhưng giữ dữ liệu
docker compose --env-file .env.docker stop

# Khởi động lại ứng dụng
docker compose --env-file .env.docker restart app web queue scheduler

# Liệt kê lịch Laravel
docker compose --env-file .env.docker exec app php artisan schedule:list

# Liệt kê job lỗi
docker compose --env-file .env.docker exec app php artisan queue:failed

# Theo dõi tài nguyên
docker stats
```

Không chạy `docker compose down -v` nếu chưa muốn xóa volume database và file tải lên. Không chạy `migrate:fresh`/`migrate:refresh` trên dữ liệu thật. Giữ nguyên `APP_KEY` khi chạy lại, nếu không toàn bộ session/password-reset mã hóa cũ sẽ hỏng.

Service `scheduler` chạy `schedule:work`, nên lệnh tự hủy đơn chưa thanh toán mỗi 15 phút trong `routes/console.php` tiếp tục hoạt động. Service `queue` xử lý các job database queue riêng.

## 6. Cập nhật an toàn

```powershell
docker compose --env-file .env.docker exec app php artisan down
docker compose --env-file .env.docker stop queue scheduler
docker compose --env-file .env.docker build --pull
docker compose --env-file .env.docker run --rm app php artisan migrate --force
docker compose --env-file .env.docker up -d
docker compose --env-file .env.docker exec app php artisan up
```

Nếu ứng dụng không có lệnh `up/down` do phiên bản Laravel hoặc middleware tùy chỉnh, bỏ hai lệnh đó và thực hiện cập nhật ngoài giờ sử dụng. Luôn sao lưu trước migration.

## 7. Sao lưu MySQL và file tải lên

Tạo thư mục backup và lấy tên container:

```powershell
New-Item -ItemType Directory -Force .\backups | Out-Null
$dbContainer = docker compose --env-file .env.docker ps -q db
$appContainer = docker compose --env-file .env.docker ps -q app
```

Sao lưu database vào trong container rồi copy ra máy chủ:

```powershell
docker exec $dbContainer sh -c 'mysqldump --single-transaction --routines --triggers -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" > /tmp/matngotbear.sql'
docker cp "${dbContainer}:/tmp/matngotbear.sql" ".\backups\matngotbear.sql"
docker exec $dbContainer rm -f /tmp/matngotbear.sql
```

Sao lưu volume storage (yêu cầu Docker Desktop có thể tải image Alpine):

```powershell
$backupPath = (Resolve-Path .\backups).Path
docker run --rm --volumes-from $appContainer `
  --mount "type=bind,source=$backupPath,target=/backup" `
  alpine tar -czf /backup/matngotbear-storage.tgz -C /var/www/html/storage .
```

Giữ file `.env.docker` và `APP_KEY` ở nơi riêng. Nên sao lưu hàng ngày và trước mỗi cập nhật; thử khôi phục định kỳ vào môi trường kiểm tra.

## 8. Khôi phục

Khôi phục database sau khi đã kiểm tra đúng file backup:

```powershell
$dbContainer = docker compose --env-file .env.docker ps -q db
docker cp .\backups\matngotbear.sql "${dbContainer}:/tmp/restore.sql"
docker exec $dbContainer sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" < /tmp/restore.sql'
docker exec $dbContainer rm -f /tmp/restore.sql
```

Khôi phục file storage khi đã dừng app/web/queue/scheduler:

```powershell
docker compose --env-file .env.docker stop app web queue scheduler
$appContainer = docker compose --env-file .env.docker ps -aq app
$backupPath = (Resolve-Path .\backups).Path
docker start $appContainer | Out-Null
docker run --rm --volumes-from $appContainer `
  --mount "type=bind,source=$backupPath,target=/backup" `
  alpine sh -c 'tar -xzf /backup/matngotbear-storage.tgz -C /var/www/html/storage'
docker compose --env-file .env.docker up -d
```

## 9. Kiểm tra nhiều người dùng

Kiểm tra bằng nhiều thiết bị: đăng nhập riêng, giỏ hàng riêng, hai người mua cùng sản phẩm sắp hết, voucher giới hạn, upload ảnh và khởi động lại container. Các transaction/khóa tồn kho của ứng dụng cần được kiểm tra thực tế với tải dự kiến; Docker không tự quyết định số người dùng tối đa.

## 10. Giới hạn khi chỉ dùng IP LAN

Đặt hàng nội bộ và quản trị có thể dùng qua IP LAN. Google OAuth và webhook/IPN của nhà cung cấp thanh toán thường cần hostname/HTTPS mà Internet truy cập được; chúng không tự gọi được địa chỉ `192.168.x.x`. Khi đưa ra Internet, cần reverse proxy HTTPS, domain, rate limiting và rà soát lại toàn bộ secret.
