@php
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($user->full_name), 0, 1));
    $isActive = $user->status === \App\Models\User::STATUS_ACTIVE;
    $roleLabel = match ($user->role) {
        \App\Models\User::ROLE_CUSTOMER => 'Khách hàng',
        \App\Models\User::ROLE_STAFF => 'Nhân viên',
        \App\Models\User::ROLE_ADMIN => 'Quản trị viên',
        default => $user->role,
    };
    $statusLabel = $isActive ? 'Hoạt động' : 'Bị khóa';
    $registrationMethod = $user->google_id ? 'Google' : 'Email';
    $startInEditMode = session('profile-editing', false) || $errors->any() || $emailChangeRequest !== null;
    $emailStartsEditable = $errors->has('email') && ! session('profile-email-locked', false);
    $profileToast = match (session('status')) {
        'profile-updated' => ['type' => 'success', 'message' => 'Cập nhật hồ sơ thành công.'],
        'email-change-code-sent' => ['type' => 'success', 'message' => 'Mã xác nhận đã được gửi đến email mới.'],
        'email-updated' => ['type' => 'success', 'message' => 'Đổi email thành công. Lần đăng nhập sau hãy dùng email mới.'],
        'email-change-cancelled' => ['type' => 'info', 'message' => 'Đã hủy yêu cầu đổi email.'],
        'profile-no-changes' => ['type' => 'info', 'message' => 'Thông tin chưa có thay đổi.'],
        default => null,
    };
    $profileLayout = $user->role === \App\Models\User::ROLE_CUSTOMER
        ? 'customer-account-layout'
        : 'app-layout';
@endphp

