$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

docker rm -f matngotbear-quick-tunnel 2>$null | Out-Null
docker compose --env-file .env.docker stop

Write-Host 'Đã tắt tunnel và các service Mật Ngọt Bear.' -ForegroundColor Green
