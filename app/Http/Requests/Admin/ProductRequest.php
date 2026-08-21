<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id'     => ['required', 'integer', 'exists:categories,id'],
            'name'            => ['required', 'string', 'max:200'],
            'description'     => ['nullable', 'string'],
            'price'           => ['required', 'numeric', 'min:0'],
            'sale_price'      => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'size'            => ['nullable', 'string', 'max:50'],
            'color'           => ['nullable', 'string', 'max:50'],
            'material'        => ['nullable', 'string', 'max:100'],
            'stock_quantity'  => ['required', 'integer', 'min:0'],
            'status'          => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
            'sold_count'      => ['nullable', 'integer', 'min:0'],

            // Validate mảng images
            'images'              => ['nullable', 'array'],
            'images.*.image_url'  => ['required_with:images', 'string', 'max:500'],
            'images.*.is_primary' => ['nullable', 'boolean'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Danh mục là bắt buộc.',
            'category_id.exists'   => 'Danh mục không tồn tại.',
            'name.required'        => 'Tên sản phẩm là bắt buộc.',
            'name.max'             => 'Tên sản phẩm không được vượt quá 200 ký tự.',
            'price.required'       => 'Giá sản phẩm là bắt buộc.',
            'price.min'            => 'Giá sản phẩm phải lớn hơn hoặc bằng 0.',
            'sale_price.lt'        => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            'stock_quantity.required' => 'Số lượng tồn kho là bắt buộc.',
            'stock_quantity.min'   => 'Số lượng tồn kho không được âm.',
            'status.required'      => 'Trạng thái sản phẩm là bắt buộc.',
            'status.in'            => 'Trạng thái sản phẩm không hợp lệ.',
        ];
    }
}
