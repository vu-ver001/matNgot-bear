<?php

namespace App\Http\Requests\Auth\SharedKT;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpCodeRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:150'],
            'code' => ['required', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'code.required' => 'Vui lòng nhập mã xác nhận.',
            'code.digits' => 'Mã xác nhận phải gồm đúng 6 chữ số.',
        ];
    }
}
