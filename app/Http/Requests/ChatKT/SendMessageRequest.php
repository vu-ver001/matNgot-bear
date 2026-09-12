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
            'content' => ['nullable', 'string', 'max:2000', 'required_without_all:image,images'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required_without_all' => 'Vui lòng nhập nội dung hoặc chọn hình ảnh để gửi.',
            'content.string' => 'Nội dung tin nhắn không hợp lệ.',
            'content.max' => 'Tin nhắn không được dài quá 2000 ký tự.',
            'image.image' => 'Tệp đính kèm phải là hình ảnh hợp lệ.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, webp, gif.',
            'image.max' => 'Dung lượng hình ảnh không được vượt quá 5MB.',
            'images.max' => 'Bạn chỉ có thể gửi tối đa 10 hình ảnh mỗi lần.',
            'images.*.image' => 'Tất cả các tệp đính kèm phải là hình ảnh hợp lệ.',
            'images.*.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, webp, gif.',
            'images.*.max' => 'Mỗi hình ảnh không được vượt quá 5MB.',
            'order_id.exists' => 'Đơn hàng liên quan không tồn tại.',
        ];
    }
}
