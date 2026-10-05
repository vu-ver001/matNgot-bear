<?php

namespace App\Http\Requests\Auth\RegistrationKT;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Chức năng: Validate yêu cầu gửi mã xác nhận OTP đăng ký (RegistrationKT)
class SendRegistrationCodeRequest extends FormRequest
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

    /**
     * Quy tắc kiểm tra: Email bắt buộc, đúng định dạng, tối đa 150 ký tự và chưa từng được đăng ký.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique(User::class, 'email'),
            ],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt cho trường email.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'email.unique' => 'Email này đã được sử dụng.',
        ];
    }
}
