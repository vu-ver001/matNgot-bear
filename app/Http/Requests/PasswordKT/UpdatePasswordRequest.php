<?php

namespace App\Http\Requests\PasswordKT;

use App\Support\PasswordKT\PasswordRulesKT;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

// Chức năng: Validate dữ liệu form đổi mật khẩu tài khoản (PasswordKT)
class UpdatePasswordRequest extends FormRequest
{
    /**
     * Tách túi lỗi đổi mật khẩu riêng biệt khỏi lỗi của modal quên mật khẩu.
     */
    protected $errorBag = 'updatePassword';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Quy tắc kiểm tra: Mật khẩu hiện tại phải đúng, mật khẩu mới đúng chuẩn và phải khác mật khẩu cũ.
     */
    public function rules(): array
    {
        return [
            // Mật khẩu hiện tại: bắt buộc, kiểm tra trùng khớp với mật khẩu đang lưu trong DB
            'current_password' => ['bail', 'required', 'current_password'],
            // Mật khẩu mới: bắt buộc, confirmed, tuân thủ PasswordRulesKT và phải khác mật khẩu hiện tại
            'password' => [
                'bail',
                'required',
                'confirmed',
                PasswordRulesKT::rule(),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && Hash::check($value, $this->user()->password)) {
                        $fail('Mật khẩu mới phải khác mật khẩu hiện tại.');
                    }
                },
            ],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt tương ứng.
     */
    public function messages(): array
    {
        return array_merge([
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
        ], PasswordRulesKT::messages('password', true));
    }
}
