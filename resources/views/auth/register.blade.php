@extends('layouts.auth')

@section('title', 'Đăng ký')
@section('card-class', 'auth-card--register')

@php
    $initialRegisterStep = $errors->has('name') || $errors->has('password') || $errors->has('password_confirmation')
        ? 'details'
        : 'email';
@endphp

@section('content')
    <header class="auth-heading auth-heading--compact">
        <h1><span aria-hidden="true">👋</span> Tạo tài khoản mới</h1>
        <p>Xác nhận email trước, sau đó hoàn tất thông tin của bạn.</p>
    </header>

    <ol class="register-progress" aria-label="Tiến trình đăng ký">
        <li data-register-progress-item="email"><span>1</span><small>Email</small></li>
        <li data-register-progress-item="otp"><span>2</span><small>Mã xác nhận</small></li>
        <li data-register-progress-item="details"><span>3</span><small>Thông tin</small></li>
    </ol>

    <form
        method="POST"
        action="{{ route('register') }}"
        class="auth-form auth-form--register"
        data-register-flow
        data-initial-step="{{ $initialRegisterStep }}"
    >
        @csrf

        <section class="register-step" data-register-step="email">
            <div class="register-step__intro">
                <span class="register-step__icon"><x-auth-icon name="mail" /></span>
                <div>
                    <h2>Bắt đầu với email của bạn</h2>
                    <p>Mã xác nhận sẽ được gửi đến địa chỉ email này.</p>
                </div>
            </div>

            <div class="auth-field">
                <label for="register_email">Email</label>
                <div @class(['auth-input-wrap', 'has-error' => $errors->has('email')])>
                    <x-auth-icon name="mail" class="auth-input-icon" />
                    <input
                        id="register_email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Nhập email của bạn"
                        required
                        autocomplete="username"
                    >
                </div>
                @error('email')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="button" class="auth-submit flex w-full items-center justify-center gap-2" data-register-next="otp">
                <span>Gửi mã xác nhận</span>
                <span aria-hidden="true">→</span>
            </button>

        </section>

        <section class="register-step" data-register-step="otp" hidden>
            <button type="button" class="register-back" data-register-back="email">← Đổi email</button>

            <div class="register-step__intro register-step__intro--center">
                <span class="register-step__icon"><x-auth-icon name="shield" /></span>
                <div>
                    <h2>Nhập mã xác nhận</h2>
                    <p>Mã gồm 6 chữ số gửi đến <strong data-register-email-value></strong></p>
                </div>
            </div>

            <div class="register-otp" role="group" aria-label="Mã xác nhận gồm 6 chữ số">
                @for ($index = 1; $index <= 6; $index++)
                    <input
                        type="text"
                        inputmode="numeric"
                        maxlength="1"
                        pattern="[0-9]*"
                        aria-label="Chữ số thứ {{ $index }}"
                        @if ($index === 1) autocomplete="one-time-code" @endif
                        data-otp-input
                    >
                @endfor
            </div>

            <p class="register-otp__message" data-otp-message aria-live="polite"></p>

            <button type="button" class="auth-submit flex w-full items-center justify-center gap-2" data-register-next="details">
                <span>Xem bước tiếp theo</span>
                <span aria-hidden="true">→</span>
            </button>

            <button type="button" class="register-resend" data-register-resend>Gửi lại mã</button>

        </section>

        <section class="register-step" data-register-step="details" hidden>
            <div class="register-email-summary">
                <span><x-auth-icon name="mail" /></span>
                <div>
                    <small>Email dùng để đăng ký</small>
                    <strong data-register-email-value>{{ old('email') }}</strong>
                </div>
                <button type="button" data-register-back="email">Sửa</button>
            </div>

            <div class="auth-field">
                <label for="name">Họ và tên</label>
                <div @class(['auth-input-wrap', 'has-error' => $errors->has('name')])>
                    <x-auth-icon name="user" class="auth-input-icon" />
                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Nhập họ và tên của bạn" required autocomplete="name">
                </div>
                @error('name')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="phone">Số điện thoại <span>(tùy chọn)</span></label>
                <div class="auth-input-wrap">
                    <x-auth-icon name="phone" class="auth-input-icon" />
                    {{-- UI only: the current registration controller does not store phone yet. --}}
                    <input id="phone" type="tel" placeholder="Nhập số điện thoại của bạn" autocomplete="tel" data-backend-field-pending>
                </div>
            </div>

            <div class="auth-field">
                <label for="register_password">Mật khẩu</label>
                <div @class(['auth-input-wrap', 'has-error' => $errors->has('password')])>
                    <x-auth-icon name="lock" class="auth-input-icon" />
                    <input id="register_password" type="password" name="password" placeholder="Nhập mật khẩu của bạn" required autocomplete="new-password">
                    <button class="auth-password-toggle" type="button" data-password-toggle="register_password" aria-label="Hiện mật khẩu" aria-pressed="false">
                        <x-auth-icon name="eye" data-icon-show />
                        <x-auth-icon name="eye-off" class="hidden" data-icon-hide />
                    </button>
                </div>
                @error('password')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password_confirmation">Xác nhận mật khẩu</label>
                <div @class(['auth-input-wrap', 'has-error' => $errors->has('password_confirmation')])>
                    <x-auth-icon name="lock" class="auth-input-icon" />
                    <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu" required autocomplete="new-password">
                    <button class="auth-password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Hiện mật khẩu" aria-pressed="false">
                        <x-auth-icon name="eye" data-icon-show />
                        <x-auth-icon name="eye-off" class="hidden" data-icon-hide />
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <label class="auth-checkbox auth-terms flex cursor-pointer items-start gap-2" for="terms">
                {{-- UI only: add a name and backend validation when terms handling is implemented. --}}
                <input id="terms" type="checkbox" required>
                <span>
                    Tôi đồng ý với <a href="#" data-placeholder-link>Điều khoản sử dụng</a>
                    và <a href="#" data-placeholder-link>Chính sách bảo mật</a>
                </span>
            </label>

            <button type="submit" class="auth-submit flex w-full items-center justify-center gap-2">
                <x-auth-icon name="paw" />
                <span>Đăng ký</span>
            </button>
        </section>
    </form>

    <div class="auth-divider flex items-center gap-4" aria-hidden="true">
        <span></span><small>hoặc</small><span></span>
    </div>

    {{-- TODO: Connect Google OAuth when the backend is available. --}}
    <button type="button" class="auth-google flex w-full items-center justify-center gap-3">
        <span class="auth-google__mark" aria-hidden="true">G</span>
        <span>Đăng ký với Google</span>
    </button>

    <p class="auth-footer">
        Đã có tài khoản?
        <a href="{{ route('login') }}">Đăng nhập ngay <span aria-hidden="true">→</span></a>
    </p>
@endsection
