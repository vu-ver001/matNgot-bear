@php
    $passwordResetPrefix = $passwordResetPrefix ?? 'password_reset';
    $passwordResetInitialStep = $passwordResetInitialStep ?? ($errors->any() ? 'password' : 'email');
    $passwordResetEmail = old('email', $passwordResetEmail ?? '');
    $emailId = $passwordResetPrefix.'_email';
    $passwordId = $passwordResetPrefix.'_password';
    $confirmationId = $passwordResetPrefix.'_password_confirmation';
@endphp

<div class="password-reset-flow" data-password-reset-container>
    <ol class="register-progress" aria-label="Tiến trình đặt lại mật khẩu">
        <li data-password-reset-progress-item="email"><span>1</span><small>Email</small></li>
        <li data-password-reset-progress-item="otp"><span>2</span><small>Mã OTP</small></li>
        <li data-password-reset-progress-item="password"><span>3</span><small>Mật khẩu mới</small></li>
    </ol>

    <form
        method="POST"
        action="{{ route('password.store') }}"
        class="auth-form auth-form--register"
        data-password-reset-flow
        data-initial-step="{{ $passwordResetInitialStep }}"
        data-send-code-url="{{ route('password.otp.send') }}"
        data-verify-code-url="{{ route('password.otp.verify') }}"
    >
        @csrf

        <section class="register-step" data-password-reset-step="email">
            <div class="register-step__intro">
                <span class="register-step__icon"><x-auth.sharedKT.icon name="mail" /></span>
                <div>
                    <h2>Nhập email đã đăng ký</h2>
                    <p>Chúng mình sẽ gửi mã OTP gồm 6 chữ số đến email này.</p>
                </div>
            </div>

            <div class="auth-field">
                <label for="{{ $emailId }}">Email</label>
                <div @class(['auth-input-wrap', 'has-error' => $errors->has('email')])>
                    <x-auth.sharedKT.icon name="mail" class="auth-input-icon" />
                    <input
                        id="{{ $emailId }}"
                        type="email"
                        name="email"
                        value="{{ $passwordResetEmail }}"
                        placeholder="Nhập email của bạn"
                        required
                        autocomplete="username"
                        data-password-reset-email-input
                    >
                </div>
                <p class="auth-error" role="alert" data-password-reset-email-message @if (! $errors->has('email')) hidden @endif>{{ $errors->first('email') }}</p>
            </div>

            <button type="button" class="auth-submit flex w-full items-center justify-center gap-2" data-password-reset-next="otp">
                <x-auth.sharedKT.icon name="send" />
                <span>Gửi mã OTP</span>
            </button>
        </section>

        <section class="register-step" data-password-reset-step="otp" hidden>
            <button type="button" class="register-back" data-password-reset-back="email">← Đổi email</button>

            <div class="register-step__intro register-step__intro--center">
                <span class="register-step__icon"><x-auth.sharedKT.icon name="shield" /></span>
                <div>
                    <h2>Nhập mã OTP</h2>
                    <p>Mã đã được gửi đến <strong data-password-reset-email-value></strong></p>
                </div>
            </div>

            <div class="register-otp" role="group" aria-label="Mã OTP gồm 6 chữ số">
                @for ($index = 1; $index <= 6; $index++)
                    <input
                        type="text"
                        inputmode="numeric"
                        maxlength="1"
                        pattern="[0-9]*"
                        aria-label="Chữ số thứ {{ $index }}"
                        @if ($index === 1) autocomplete="one-time-code" @endif
                        data-password-reset-otp-input
                    >
                @endfor
            </div>

            <p class="register-countdown" data-password-reset-countdown aria-live="polite" hidden>
                Mã có hiệu lực trong <strong data-password-reset-countdown-value>01:00</strong>
            </p>

            <p class="register-otp__message" data-password-reset-otp-message aria-live="polite"></p>

            <button type="button" class="auth-submit flex w-full items-center justify-center gap-2" data-password-reset-next="password">
                <span>Xác nhận mã OTP</span>
                <span aria-hidden="true">→</span>
            </button>

            <button type="button" class="register-resend" data-password-reset-resend>Gửi lại mã</button>
        </section>

        <section class="register-step" data-password-reset-step="password" hidden>
            <div class="register-email-summary">
                <span><x-auth.sharedKT.icon name="mail" /></span>
                <div>
                    <span class="register-email-summary__label">Email đã xác nhận</span>
                    <strong data-password-reset-email-value>{{ $passwordResetEmail }}</strong>
                </div>
                <button type="button" data-password-reset-back="email">Sửa</button>
            </div>

            @if ($errors->has('email'))
                <p class="auth-error" role="alert">{{ $errors->first('email') }}</p>
            @endif

            <div class="auth-field">
                <label for="{{ $passwordId }}">Mật khẩu mới</label>
                <div @class(['auth-input-wrap', 'has-error' => $errors->has('password')])>
                    <x-auth.sharedKT.icon name="lock" class="auth-input-icon" />
                    <input
                        id="{{ $passwordId }}"
                        type="password"
                        name="password"
                        placeholder="Nhập mật khẩu mới"
                        required
                        autocomplete="new-password"
                        data-password-reset-new-password
                    >
                    <button class="auth-password-toggle" type="button" data-password-toggle="{{ $passwordId }}" aria-label="Hiện mật khẩu" aria-pressed="false">
                        <x-auth.sharedKT.icon name="eye" data-icon-show />
                        <x-auth.sharedKT.icon name="eye-off" class="hidden" data-icon-hide />
                    </button>
                </div>
                @error('password')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="{{ $confirmationId }}">Xác nhận mật khẩu mới</label>
                <div @class(['auth-input-wrap', 'has-error' => $errors->has('password_confirmation')])>
                    <x-auth.sharedKT.icon name="lock" class="auth-input-icon" />
                    <input
                        id="{{ $confirmationId }}"
                        type="password"
                        name="password_confirmation"
                        placeholder="Nhập lại mật khẩu mới"
                        required
                        autocomplete="new-password"
                    >
                    <button class="auth-password-toggle" type="button" data-password-toggle="{{ $confirmationId }}" aria-label="Hiện mật khẩu" aria-pressed="false">
                        <x-auth.sharedKT.icon name="eye" data-icon-show />
                        <x-auth.sharedKT.icon name="eye-off" class="hidden" data-icon-hide />
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <p class="password-recovery__hint">
                <x-auth.sharedKT.icon name="shield" />
                <span>Mật khẩu cần ít nhất 8 ký tự, có chữ cái và chữ số.</span>
            </p>

            <button type="submit" class="auth-submit flex w-full items-center justify-center gap-2">
                <x-auth.sharedKT.icon name="shield" />
                <span>Đặt lại mật khẩu</span>
            </button>
        </section>
    </form>
</div>
