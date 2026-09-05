<?php

namespace App\Http\Requests\PasswordKT;

use App\Support\PasswordKT\PasswordRulesKT;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Tách lỗi đổi mật khẩu khỏi lỗi của popup quên mật khẩu.
     */
    protected $errorBag = 'updatePassword';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['bail', 'required', 'current_password'],
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

    public function messages(): array
    {
        return array_merge([
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
        ], PasswordRulesKT::messages('password', true));
    }
}
