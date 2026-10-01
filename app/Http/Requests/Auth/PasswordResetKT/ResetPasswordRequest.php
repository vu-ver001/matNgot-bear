<?php

namespace App\Http\Requests\Auth\PasswordResetKT;

use App\Models\User;
use App\Support\PasswordKT\PasswordRulesKT;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Chức năng: Validate yêu cầu đặt lại mật khẩu mới (PasswordResetKT)
class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hóa email trước khi kiểm tra (chuyển chữ thường, xóa khoảng trắng).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    /**
     * Quy tắc kiểm tra: Email tồn tại trong hệ thống, mật khẩu mới đúng chuẩn bảo mật và xác nhận khớp.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::exists(User::class, 'email'),
            ],
            // Mật khẩu mới bắt buộc, xác nhận lại (confirmed) và tuân thủ PasswordRulesKT (8+ ký tự, chữ hoa, số, ký tự đặc biệt)
            'password' => ['required', 'confirmed', PasswordRulesKT::rule()],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt tương ứng.
     */
    public function messages(): array
    {
        return array_merge([
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'email.exists' => 'Không tìm thấy tài khoản nào sử dụng email này.',
        ], PasswordRulesKT::messages('password', true));
    }
}
