<?php

namespace App\Http\Controllers\Auth\LoginKT;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\RoleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Throwable;

// Chức năng: Đăng nhập bằng tài khoản Google (OAuth2) (LoginKT)
class GoogleAuthController extends Controller
{
    /**
     * [Giao diện] Chuyển hướng người dùng sang trang đăng nhập/chọn tài khoản của Google.
     */
    public function redirect(): RedirectResponse
    {
        // Kiểm tra xem cấu hình Google Client ID/Secret trong .env đã đầy đủ chưa
        if (! $this->isConfigured()) {
            return $this->loginError('Đăng nhập Google chưa được cấu hình. Vui lòng liên hệ quản trị viên.');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Xử lý dữ liệu callback từ Google: đăng nhập tài khoản có sẵn hoặc tự động đăng ký mới.
     */
    public function callback(Request $request): RedirectResponse
    {
        // Kiểm tra cấu hình Google OAuth
        if (! $this->isConfigured()) {
            return $this->loginError('Đăng nhập Google chưa được cấu hình. Vui lòng liên hệ quản trị viên.');
        }

        try {
            // Lấy thông tin tài khoản người dùng từ Google qua Socialite
            /** @var GoogleUser $googleUser */
            $googleUser = Socialite::driver('google')->user();
            $email = Str::lower(trim((string) $googleUser->getEmail()));
            $googleId = trim((string) $googleUser->getId());

            // Kiểm tra email và google ID hợp lệ từ Google
            if ($googleId === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                return $this->loginError('Google không cung cấp email hợp lệ cho tài khoản này.');
            }

            // Kiểm tra email đã được xác minh bên phía Google hay chưa
            if (! $this->hasVerifiedEmail($googleUser)) {
                return $this->loginError('Email Google chưa được xác minh nên không thể đăng nhập.');
            }

            // Tìm kiếm hoặc tạo mới tài khoản trong database (dùng transaction)
            $user = DB::transaction(function () use ($googleUser, $googleId, $email): User {
                $user = User::query()
                    ->where('google_id', $googleId)
                    ->lockForUpdate()
                    ->first();

                if (! $user) {
                    $user = User::query()
                        ->where('email', $email)
                        ->lockForUpdate()
                        ->first();
                }

                // Nếu tài khoản đã tồn tại: liên kết thêm google_id vào tài khoản
                if ($user) {
                    if ($user->google_id !== null && ! hash_equals($user->google_id, $googleId)) {
                        throw ValidationException::withMessages([
                            'email' => 'Email này đã được liên kết với một tài khoản Google khác.',
                        ]);
                    }

                    if ($user->status === User::STATUS_ACTIVE) {
                        $user->forceFill([
                            'google_id' => $googleId,
                            'email_verified_at' => $user->email_verified_at ?? now(),
                        ])->save();
                    }

                    return $user;
                }

                // Nếu chưa có tài khoản: Tạo tài khoản Khách hàng (CUSTOMER) mới
                return User::query()->create([
                    'full_name' => $this->displayName($googleUser, $email),
                    'email' => $email,
                    'email_verified_at' => now(),
                    'google_id' => $googleId,
                    'password' => Str::random(64),
                    'role' => User::ROLE_CUSTOMER,
                    'status' => User::STATUS_ACTIVE,
                ]);
            });
        } catch (ValidationException $exception) {
            return $this->loginError($exception->validator->errors()->first());
        } catch (Throwable $exception) {
            Log::warning('Google OAuth callback failed.', [
                'exception' => $exception::class,
            ]);

            return $this->loginError('Không thể đăng nhập bằng Google. Vui lòng thử lại.');
        }

        // Chặn đăng nhập nếu tài khoản đang bị khóa
        if ($user->status === User::STATUS_BLOCKED) {
            return $this->loginError('Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.');
        }

        // Ghi nhận thời gian đăng nhập và kích hoạt phiên đăng nhập
        $user->recordLogin();
        Auth::login($user);
        $request->session()->regenerate();

        // Chuyển hướng theo vai trò người dùng (Admin / Staff / Customer)
        return redirect()->intended(route(RoleRedirect::routeName($user), absolute: false));
    }

    /**
     * Kiểm tra các biến môi trường cấu hình Google OAuth trong file .env đã đầy đủ chưa.
     */
    private function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }

    /**
     * Kiểm tra cờ xác thực email từ dữ liệu Google trả về.
     */
    private function hasVerifiedEmail(GoogleUser $googleUser): bool
    {
        return filter_var(
            data_get($googleUser->user, 'email_verified', data_get($googleUser->user, 'verified_email', false)),
            FILTER_VALIDATE_BOOL,
        );
    }

    /**
     * Lấy tên hiển thị từ Google hoặc dùng phần tên trước @ của email nếu Google không có tên.
     */
    private function displayName(GoogleUser $googleUser, string $email): string
    {
        $name = trim((string) $googleUser->getName());

        return Str::limit($name !== '' ? $name : Str::before($email, '@'), 100, '');
    }

    /**
     * Chuyển hướng về trang đăng nhập kèm thông báo lỗi.
     */
    private function loginError(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
