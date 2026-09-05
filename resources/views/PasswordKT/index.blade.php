@php
    $user = auth()->user();
    $layout = $user->role === \App\Models\User::ROLE_CUSTOMER
        ? 'customer-account-layout'
        : 'app-layout';
    $passwordErrors = $errors->getBag('updatePassword');
    $resetModalOpen = $errors->getBag('default')->any();
    $passwordToast = match (session('status')) {
        'password-updated' => 'Đổi mật khẩu thành công.',
        'password-reset' => 'Đặt lại mật khẩu thành công.',
        default => null,
    };
    $passwordResetPrefix = 'password_modal';
    $passwordResetInitialStep = $resetModalOpen ? 'password' : 'email';
    $passwordResetEmail = old('email', $user->email);
@endphp

<x-dynamic-component :component="$layout" title="Đổi mật khẩu" :flush="true">
    <div
        class="password-page {{ $user->role !== \App\Models\User::ROLE_CUSTOMER ? 'uses-default-layout' : '' }}"
        data-password-page
        data-reset-modal-open="{{ $resetModalOpen ? 'true' : 'false' }}"
    >
        @if ($passwordToast)
            <div class="password-toast is-visible" data-password-toast role="status" aria-live="polite">
                <span aria-hidden="true">✓</span>
                <p>{{ $passwordToast }}</p>
                <button type="button" data-password-toast-close aria-label="Đóng thông báo">×</button>
            </div>
        @endif

        <section
            class="password-banner"
            aria-label="Không gian ấm áp của Mật Ngọt Bear"
            style="--password-banner-image: url('{{ asset('images/passwordKT/password-banner.png') }}')"
        >
            <div class="password-banner-badge">
                <span class="password-banner-badge-icon">
                    @include('PasswordKT.partials.icon', ['name' => 'shield'])
                </span>
                <span>Trung tâm bảo mật tài khoản</span>
            </div>
        </section>

        <header class="password-heading">
            <span class="password-heading-icon">
                @include('PasswordKT.partials.icon', ['name' => 'lock'])
            </span>
            <div class="password-heading-copy">
                <h1>Đổi mật khẩu</h1>
                <p>Cập nhật mật khẩu định kỳ giúp tài khoản và đơn hàng của bạn luôn được an toàn.</p>
            </div>
        </header>

        <div class="password-grid">
            <section class="password-card password-form-card" aria-label="Biểu mẫu đổi mật khẩu">
                <div class="password-form-header">
                    <span class="password-form-badge">
                        @include('PasswordKT.partials.icon', ['name' => 'key'])
                        Thiết lập mật khẩu mới
                    </span>
                    <span class="password-required-note">
                        <span class="password-star">*</span> Bắt buộc
                    </span>
                </div>

                <form
                    method="POST"
                    action="{{ route('account.password.update') }}"
                    class="password-form"
                    data-change-password-form
                >
                    @csrf
                    @method('PUT')

                    <div class="password-field">
                        <div class="password-field-header">
                            <label for="current_password">Mật khẩu hiện tại <span class="password-star">*</span></label>
                            <button
                                type="button"
                                class="password-forgot-inline"
                                data-password-reset-open
                            >
                                Quên mật khẩu?
                            </button>
                        </div>
                        <div @class(['password-input-wrap', 'has-error' => $passwordErrors->has('current_password')])>
                            @include('PasswordKT.partials.icon', ['name' => 'key'])
                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="Nhập mật khẩu bạn đang sử dụng"
                            >
                            <button type="button" class="password-visibility" data-password-toggle="current_password" aria-label="Hiện mật khẩu" aria-pressed="false">
                                <x-auth.sharedKT.icon name="eye" data-icon-show />
                                <x-auth.sharedKT.icon name="eye-off" class="hidden" data-icon-hide />
                            </button>
                        </div>
                        @if ($passwordErrors->has('current_password'))
                            <p class="password-field-error" role="alert">{{ $passwordErrors->first('current_password') }}</p>
                        @endif
                    </div>

                    <div class="password-field">
                        <label for="new_password">Mật khẩu mới <span class="password-star">*</span></label>
                        <div @class(['password-input-wrap', 'has-error' => $passwordErrors->has('password')])>
                            @include('PasswordKT.partials.icon', ['name' => 'lock'])
                            <input
                                id="new_password"
                                name="password"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="Tối thiểu 8 ký tự, chữ hoa, số & ký tự đặc biệt"
                                data-new-password
                            >
                            <button type="button" class="password-visibility" data-password-toggle="new_password" aria-label="Hiện mật khẩu" aria-pressed="false">
                                <x-auth.sharedKT.icon name="eye" data-icon-show />
                                <x-auth.sharedKT.icon name="eye-off" class="hidden" data-icon-hide />
                            </button>
                        </div>

                        <div class="password-strength" data-password-strength data-level="empty" aria-live="polite">
                            <div class="password-strength-copy">
                                <span>Độ mạnh mật khẩu:</span>
                                <strong data-password-strength-label>Chưa nhập</strong>
                            </div>
                            <div class="password-strength-bar" aria-hidden="true">
                                <span></span><span></span><span></span><span></span>
                            </div>
                        </div>

                        @if ($passwordErrors->has('password'))
                            <p class="password-field-error" role="alert">{{ $passwordErrors->first('password') }}</p>
                        @endif
                    </div>

                    <div class="password-field">
                        <label for="new_password_confirmation">Xác nhận mật khẩu mới <span class="password-star">*</span></label>
                        <div class="password-input-wrap">
                            @include('PasswordKT.partials.icon', ['name' => 'lock'])
                            <input
                                id="new_password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="Nhập lại chính xác mật khẩu mới"
                            >
                            <button type="button" class="password-visibility" data-password-toggle="new_password_confirmation" aria-label="Hiện mật khẩu" aria-pressed="false">
                                <x-auth.sharedKT.icon name="eye" data-icon-show />
                                <x-auth.sharedKT.icon name="eye-off" class="hidden" data-icon-hide />
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="password-submit" data-password-submit>
                        @include('PasswordKT.partials.icon', ['name' => 'shield'])
                        <span data-password-submit-label>Lưu mật khẩu mới</span>
                    </button>
                </form>
            </section>

            <aside class="password-card password-security-card" aria-labelledby="password-security-title">
                <header class="password-card-heading">
                    @include('PasswordKT.partials.icon', ['name' => 'shield'])
                    <div>
                        <h2 id="password-security-title">Bảo mật tài khoản</h2>
                        <p>Mật khẩu mạnh giúp bảo vệ an toàn cho thông tin và đơn hàng của bạn.</p>
                    </div>
                </header>

                <div class="password-security-message">
                    @include('PasswordKT.partials.icon', ['name' => 'info'])
                    <p>Tuyệt đối không chia sẻ mật khẩu hoặc mã OTP xác nhận cho bất kỳ ai.</p>
                </div>

                <div class="password-requirements-box">
                    <span class="password-requirements-caption">Tiêu chuẩn mật khẩu an toàn:</span>
                    <ul class="password-requirements" aria-label="Gợi ý tạo mật khẩu mạnh">
                        <li data-password-requirement="length">
                            <span>@include('PasswordKT.partials.icon', ['name' => 'check'])</span>
                            Sử dụng ít nhất 8 ký tự
                        </li>
                        <li data-password-requirement="case">
                            <span>@include('PasswordKT.partials.icon', ['name' => 'check'])</span>
                            Kết hợp chữ hoa và chữ thường
                        </li>
                        <li data-password-requirement="number">
                            <span>@include('PasswordKT.partials.icon', ['name' => 'check'])</span>
                            Bao gồm ít nhất một chữ số (0–9)
                        </li>
                        <li data-password-requirement="symbol">
                            <span>@include('PasswordKT.partials.icon', ['name' => 'check'])</span>
                            Bao gồm ký tự đặc biệt (!@#$%^&*)
                        </li>
                    </ul>
                </div>

                <div class="password-security-note">
                    @include('PasswordKT.partials.icon', ['name' => 'shield'])
                    <div>
                        <strong>Mẹo an toàn</strong>
                        <p>Hãy cập nhật ngay khi phát hiện bất kỳ dấu hiệu truy cập bất thường nào.</p>
                    </div>
                </div>
            </aside>
        </div>

        <div
            class="password-reset-modal"
            data-password-reset-modal
            role="dialog"
            aria-modal="true"
            aria-labelledby="password-reset-modal-title"
            @unless ($resetModalOpen) hidden @endunless
        >
            <button type="button" class="password-reset-backdrop" data-password-reset-close aria-label="Đóng popup"></button>

            <section class="password-reset-dialog" role="document">
                <header class="password-reset-header">
                    <div>
                        <span class="password-reset-header-icon">@include('PasswordKT.partials.icon', ['name' => 'key'])</span>
                        <div>
                            <h2 id="password-reset-modal-title">Quên mật khẩu</h2>
                            <p>Xác nhận email để tạo lại mật khẩu an toàn.</p>
                        </div>
                    </div>
                    <button type="button" class="password-reset-close" data-password-reset-close aria-label="Đóng popup">×</button>
                </header>

                <div class="password-reset-content">
                    @include('auth.passwordResetKT.partials.form')
                </div>
            </section>
        </div>
    </div>
</x-dynamic-component>