<x-dynamic-component :component="$profileLayout" title="Hồ sơ cá nhân" :flush="true">
    <div
        @class([
            'profile-page',
            'is-editing' => $startInEditMode,
            'uses-default-layout' => $user->role !== \App\Models\User::ROLE_CUSTOMER,
        ])
        data-profile-editor
        data-profile-editing-initially="{{ $startInEditMode ? 'true' : 'false' }}"
        data-profile-email-pending="{{ $emailChangeRequest ? 'true' : 'false' }}"
        data-profile-current-email="{{ $user->email }}"
    >
        <div
            @class([
                'account-toast',
                'is-visible' => $profileToast,
                'is-info' => $profileToast && $profileToast['type'] === 'info',
            ])
            data-account-toast
            data-profile-toast
            role="status"
            aria-live="polite"
        >
            <span aria-hidden="true">{{ $profileToast && $profileToast['type'] === 'info' ? 'i' : '✓' }}</span>
            <p>{{ $profileToast['message'] ?? '' }}</p>
            <button type="button" data-account-toast-close aria-label="Đóng thông báo">×</button>
        </div>

        <section
            class="profile-hero"
            aria-labelledby="profile-user-name"
            style="--profile-hero-image: url('{{ asset('images/profile/profile-banner-table.png') }}')"
        >
            <div class="profile-hero-avatar">
                <img
                    @class(['hidden' => ! $user->avatar_url])
                    @if ($user->avatar_url) src="{{ $user->avatar_url }}" @endif
                    alt="Ảnh đại diện của {{ $user->full_name }}"
                    data-profile-avatar-preview
                >
                <span
                    @class(['profile-avatar-fallback', 'hidden' => $user->avatar_url])
                    data-profile-avatar-fallback
                    aria-hidden="true"
                >{{ $initial }}</span>

                <label
                    for="avatar"
                    class="profile-camera-button"
                    title="Thay ảnh đại diện"
                    data-profile-edit-only
                    @unless ($startInEditMode) hidden @endunless
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 8h4l1.5-2h5L16 8h4v11H4V8Z" />
                        <circle cx="12" cy="13" r="3.2" />
                    </svg>
                    <span class="sr-only">Thay ảnh đại diện</span>
                    <input
                        id="avatar"
                        name="avatar"
                        type="file"
                        form="profile-update-form"
                        class="sr-only"
                        accept="image/jpeg,image/png"
                        data-profile-avatar-input
                    >
                </label>
            </div>

            <div class="profile-hero-copy">
                <h1 id="profile-user-name">{{ $user->full_name }}</h1>
                <p>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="m4 7 8 6 8-6" />
                    </svg>
                    <span data-account-current-email>{{ $user->email }}</span>
                </p>
                <p>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="4" y="6" width="16" height="14" rx="2" />
                        <path d="M8 3v6m8-6v6M4 11h16" />
                    </svg>
                    Tham gia từ {{ $user->created_at?->format('d/m/Y') }}
                </p>

                @error('avatar')
                    <p class="profile-avatar-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

        </section>

        <div class="profile-card-grid">
            <section class="profile-card" aria-labelledby="personal-information-title">
                <header class="profile-card-header">
                    @include('ProfileKT.partials.user-icon')
                    <h2 id="personal-information-title">Thông tin cá nhân</h2>
                    <button
                        type="button"
                        class="profile-edit-button"
                        data-profile-edit-button
                        @if ($startInEditMode) hidden @endif
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Zm10-12 3 3" />
                        </svg>
                        Chỉnh sửa thông tin
                    </button>
                </header>

                <form id="profile-email-code-form" method="POST" action="{{ route('profile.email.code') }}">
                    @csrf
                </form>

                <form id="profile-email-verify-form" method="POST" action="{{ route('profile.email.verify') }}">
                    @csrf
                    @method('PATCH')
                </form>

                <form id="profile-email-cancel-form" method="POST" action="{{ route('profile.email.cancel') }}">
                    @csrf
                    @method('DELETE')
                </form>

                <form
                    id="profile-update-form"
                    method="POST"
                    action="{{ route('profile.update') }}"
                    enctype="multipart/form-data"
                    class="profile-detail-form"
                >
                    @csrf
                    @method('PATCH')

                    <div class="profile-form-row">
                        <label for="full_name">Họ và tên <span aria-hidden="true">*</span></label>
                        <div>
                            <input
                                id="full_name"
                                name="full_name"
                                type="text"
                                class="account-input"
                                value="{{ old('full_name', $user->full_name) }}"
                                autocomplete="name"
                                maxlength="100"
                                required
                                data-profile-editable
                                data-profile-original-value="{{ $user->full_name }}"
                                @unless ($startInEditMode) readonly aria-readonly="true" @endunless
                            >
                            @error('full_name')
                                <p class="account-field-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="profile-form-row">
                        <label for="phone">Số điện thoại</label>
                        <div>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                class="account-input"
                                value="{{ old('phone', $user->phone) }}"
                                autocomplete="tel"
                                maxlength="20"
                                data-profile-editable
                                data-profile-original-value="{{ $user->phone }}"
                                @unless ($startInEditMode) readonly aria-readonly="true" @endunless
                            >
                            @error('phone')
                                <p class="account-field-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="profile-form-row">
                        <label for="email">Email <span aria-hidden="true">*</span></label>
                        <div>
                            <div class="profile-email-editor">
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    class="account-input"
                                    value="{{ old('email', $emailChangeRequest?->email ?? $user->email) }}"
                                    autocomplete="email"
                                    maxlength="150"
                                    required
                                    form="profile-email-code-form"
                                    data-profile-email-input
                                    @unless ($emailStartsEditable) readonly aria-readonly="true" @endunless
                                >
                                <button
                                    type="{{ $emailChangeRequest || $emailStartsEditable ? 'submit' : 'button' }}"
                                    form="profile-email-code-form"
                                    data-profile-edit-only
                                    data-profile-email-action
                                    @unless ($startInEditMode) hidden @endunless
                                >
                                    {{ $emailStartsEditable ? 'Gửi mã' : ($emailChangeRequest ? 'Gửi lại mã' : 'Đổi email') }}
                                </button>
                            </div>
                            <p
                                class="profile-email-note"
                                data-profile-edit-only
                                @unless ($startInEditMode) hidden @endunless
                            >Email chỉ thay đổi sau khi bạn nhập đúng mã xác nhận.</p>
                            @error('email')
                                <p class="account-field-error" role="alert" data-profile-email-error>{{ $message }}</p>
                            @else
                                <p class="account-field-error" role="alert" data-profile-email-error hidden></p>
                            @enderror
                        </div>
                    </div>

                    <div
                        class="profile-form-row profile-email-verification"
                        data-profile-email-verification
                        @unless ($emailChangeRequest) hidden @endunless
                    >
                        <label for="email_change_code">Mã xác nhận</label>
                        <div>
                            <div class="profile-email-code-entry">
                                <input
                                    id="email_change_code"
                                    name="code"
                                    type="text"
                                    class="account-input"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    maxlength="6"
                                    placeholder="Nhập 6 chữ số"
                                    required
                                    form="profile-email-verify-form"
                                >
                                <div class="profile-email-code-actions">
                                    <button type="submit" form="profile-email-verify-form">Xác nhận</button>
                                    <button
                                        type="submit"
                                        form="profile-email-cancel-form"
                                        class="profile-email-cancel-button"
                                    >Hủy</button>
                                </div>
                                <div class="profile-email-pending-meta">
                                    <p class="profile-email-note">
                                        Mã đã gửi tới
                                        <strong data-profile-pending-email>{{ $emailChangeRequest?->email }}</strong>
                                        và có hiệu lực trong 5 phút.
                                    </p>
                                </div>
                            </div>
                            @error('code')
                                <p class="account-field-error" role="alert" data-profile-code-error>{{ $message }}</p>
                            @else
                                <p class="account-field-error" role="alert" data-profile-code-error hidden></p>
                            @enderror
                        </div>
                    </div>

                    <div class="profile-form-row">
                        <label for="address">Địa chỉ</label>
                        <div>
                            <textarea
                                id="address"
                                name="address"
                                class="account-textarea profile-address-input"
                                rows="2"
                                maxlength="255"
                                autocomplete="street-address"
                                data-profile-editable
                                data-profile-original-value="{{ $user->address }}"
                                @unless ($startInEditMode) readonly aria-readonly="true" @endunless
                            >{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <p class="account-field-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div
                        class="profile-form-actions"
                        data-profile-edit-only
                        @unless ($startInEditMode) hidden @endunless
                    >
                        <button type="button" class="profile-cancel-edit-button" data-profile-cancel-edit>
                            Hủy chỉnh sửa
                        </button>
                        <button type="submit" class="profile-save-button">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5 3h12l2 2v16H5V3Zm4 0v6h6V3M8 21v-7h8v7" />
                            </svg>
                            Lưu thay đổi
                        </button>
                    </div>
                </form>
            </section>

            <section class="profile-card" aria-labelledby="account-information-title">
                <header class="profile-card-header">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m12 3 7 3v5c0 4.6-2.8 8.3-7 10-4.2-1.7-7-5.4-7-10V6l7-3Z" />
                        <path d="m9 12 2 2 4-5" />
                    </svg>
                    <h2 id="account-information-title">Thông tin tài khoản</h2>
                </header>

                <dl class="profile-account-table">
                    <div>
                        <dt>Vai trò</dt>
                        <dd>{{ $roleLabel }}</dd>
                    </div>
                    <div>
                        <dt>Trạng thái</dt>
                        <dd>
                            <span @class(['profile-status-badge', 'is-active' => $isActive, 'is-blocked' => ! $isActive])>
                                {{ $statusLabel }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt>Đăng ký qua</dt>
                        <dd>{{ $registrationMethod }}</dd>
                    </div>
                    <div>
                        <dt>Lần đăng nhập cuối</dt>
                        <dd>{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Chưa ghi nhận' }}</dd>
                    </div>
                </dl>

                <div class="profile-support-box">
                    <span class="profile-support-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 13v-1a8 8 0 0 1 16 0v1M4 13H3v5h4v-5H4Zm16 0h1v5h-4v-5h3Zm-3 5c0 2-2 3-5 3" />
                        </svg>
                    </span>
                    <div>
                        <strong>Cần hỗ trợ?</strong>
                        <p>Đội ngũ Mật Ngọt Bear luôn sẵn sàng giúp bạn.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-dynamic-component>
