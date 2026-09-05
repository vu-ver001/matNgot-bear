<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'voucher_type',
        'apply_scope',
        'discount_type',
        'discount_value',
        'min_order_value',
        'max_discount_value',
        'start_date',
        'end_date',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'status',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_discount_value' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'usage_limit' => 'integer',
        'usage_limit_per_user' => 'integer',
        'used_count' => 'integer',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'voucher_categories');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'voucher_products');
    }

    /**
     * Đếm số lần khách hàng này đã áp dụng voucher (kể cả đơn đã hủy).
     */
    public function countUsedByCustomer(int $userId): int
    {
        return Order::where('customer_id', $userId)
            ->where(function ($query) {
                $query->where('voucher_id', $this->id)
                      ->orWhere('shipping_voucher_id', $this->id);
            })
            ->count();
    }

    /**
     * Kiểm tra xem khách hàng này đã sử dụng hết lượt cho phép hay chưa.
     */
    public function isUsedByCustomer(int $userId): bool
    {
        $limit = max(1, (int) ($this->usage_limit_per_user ?? 1));
        return $this->countUsedByCustomer($userId) >= $limit;
    }

    /**
     * Validate voucher cho khách hàng và tính toán số tiền giảm giá.
     * Hỗ trợ phạm vi áp dụng:
     * - 'ALL': Toàn bộ cửa hàng
     * - 'CATEGORY': Theo danh mục sản phẩm
     * - 'PRODUCT': Theo sản phẩm cụ thể
     */
    public function validateForCustomer(int $userId, float $orderSubtotal = 0, float $shippingFee = 30000, $cartItems = []): array
    {
        // 1. Kiểm tra trạng thái kích hoạt
        if ($this->status !== 'ACTIVE') {
            return [
                'valid' => false,
                'message' => "Mã giảm giá [{$this->code}] hiện đang tạm ngưng áp dụng.",
            ];
        }

        // 2. Kiểm tra thời gian hiệu lực
        $now = now();
        if ($this->start_date > $now) {
            return [
                'valid' => false,
                'message' => "Mã giảm giá [{$this->code}] chưa đến thời gian áp dụng.",
            ];
        }

        if ($this->end_date < $now) {
            return [
                'valid' => false,
                'message' => "Mã giảm giá [{$this->code}] đã hết hạn sử dụng.",
            ];
        }

        // 3. Kiểm tra tổng lượt dùng hệ thống
        if ($this->used_count >= $this->usage_limit) {
            return [
                'valid' => false,
                'message' => "Mã giảm giá [{$this->code}] đã hết lượt sử dụng.",
            ];
        }

        // 4. Kiểm tra giới hạn lượt dùng của mỗi khách hàng
        $limitPerUser = max(1, (int) ($this->usage_limit_per_user ?? 1));
        $timesUsed = $this->countUsedByCustomer($userId);
        if ($timesUsed >= $limitPerUser) {
            $msg = $limitPerUser === 1
                ? "Bạn đã từng áp dụng mã [{$this->code}] này rồi. Mỗi khách hàng chỉ được sử dụng mã 1 lần duy nhất."
                : "Bạn đã sử dụng hết {$limitPerUser} lượt áp dụng cho phép của mã [{$this->code}].";
            return [
                'valid' => false,
                'message' => $msg,
            ];
        }

        // 5. Kiểm tra phạm vi áp dụng (Category / Product / All)
        $eligibleSubtotal = $orderSubtotal;

        if ($this->voucher_type === 'ORDER' && !empty($cartItems)) {
            if ($this->apply_scope === 'CATEGORY') {
                $allowedCategoryIds = $this->categories->pluck('id')->toArray();
                $eligibleSubtotal = 0;
                $hasMatchingProduct = false;

                foreach ($cartItems as $item) {
                    $itemCategoryId = $item->product?->category_id ?? ($item['category_id'] ?? null);
                    $itemPrice = $item->product?->sale_price ?? $item->product?->price ?? ($item['price'] ?? 0);
                    $itemQty = $item->quantity ?? ($item['quantity'] ?? 1);

                    if ($itemCategoryId && in_array($itemCategoryId, $allowedCategoryIds)) {
                        $hasMatchingProduct = true;
                        $eligibleSubtotal += ($itemPrice * $itemQty);
                    }
                }

                if (!$hasMatchingProduct) {
                    $catNames = $this->categories->pluck('name')->join(', ');
                    return [
                        'valid' => false,
                        'message' => "Mã giảm giá [{$this->code}] chỉ áp dụng cho các sản phẩm thuộc danh mục: {$catNames}.",
                    ];
                }
            } elseif ($this->apply_scope === 'PRODUCT') {
                $allowedProductIds = $this->products->pluck('id')->toArray();
                $eligibleSubtotal = 0;
                $hasMatchingProduct = false;

                foreach ($cartItems as $item) {
                    $itemProductId = $item->product_id ?? ($item->product?->id ?? ($item['product_id'] ?? null));
                    $itemPrice = $item->product?->sale_price ?? $item->product?->price ?? ($item['price'] ?? 0);
                    $itemQty = $item->quantity ?? ($item['quantity'] ?? 1);

                    if ($itemProductId && in_array($itemProductId, $allowedProductIds)) {
                        $hasMatchingProduct = true;
                        $eligibleSubtotal += ($itemPrice * $itemQty);
                    }
                }

                if (!$hasMatchingProduct) {
                    return [
                        'valid' => false,
                        'message' => "Mã giảm giá [{$this->code}] chỉ áp dụng cho một số sản phẩm nhất định trong chương trình khuyến mãi.",
                    ];
                }
            }
        }

        // 6. Kiểm tra điều kiện giá trị đơn hàng tối thiểu (áp dụng trên phần tiền hợp lệ)
        if ($eligibleSubtotal < (float)$this->min_order_value) {
            $minFormatted = number_format($this->min_order_value, 0, ',', '.') . 'đ';
            return [
                'valid' => false,
                'message' => "Tổng giá trị sản phẩm hợp lệ phải từ {$minFormatted} để áp dụng mã [{$this->code}].",
            ];
        }

        // 7. Tính toán số tiền được giảm
        $discountAmount = 0;

        if ($this->voucher_type === 'ORDER') {
            // Giảm trực tiếp vào eligibleSubtotal
            if ($this->discount_type === 'PERCENTAGE') {
                $discountAmount = ($eligibleSubtotal * (float)$this->discount_value) / 100;
                if ($this->max_discount_value && (float)$this->max_discount_value > 0) {
                    $discountAmount = min($discountAmount, (float)$this->max_discount_value);
                }
            } else {
                // FIXED
                $discountAmount = min($eligibleSubtotal, (float)$this->discount_value);
            }
        } elseif ($this->voucher_type === 'SHIPPING') {
            // Mã giảm phí vận chuyển
            if ($this->discount_type === 'PERCENTAGE') {
                $discountAmount = ($shippingFee * (float)$this->discount_value) / 100;
                if ($this->max_discount_value && (float)$this->max_discount_value > 0) {
                    $discountAmount = min($discountAmount, (float)$this->max_discount_value);
                }
            } else {
                // FIXED
                $discountAmount = min($shippingFee, (float)$this->discount_value);
            }
        }

        return [
            'valid' => true,
            'voucher' => $this,
            'voucher_type' => $this->voucher_type,
            'apply_scope' => $this->apply_scope,
            'eligible_subtotal' => $eligibleSubtotal,
            'discount_amount' => $discountAmount,
            'discount_formatted' => number_format($discountAmount, 0, ',', '.') . 'đ',
            'message' => "Áp dụng thành công mã [{$this->code}]! Giảm " . number_format($discountAmount, 0, ',', '.') . "đ.",
        ];
    }

    /**
     * Lấy trạng thái thực tế của voucher.
     * Trả về:
     * - 'DISABLED': Vô hiệu hóa (do Admin tắt)
     * - 'EXPIRED': Đã hết hạn (quá ngày kết thúc)
     * - 'OUT_OF_STOCK': Hết lượt dùng (used_count >= usage_limit)
     * - 'UPCOMING': Sắp diễn ra (chưa tới ngày bắt đầu)
     * - 'RUNNING': Đang diễn ra (thỏa mãn tất cả điều kiện)
     */
    public function getRealStatusAttribute(): string
    {
        if ($this->status !== 'ACTIVE') {
            return 'DISABLED';
        }

        $now = now();
        if ($this->end_date && $this->end_date->isPast()) {
            return 'EXPIRED';
        }

        if ($this->used_count >= $this->usage_limit) {
            return 'OUT_OF_STOCK';
        }

        if ($this->start_date && $this->start_date->isFuture()) {
            return 'UPCOMING';
        }

        return 'RUNNING';
    }

    /**
     * Dữ liệu nhãn badge hiển thị trạng thái thực tế.
     */
    public function getRealStatusBadgeAttribute(): array
    {
        return match ($this->real_status) {
            'RUNNING' => [
                'label' => 'Đang diễn ra',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'icon' => '🟢',
            ],
            'UPCOMING' => [
                'label' => 'Sắp diễn ra',
                'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                'dot' => 'bg-blue-500',
                'icon' => '⏳',
            ],
            'OUT_OF_STOCK' => [
                'label' => 'Hết lượt dùng',
                'bg' => 'bg-amber-50 text-amber-800 border-amber-200',
                'dot' => 'bg-amber-500',
                'icon' => '🏷️',
            ],
            'EXPIRED' => [
                'label' => 'Đã hết hạn',
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                'dot' => 'bg-rose-500',
                'icon' => '⌛',
            ],
            default => [
                'label' => 'Vô hiệu hóa',
                'bg' => 'bg-gray-100 text-gray-600 border-gray-200',
                'dot' => 'bg-gray-400',
                'icon' => '⏸️',
            ],
        };
    }

    /**
     * Dữ liệu nhãn hiển thị phạm vi áp dụng.
     */
    public function getApplyScopeBadgeAttribute(): array
    {
        if ($this->voucher_type === 'SHIPPING') {
            return [
                'label' => 'Phí vận chuyển',
                'icon' => '🚚',
                'bg' => 'bg-teal-50 text-teal-700 border-teal-200',
            ];
        }

        return match ($this->apply_scope) {
            'CATEGORY' => [
                'label' => $this->categories->count() > 0 ? $this->categories->count() . ' danh mục' : 'Theo danh mục',
                'icon' => '📂',
                'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            ],
            'PRODUCT' => [
                'label' => $this->products->count() > 0 ? $this->products->count() . ' sản phẩm' : 'Theo sản phẩm',
                'icon' => '🧸',
                'bg' => 'bg-orange-50 text-orange-700 border-orange-200',
            ],
            default => [
                'label' => 'Toàn bộ shop',
                'icon' => '🌐',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
        };
    }
}
