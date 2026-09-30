$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

if (-not (Test-Path -LiteralPath '.env.docker')) {
    throw 'Chưa có .env.docker. Hãy copy .env.docker.example thành .env.docker và cấu hình trước.'
}

# Đọc APP_PORT từ .env.docker để healthcheck và tunnel dùng đúng cổng đã cấu hình.
$appPort = '8080'
foreach ($line in (Get-Content -LiteralPath '.env.docker')) {
    if ($line -match '^\s*APP_PORT\s*=\s*(.+?)\s*$') {
        $value = $Matches[1].Trim().Trim('"').Trim("'")
        if ($value -ne '') { $appPort = $value }
    }
}

$healthUrl = "http://localhost:${appPort}/up"
$tunnelUrl = "http://host.docker.internal:${appPort}"

Write-Host 'Đang khởi động Mật Ngọt Bear...' -ForegroundColor Cyan
docker compose --env-file .env.docker up -d

if ($LASTEXITCODE -ne 0) {
    throw 'Không thể khởi động Docker Compose.'
}

Write-Host 'Đang chờ web sẵn sàng...' -ForegroundColor Cyan
$ready = $false

for ($attempt = 1; $attempt -le 30; $attempt++) {
    try {
        $response = Invoke-WebRequest -Uri $healthUrl -UseBasicParsing -TimeoutSec 2
        if ($response.StatusCode -eq 200) {
            $ready = $true
            break
        }
    } catch {
        Start-Sleep -Seconds 2
    }
}

if (-not $ready) {
    docker compose --env-file .env.docker ps
    throw "Website chưa sẵn sàng tại ${healthUrl}. Kiểm tra log Docker trước khi mở tunnel."
}

# Xóa container tunnel cũ nếu một phiên trước bị đóng đột ngột.
docker rm -f matngotbear-quick-tunnel 2>$null | Out-Null

Write-Host ''
Write-Host "Website nội bộ: http://localhost:${appPort}" -ForegroundColor Green
Write-Host 'Cloudflare sẽ in URL https://...trycloudflare.com bên dưới.' -ForegroundColor Green
Write-Host 'Giữ cửa sổ này mở trong lúc trình bày. Nhấn Ctrl+C để đóng tunnel.' -ForegroundColor Yellow
Write-Host ''

docker run --rm --name matngotbear-quick-tunnel `
    cloudflare/cloudflared:latest `
    tunnel --no-autoupdate --url $tunnelUrl
