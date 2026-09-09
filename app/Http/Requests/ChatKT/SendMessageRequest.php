<?php

namespace App\Http\Requests\ChatKT;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
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
            'content.required' => 'Vui lòng nhập nội dung tin nhắn.',
            'content.string' => 'Nội dung tin nhắn không hợp lệ.',
            'content.max' => 'Tin nhắn không được dài quá 2000 ký tự.',
            'order_id.exists' => 'Đơn hàng liên quan không tồn tại.',
        ];
    }
}
