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
            'price'           => ['nullable', 'numeric', 'min:0'],
            'sale_price'      => ['nullable', 'numeric', 'min:0'],
            'sale_start_at'   => ['nullable', 'date'],
            'sale_end_at'     => ['nullable', 'date'],
            'size'            => ['nullable', 'string', 'max:50'],
            'color'           => ['nullable', 'string', 'max:50'],
            'material'        => ['nullable', 'string', 'max:100'],
            'stock_quantity'  => ['nullable', 'integer', 'min:0'],
            'status'          => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
            'sold_count'      => ['nullable', 'integer', 'min:0'],

            // Quản lý sản phẩm con (biến thể)
            'variants'                 => ['nullable', 'array'],
            'variants.*.id'            => ['nullable', 'integer'],
            'variants.*.size'          => ['required_with:variants', 'string', 'regex:/^\d+(\.\d+)?cm$/i', 'max:20'],
            'variants.*.color'         => ['required_with:variants', 'string', 'max:50'],
            'variants.*.price'         => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.sale_price'    => ['nullable', 'numeric', 'min:0'],
            'variants.*.sale_start_at' => ['nullable', 'date'],
            'variants.*.sale_end_at'   => ['nullable', 'date'],
            'variants.*.stock_quantity'=> ['required_with:variants', 'integer', 'min:0'],
            'variants.*.image_url'     => ['nullable', 'string', 'max:500'],
            'variants.*.is_default'    => ['nullable'],
            'variants.*.status'        => ['nullable', Rule::in(['ACTIVE', 'INACTIVE'])],

            // File ảnh riêng của từng biến thể (nếu tải lên từ máy tính)
            'variant_images'           => ['nullable', 'array'],
            'variant_images.*'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],

            // Validate file ảnh tải lên từ máy tính cho sản phẩm cha (tối đa 6 ảnh)
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
            'name.required'                 => 'Bé Gấu nhắc bạn: Vui lòng nhập Tên sản phẩm nha! 🐻',
            'name.max'                      => 'Tên sản phẩm không được vượt quá 200 ký tự.',
            'category_id.required'          => 'Bé Gấu nhắc bạn: Vui lòng chọn Danh mục sản phẩm nha! 🐻',
            'category_id.exists'            => 'Danh mục được chọn không tồn tại trong hệ thống.',
            'status.required'               => 'Trạng thái sản phẩm là bắt buộc.',
            'status.in'                     => 'Trạng thái sản phẩm không hợp lệ.',

            'variants.*.size.required_with' => 'Bé Gấu nhắc bạn: Vui lòng nhập Kích thước cho từng sản phẩm con nha! 🐻',
            'variants.*.size.regex'         => 'Bé Gấu nhắc bạn: Kích thước bắt buộc phải đúng dạng số kèm đơn vị "cm" viết liền (ví dụ: 45cm), không có khoảng trắng nha! 🐻',

            'variants.*.color.required_with'=> 'Bé Gấu nhắc bạn: Vui lòng nhập Màu sắc cho từng sản phẩm con nha! 🐻',
            'variants.*.price.required_with'   => 'Bé Gấu nhắc bạn: Vui lòng nhập Giá gốc cho từng sản phẩm con nha! 🐻',
            'variants.*.price.numeric'         => 'Giá sản phẩm con phải là một số hợp lệ.',
            'variants.*.price.min'             => 'Giá sản phẩm con không được âm.',
            'variants.*.stock_quantity.required_with' => 'Bé Gấu nhắc bạn: Vui lòng nhập Số lượng tồn kho cho từng sản phẩm con nha! 🐻',
            'variants.*.stock_quantity.integer'=> 'Số lượng tồn kho sản phẩm con phải là số nguyên.',
            'variants.*.stock_quantity.min'    => 'Số lượng tồn kho sản phẩm con không được âm.',

            'image_files.max'            => 'Chỉ được chọn tối đa 6 ảnh cho mỗi sản phẩm.',
            'image_files.*.image'        => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'image_files.*.mimes'        => 'Ảnh phải có định dạng: jpeg, png, jpg, webp, gif.',
            'image_files.*.max'          => 'Kích thước mỗi ảnh không được vượt quá 5MB.',
            'images.max'                 => 'Chỉ được chọn tối đa 6 ảnh cho mỗi sản phẩm.',
        ];
    }

    /**
     * Cấu hình các ràng buộc kiểm tra nghiệp vụ bổ sung (After Validation)
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $variants = $this->input('variants', []);
            if (is_string($variants)) {
                $variants = json_decode($variants, true) ?: [];
            }

            $isCreate = $this->isMethod('POST');
            // Cho phép trừ 2 phút buffer đề phòng chênh lệch thời gian mạng khi client gửi request
            $nowBuffer = now()->subMinutes(2);

            foreach ($variants as $idx => $v) {
                $num = $idx + 1;
                $size = $v['size'] ?? '';
                $color = $v['color'] ?? '';
                $sizeColor = trim("{$size} {$color}");
                $label = $sizeColor ? "Phân loại #{$num} ({$sizeColor})" : "Phân loại #{$num}";

                // Ràng buộc Cột Ảnh bắt buộc cho từng sản phẩm con
                $hasVariantImg = $this->hasFile("variant_images.{$idx}") || (!empty($v['image_url']) && !str_contains($v['image_url'], 'placehold.co'));
                if (!$hasVariantImg) {
                    $validator->errors()->add("variants.{$idx}.image", "Bé Gấu nhắc bạn: Vui lòng chọn ảnh cho {$label} nha! 🐻");
                }

                $price = isset($v['price']) && is_numeric($v['price']) ? (float) $v['price'] : null;
                $salePrice = isset($v['sale_price']) && is_numeric($v['sale_price']) && (float) $v['sale_price'] > 0 
                             ? (float) $v['sale_price'] 
                             : null;
                
                $startAtStr = !empty($v['sale_start_at']) ? $v['sale_start_at'] : null;
                $endAtStr = !empty($v['sale_end_at']) ? $v['sale_end_at'] : null;
                $startAt = $startAtStr ? \Carbon\Carbon::parse($startAtStr) : null;
                $endAt = $endAtStr ? \Carbon\Carbon::parse($endAtStr) : null;

                // 1. RÀNG BUỘC KHI CÓ GIÁ KHUYẾN MÃI:
                if ($salePrice !== null) {
                    // a) Giá sale phải nhỏ hơn giá gốc
                    if ($price !== null && $salePrice >= $price) {
                        $validator->errors()->add("variants.{$idx}.sale_price", "{$label}: Giá khuyến mãi (" . number_format($salePrice, 0, ',', '.') . " đ) phải nhỏ hơn giá gốc (" . number_format($price, 0, ',', '.') . " đ)!");
                    }

                    // b) BẮT BUỘC phải nhập cả ngày bắt đầu và ngày kết thúc
                    if (!$startAt || !$endAt) {
                        $validator->errors()->add("variants.{$idx}.sale_dates", "{$label}: Khi đã nhập giá khuyến mãi thì BẮT BUỘC phải nhập cả Ngày bắt đầu và Ngày kết thúc sale!");
                    }
                }

                // 2. RÀNG BUỘC VỀ THỜI GIAN SALE:
                if ($startAt && $endAt) {
                    // Ngày kết thúc phải sau ngày bắt đầu
                    if ($endAt->lte($startAt)) {
                        $validator->errors()->add("variants.{$idx}.sale_end_at", "{$label}: Ngày giờ kết thúc sale phải diễn ra sau ngày giờ bắt đầu!");
                    }

                    // Khi thêm mới: Ngày bắt đầu phải từ hiện tại trở đi (không được trong quá khứ)
                    if ($isCreate && $startAt->lt($nowBuffer)) {
                        $validator->errors()->add("variants.{$idx}.sale_start_at", "{$label}: Ngày bắt đầu sale phải từ thời điểm hiện tại trở đi, không được chọn thời gian trong quá khứ!");
                    }

                    // Khi chỉnh sửa:
                    if (!$isCreate) {
                        $varId = $v['id'] ?? null;
                        $origVar = $varId ? \App\Models\ProductVariant::find($varId) : null;
                        $origStartStr = ($origVar && $origVar->sale_start_at) ? $origVar->sale_start_at->format('Y-m-d\TH:i') : null;

                        // Nếu không có id (phân loại mới thêm trong lúc sửa) hoặc ngày bắt đầu đã bị thay đổi so với CSDL
                        $currentStartFormatted = $startAt->format('Y-m-d\TH:i');
                        $isStartChanged = (!$origVar || $origStartStr !== $currentStartFormatted);

                        if ($isStartChanged && $startAt->lt($nowBuffer)) {
                            $validator->errors()->add("variants.{$idx}.sale_start_at", "{$label}: Bạn đã thay đổi ngày bắt đầu sale, thời gian mới phải từ thời điểm hiện tại trở đi!");
                        }
                    }
                }
            }
        });
    }
}


