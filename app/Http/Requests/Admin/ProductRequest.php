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
            'sale_start_at'   => ['nullable', 'date', $this->isMethod('post') ? 'after_or_equal:today' : 'nullable'],
            'sale_end_at'     => ['nullable', 'date', 'after_or_equal:sale_start_at'],
            'size'            => ['nullable', 'string', 'max:50'],
            'color'           => ['nullable', 'string', 'max:50'],
            'material'        => ['nullable', 'string', 'max:100'],
            'stock_quantity'  => ['required', 'integer', 'min:0'],
            'status'          => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
            'sold_count'      => ['nullable', 'integer', 'min:0'],

            // Validate file ảnh tải lên từ máy tính (tối đa 6 ảnh)
            'image_files'         => ['nullable', 'array', 'max:6'],
            'image_files.*'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'primary_index'       => ['nullable', 'integer', 'min:0', 'max:5'],

            // Quản lý ảnh cũ khi edit
            'kept_image_ids'      => ['nullable', 'array', 'max:6'],
            'kept_image_ids.*'    => ['integer'],
            'primary_type'        => ['nullable', 'string', 'in:existing,new'],
            'primary_id'          => ['nullable', 'integer'],

            // Hỗ trợ mảng images nếu gọi từ API
            'images'              => ['nullable', 'array', 'max:6'],
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
            'category_id.required'          => 'Danh mục là bắt buộc.',
            'category_id.exists'            => 'Danh mục không tồn tại.',
            'name.required'                 => 'Tên sản phẩm là bắt buộc.',
            'name.max'                      => 'Tên sản phẩm không được vượt quá 200 ký tự.',
            'price.required'                => 'Giá sản phẩm là bắt buộc.',
            'price.min'                     => 'Giá sản phẩm phải lớn hơn hoặc bằng 0.',
            'sale_price.lt'                 => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            'sale_start_at.after_or_equal'  => 'Ngày bắt đầu khuyến mãi phải từ ngày hôm nay trở đi (lớn hơn hoặc bằng ngày hiện tại).',
            'sale_end_at.after_or_equal'    => 'Ngày kết thúc khuyến mãi phải sau hoặc cùng ngày bắt đầu.',
            'stock_quantity.required'       => 'Số lượng tồn kho là bắt buộc.',
            'stock_quantity.min'            => 'Số lượng tồn kho không được âm.',
            'status.required'               => 'Trạng thái sản phẩm là bắt buộc.',
            'status.in'                     => 'Trạng thái sản phẩm không hợp lệ.',

            'image_files.max'            => 'Chỉ được chọn tối đa 6 ảnh cho mỗi sản phẩm.',
            'image_files.*.image'        => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'image_files.*.mimes'        => 'Ảnh phải có định dạng: jpeg, png, jpg, webp, gif.',
            'image_files.*.max'          => 'Kích thước mỗi ảnh không được vượt quá 5MB.',
            'images.max'                 => 'Chỉ được chọn tối đa 6 ảnh cho mỗi sản phẩm.',
        ];
    }
}


