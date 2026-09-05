<?php

namespace App\Http\Requests\ReviewKT;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao đánh giá phải là số nguyên.',
            'rating.min' => 'Đánh giá tối thiểu 1 sao.',
            'rating.max' => 'Đánh giá tối đa 5 sao.',
            'comment.required' => 'Vui lòng nhập nội dung nhận xét.',
            'comment.string' => 'Nội dung nhận xét không hợp lệ.',
            'comment.max' => 'Nội dung nhận xét không được vượt quá 1000 ký tự.',
            'images.max' => 'Chỉ được tải lên tối đa 5 ảnh.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'images.*.mimes' => 'Hình ảnh phải có định dạng JPG, PNG hoặc WEBP.',
            'images.*.max' => 'Dung lượng mỗi ảnh không được vượt quá 5MB.',
        ];
    }
}
