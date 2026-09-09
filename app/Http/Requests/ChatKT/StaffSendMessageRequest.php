<?php

namespace App\Http\Requests\ChatKT;

use Illuminate\Foundation\Http\FormRequest;

class StaffSendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && in_array($user->role, [\App\Models\User::ROLE_STAFF, \App\Models\User::ROLE_ADMIN], true);
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:2000'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Vui lòng nhập nội dung phản hồi.',
            'content.string' => 'Nội dung phản hồi không hợp lệ.',
            'content.max' => 'Tin nhắn không được dài quá 2000 ký tự.',
        ];
    }
}
