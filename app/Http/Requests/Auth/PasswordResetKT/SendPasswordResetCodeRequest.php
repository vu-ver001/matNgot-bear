<?php

namespace App\Http\Requests\Auth\PasswordResetKT;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Chức năng: Validate yêu cầu gửi mã OTP quên mật khẩu (PasswordResetKT)
class SendPasswordResetCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hóa email trước khi validate (chuyển chữ thường, xóa khoảng trắng).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

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
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'email.exists' => 'Không tìm thấy tài khoản nào sử dụng email này.',
        ];
    }
}
