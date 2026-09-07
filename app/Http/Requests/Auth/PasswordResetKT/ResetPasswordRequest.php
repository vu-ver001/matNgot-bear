<?php

namespace App\Http\Requests\Auth\PasswordResetKT;

use App\Models\User;
use App\Support\PasswordKT\PasswordRulesKT;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            'password' => ['required', 'confirmed', PasswordRulesKT::rule()],
        ];
    }

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
