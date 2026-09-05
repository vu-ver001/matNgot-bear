<?php

namespace App\Http\Requests\ReviewKT;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        if ($this->has('items')) {
            return [
                'order_id' => ['required', 'integer', 'exists:orders,id'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
                'items.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
                'items.*.comment' => ['required', 'string', 'max:1000'],
                'items.*.review_id' => ['nullable', 'integer', 'exists:reviews,id'],
                'items.*.images' => ['nullable', 'array', 'max:5'],
                'items.*.images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            ];
        }

        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Vui lòng chọn sản phẩm cần đánh giá.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao đánh giá phải là số nguyên.',
            'rating.min' => 'Đánh giá tối thiểu 1 sao.',
            'rating.max' => 'Đánh giá tối đa 5 sao.',
            'comment.required' => 'Vui lòng nhập nội dung nhận xét.',
            'comment.string' => 'Nội dung nhận xét không hợp lệ.',
            'comment.max' => 'Nội dung nhận xét không được vượt quá 1000 ký tự.',
            'order_id.required' => 'Mã đơn hàng không được để trống.',
            'order_id.exists' => 'Đơn hàng không tồn tại.',
            'items.required' => 'Danh sách sản phẩm đánh giá không được để trống.',
            'items.*.product_id.required' => 'Sản phẩm đánh giá không hợp lệ.',
            'items.*.rating.required' => 'Vui lòng chọn số sao cho tất cả các sản phẩm.',
            'items.*.rating.min' => 'Đánh giá tối thiểu 1 sao.',
            'items.*.rating.max' => 'Đánh giá tối đa 5 sao.',
            'items.*.comment.required' => 'Vui lòng nhập nhận xét cho tất cả các sản phẩm.',
            'items.*.comment.max' => 'Nội dung nhận xét không được vượt quá 1000 ký tự.',
            'items.*.images.max' => 'Mỗi sản phẩm chỉ được tải lên tối đa 5 ảnh.',
            'items.*.images.*.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'items.*.images.*.mimes' => 'Hình ảnh phải có định dạng JPG, PNG hoặc WEBP.',
            'items.*.images.*.max' => 'Dung lượng mỗi ảnh không được vượt quá 5MB.',
            'images.max' => 'Chỉ được tải lên tối đa 5 ảnh.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'images.*.mimes' => 'Hình ảnh phải có định dạng JPG, PNG hoặc WEBP.',
            'images.*.max' => 'Dung lượng mỗi ảnh không được vượt quá 5MB.',
        ];
    }
}
