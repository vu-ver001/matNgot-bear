@extends('layouts.auth')

@section('title', 'Đăng nhập')
@section('card-class', 'auth-card--login')

@section('content')
    <header class="auth-heading">
        <h1><span aria-hidden="true">👋</span> Chào mừng trở lại!</h1>
        <p>Đăng nhập để tiếp tục hành trình cùng Mật Ngọt Bear</p>
    </header>

    @if (session('status'))
        <div class="auth-status" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="email">Email</label>
            <div @class(['auth-input-wrap', 'has-error' => $errors->has('email')])>
                <x-auth.sharedKT.icon name="mail" class="auth-input-icon" />
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Nhập email của bạn" required autocomplete="username">
            </div>
            @error('email')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">Mật khẩu</label>
            <div @class(['auth-input-wrap', 'has-error' => $errors->has('password')])>
                <x-auth.sharedKT.icon name="lock" class="auth-input-icon" />
                <input id="password" type="password" name="password" placeholder="Nhập mật khẩu của bạn" required autocomplete="current-password">
                <button class="auth-password-toggle" type="button" data-password-toggle="password" aria-label="Hiện mật khẩu" aria-pressed="false">
                    <x-auth.sharedKT.icon name="eye" data-icon-show />
                    <x-auth.sharedKT.icon name="eye-off" class="hidden" data-icon-hide />
                </button>
            </div>
            @error('password')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-options flex justify-between gap-4">
            <label class="auth-checkbox flex cursor-pointer items-center gap-2" for="remember_me">
                <input type="hidden" name="remember" value="0">
                <input id="remember_me" type="checkbox" name="remember" value="1" @checked((bool) old('remember', true))>
                <span>Ghi nhớ đăng nhập</span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-inline-link shrink-0" href="{{ route('password.request') }}">Quên mật khẩu?</a>
            @endif
        </div>

        <button type="submit" class="auth-submit flex w-full items-center justify-center gap-2">
            <x-auth.sharedKT.icon name="paw" />
            <span>Đăng nhập</span>
        </button>
    </form>

    <div class="auth-divider flex items-center gap-4" aria-hidden="true">
        <span></span><small>hoặc</small><span></span>
    </div>

    <a href="{{ route('auth.google.redirect') }}" class="auth-google flex w-full items-center justify-center gap-3">
        <span class="auth-google__mark" aria-hidden="true">G</span>
        <span>Tiếp tục với Google</span>
    </a>

    <p class="auth-footer">
        Chưa có tài khoản?
        <a href="{{ route('register') }}">Đăng ký ngay <span aria-hidden="true">→</span></a>
    </p>
@endsection
