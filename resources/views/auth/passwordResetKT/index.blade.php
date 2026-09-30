{{-- Giao diện: Khôi phục mật khẩu qua mã xác nhận OTP (PasswordResetKT) --}}
@extends('layouts.auth')

@section('title', 'Quên mật khẩu')
@section('card-class', 'auth-card--password')

@php
    // Xác định bước mở đầu: nếu có lỗi form thì giữ ở bước password, ngược lại mở bước email
    $passwordResetInitialStep = $errors->any() ? 'password' : 'email';
    $passwordResetPrefix = 'forgot_password';
@endphp

@section('content')
    <header class="auth-heading auth-heading--compact auth-heading--password">
        <span class="password-recovery__icon" aria-hidden="true">
            <x-auth.sharedKT.icon name="shield" />
        </span>
        <h1>Đặt lại mật khẩu</h1>
        <p>Xác nhận email bằng mã OTP, sau đó tạo mật khẩu mới.</p>
    </header>

    {{-- Form đặt lại mật khẩu gồm 3 bước: Email -> OTP -> Mật khẩu mới --}}
    @include('auth.passwordResetKT.partials.form')

    {{-- Nút quay lại trang đăng nhập --}}
    <p class="auth-footer auth-footer--back">
        <a href="{{ route('login') }}">
            <span aria-hidden="true">←</span> Quay lại đăng nhập
        </a>
    </p>
@endsection
