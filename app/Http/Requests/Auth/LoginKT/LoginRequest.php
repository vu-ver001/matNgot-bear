<?php

namespace App\Http\Requests\Auth\LoginKT;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// Chức năng: Kiểm tra dữ liệu form Đăng nhập - Giao diện: Trang /login
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Quy tắc kiểm tra: Email hợp lệ và Mật khẩu bắt buộc nhập
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:150'],
            'password' => ['required', 'string'],
        ];
    }

    // Chuẩn hóa email: Xóa khoảng trắng thừa và chuyển hết thành chữ thường trước khi kiểm tra
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    // Thông báo lỗi tiếng Việt tương ứng trên giao diện
    public function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ];
    }

    // Kiểm tra đăng nhập: đối chiếu email/mật khẩu trong CSDL và kiểm tra tài khoản có bị khóa không
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey()); // Tăng số lần thử sai

            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không chính xác.',
            ]);
        }

        // Chặn nếu tài khoản ở trạng thái Bị khóa (BLOCKED)
        if ($this->user()->status !== User::STATUS_ACTIVE) {
            Auth::guard('web')->logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.',
            ]);
        }

        // Đăng nhập đúng: Xóa bộ đếm số lần thử sai
        RateLimiter::clear($this->throttleKey());
    }

    // Chặn người dùng nếu nhập sai quá 5 lần trong 1 phút (chống tấn công dò pass)
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Tạo khóa giới hạn đăng nhập từ email và địa chỉ IP.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
