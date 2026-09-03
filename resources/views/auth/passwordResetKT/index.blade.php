@extends('layouts.auth')

@section('title', 'Quên mật khẩu')
@section('card-class', 'auth-card--password')

@php
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

    @include('auth.passwordResetKT.partials.form')

    <p class="auth-footer auth-footer--back">
        <a href="{{ route('login') }}">
            <span aria-hidden="true">←</span> Quay lại đăng nhập
        </a>
    </p>
@endsection
