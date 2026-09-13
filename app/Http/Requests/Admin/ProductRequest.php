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
        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;

        return [
            'category_id'     => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('is_active', true)->whereNull('deleted_at')),
            ],
            'name'            => [
                'required',
                'string',
                'min:5',
                'max:120',
                Rule::unique('products', 'name')->ignore($productId)->whereNull('deleted_at'),
            ],
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
            'variants.*.image_url'     => ['nullable', 'string'],
            'variants.*.status'        => ['nullable', Rule::in(['ACTIVE', 'INACTIVE'])],

            // File ảnh riêng của từng biến thể (nếu tải lên từ máy tính)
            'variant_images'           => ['nullable', 'array'],
            'variant_images.*'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],

            // Validate file ảnh tải lên từ máy tính cho Bộ ảnh sản phẩm chính (tối đa 9 ảnh)
            'image_files'         => ['nullable', 'array', 'max:9'],
            'image_files.*'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'primary_index'       => ['nullable', 'integer', 'min:0', 'max:8'],

            // Quản lý ảnh cũ khi edit (Bộ ảnh sản phẩm chính)
            'kept_image_ids'      => ['nullable', 'array', 'max:9'],
            'kept_image_ids.*'    => ['integer'],
            'primary_type'        => ['nullable', 'string', 'in:existing,new'],
            'primary_id'          => ['nullable', 'integer'],

            // Hỗ trợ mảng images nếu gọi từ API
            'images'              => ['nullable', 'array', 'max:9'],
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
            'name.required'                 => 'Vui lòng nhập tên sản phẩm.',
            'name.min'                      => 'Tên sản phẩm quá ngắn, vui lòng nhập tối thiểu 5 ký tự.',
            'name.max'                      => 'Tên sản phẩm không được vượt quá 120 ký tự (chuẩn sàn TMĐT Shopee/TikTok Shop).',
            'name.unique'                   => 'Tên sản phẩm gấu bông này đã tồn tại trong hệ thống, vui lòng đặt tên khác.',
            'category_id.required'          => 'Vui lòng chọn danh mục sản phẩm.',
            'category_id.exists'            => 'Danh mục sản phẩm được chọn không tồn tại hoặc đang tạm ẩn kinh doanh.',
            'status.required'               => 'Trạng thái sản phẩm là bắt buộc.',
            'status.in'                     => 'Trạng thái sản phẩm không hợp lệ.',

            'variants.*.size.required_with' => 'Vui lòng nhập kích thước cho từng sản phẩm con.',
            'variants.*.size.regex'         => 'Kích thước bắt buộc phải đúng dạng số kèm đơn vị "cm" viết liền (ví dụ: 45cm), không có khoảng trắng.',

            'variants.*.color.required_with'=> 'Vui lòng nhập màu sắc cho từng sản phẩm con.',
            'variants.*.price.required_with'   => 'Vui lòng nhập giá gốc cho từng sản phẩm con.',
            'variants.*.price.numeric'         => 'Giá sản phẩm con phải là một số hợp lệ.',
            'variants.*.price.min'             => 'Giá sản phẩm con không được âm.',
            'variants.*.stock_quantity.required_with' => 'Vui lòng nhập số lượng tồn kho cho từng sản phẩm con.',
            'variants.*.stock_quantity.integer'=> 'Số lượng tồn kho sản phẩm con phải là số nguyên.',
            'variants.*.stock_quantity.min'    => 'Số lượng tồn kho sản phẩm con không được âm.',

            'image_files.max'            => 'Bộ ảnh sản phẩm chính chỉ được chọn tối đa 9 ảnh.',
            'image_files.*.image'        => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'image_files.*.mimes'        => 'Ảnh phải có định dạng: jpeg, png, jpg, webp, gif.',
            'image_files.*.max'          => 'Kích thước mỗi ảnh không được vượt quá 5MB.',
            'images.max'                 => 'Bộ ảnh sản phẩm chính chỉ được chọn tối đa 9 ảnh.',
        ];
    }

    /**
     * Cấu hình các ràng buộc kiểm tra nghiệp vụ bổ sung (After Validation)
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $isCreate = $this->isMethod('POST');

            // 1. Ràng buộc Bộ ảnh sản phẩm chính: Bắt buộc tối thiểu 1 ảnh để làm ảnh bìa (Tối đa 9 ảnh)
            if ($isCreate) {
                $hasMainFile = $this->hasFile('image_files') && count($this->file('image_files')) > 0;
                $hasApiImages = $this->has('images') && is_array($this->input('images')) && count($this->input('images')) > 0;
                if (!$hasMainFile && !$hasApiImages) {
                    $validator->errors()->add('image_files', 'Bộ ảnh sản phẩm chính bắt buộc phải có tối thiểu 1 ảnh để làm ảnh bìa đại diện.');
                }
            } else {
                $newFilesCount = $this->hasFile('image_files') ? count($this->file('image_files')) : 0;
                $keptIds = $this->input('kept_image_ids', []);
                $imagesInput = $this->input('images', []);
                $existingCount = is_array($keptIds) ? count($keptIds) : (is_array($imagesInput) ? count($imagesInput) : 0);
                if (($newFilesCount + $existingCount) < 1) {
                    $validator->errors()->add('image_files', 'Bộ ảnh sản phẩm chính bắt buộc phải có tối thiểu 1 ảnh để làm ảnh bìa đại diện.');
                }
            }

            $variants = $this->input('variants', []);
            if (is_string($variants)) {
                $variants = json_decode($variants, true) ?: [];
            }
            // Cho phép trừ 2 phút buffer đề phòng chênh lệch thời gian mạng khi client gửi request
            $nowBuffer = now()->subMinutes(2);

            // Thu thập các nhóm màu đã có ảnh tải lên hoặc có sẵn image_url hợp lệ
            $colorHasImage = [];
            foreach ($variants as $idx => $v) {
                $cKey = mb_strtolower(trim($v['color'] ?? ''));
                $hasImg = $this->hasFile("variant_images.{$idx}") || (!empty($v['image_url']) && !str_contains($v['image_url'], 'placehold.co'));
                if ($hasImg && $cKey !== '') {
                    $colorHasImage[$cKey] = true;
                }
            }

            $reportedMissingColors = [];

            foreach ($variants as $idx => $v) {
                $num = $idx + 1;
                $size = $v['size'] ?? '';
                $color = trim($v['color'] ?? '');
                $cKey = mb_strtolower($color);
                $sizeColor = trim("{$size} {$color}");
                $label = $sizeColor ? "Phân loại #{$num} ({$sizeColor})" : "Phân loại #{$num}";

                // Ràng buộc ảnh thông minh: Mỗi nhóm màu chỉ cần ít nhất 1 ảnh (các kích thước cùng màu tự động kế thừa)
                $hasVariantImg = $this->hasFile("variant_images.{$idx}") || (!empty($v['image_url']) && !str_contains($v['image_url'], 'placehold.co'));
                $isColorCovered = ($cKey !== '' && !empty($colorHasImage[$cKey]));

                if (!$hasVariantImg && !$isColorCovered) {
                    if ($cKey !== '') {
                        if (!in_array($cKey, $reportedMissingColors, true)) {
                            $validator->errors()->add("variants.{$idx}.image", "Nhóm màu '{$color}' chưa có ảnh. Vui lòng chọn ít nhất 1 ảnh cho nhóm màu này.");
                            $reportedMissingColors[] = $cKey;
                        }
                    } else {
                        $validator->errors()->add("variants.{$idx}.image", "Vui lòng chọn ảnh cho {$label}.");
                    }
                }

                $price = isset($v['price']) && is_numeric($v['price']) ? (float) $v['price'] : null;
                $salePrice = (isset($v['sale_price']) && $v['sale_price'] !== '' && $v['sale_price'] !== null && is_numeric($v['sale_price']) && (float) $v['sale_price'] >= 0) 
                             ? (float) $v['sale_price'] 
                             : null;
                
                $startAtStr = !empty($v['sale_start_at']) ? $v['sale_start_at'] : null;
                $endAtStr = !empty($v['sale_end_at']) ? $v['sale_end_at'] : null;
                $startAt = $startAtStr ? \Carbon\Carbon::parse($startAtStr) : null;
                $endAt = $endAtStr ? \Carbon\Carbon::parse($endAtStr) : null;

                $hasSalePrice = ($salePrice !== null && $salePrice >= 0);
                $hasSaleTime = (!empty($startAtStr) || !empty($endAtStr));

                // 1. RÀNG BUỘC HAI CHIỀU GIỮA GIÁ SALE VÀ THỜI GIAN SALE:
                // Chiều 1: Nếu có giá sale -> BẮT BUỘC phải có thời gian sale (cả ngày bắt đầu và ngày kết thúc)
                if ($hasSalePrice) {
                    // a) Giá sale phải nhỏ hơn giá gốc
                    if ($price !== null && $salePrice >= $price) {
                        $validator->errors()->add("variants.{$idx}.sale_price", "{$label}: Giá khuyến mãi (" . number_format($salePrice, 0, ',', '.') . " đ) phải nhỏ hơn giá gốc (" . number_format($price, 0, ',', '.') . " đ)!");
                    }

                    // b) Bắt buộc phải chọn đủ cả 2 ngày
                    if (!$startAt || !$endAt) {
                        $validator->errors()->add("variants.{$idx}.sale_dates", "{$label}: Khi đã nhập giá khuyến mãi thì BẮT BUỘC phải chọn cả Ngày bắt đầu và Ngày kết thúc sale!");
                    }
                }

                // Chiều 2: Nếu có chọn thời gian sale -> BẮT BUỘC phải có giá khuyến mãi hợp lệ
                if ($hasSaleTime) {
                    if (!$hasSalePrice) {
                        $validator->errors()->add("variants.{$idx}.sale_price", "{$label}: Bạn đã chọn thời gian sale, BẮT BUỘC phải nhập cả Giá khuyến mãi hợp lệ (>= 0 đ)!");
                    }
                    if (!$startAt || !$endAt) {
                        $validator->errors()->add("variants.{$idx}.sale_dates", "{$label}: BẮT BUỘC phải chọn đầy đủ cả Ngày bắt đầu và Ngày kết thúc sale!");
                    }
                }

                // 2. RÀNG BUỘC TÍNH HỢP LỆ VỀ THỜI GIAN SALE:
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


