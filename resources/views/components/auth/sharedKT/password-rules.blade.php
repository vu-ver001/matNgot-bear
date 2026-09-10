@props([
    'targetInput' => null,
])

<div class="auth-password-rules" data-password-rules-box @if($targetInput) data-target-input="{{ $targetInput }}" @endif>
    <div class="auth-password-strength" data-password-strength data-level="empty" aria-live="polite">
        <div class="auth-password-strength__copy">
            <span>Độ mạnh mật khẩu:</span>
            <strong data-password-strength-label>Chưa nhập</strong>
        </div>
        <div class="auth-password-strength__bar" aria-hidden="true">
            <span></span><span></span><span></span><span></span>
        </div>
    </div>

    <div class="auth-password-requirements-box">
        <span class="auth-password-requirements-caption">Tiêu chuẩn mật khẩu an toàn:</span>
        <ul class="auth-password-requirements" aria-label="Tiêu chuẩn mật khẩu an toàn">
            <li data-password-requirement="length">
                <span class="auth-password-requirement-icon">
                    <x-auth.sharedKT.icon name="check" />
                </span>
                <span>Sử dụng ít nhất 8 ký tự</span>
            </li>
            <li data-password-requirement="case">
                <span class="auth-password-requirement-icon">
                    <x-auth.sharedKT.icon name="check" />
                </span>
                <span>Kết hợp chữ hoa và chữ thường</span>
            </li>
            <li data-password-requirement="number">
                <span class="auth-password-requirement-icon">
                    <x-auth.sharedKT.icon name="check" />
                </span>
                <span>Bao gồm ít nhất một chữ số (0–9)</span>
            </li>
            <li data-password-requirement="symbol">
                <span class="auth-password-requirement-icon">
                    <x-auth.sharedKT.icon name="check" />
                </span>
                <span>Bao gồm ký tự đặc biệt (!@#$%^&*)</span>
            </li>
        </ul>
    </div>
</div>
