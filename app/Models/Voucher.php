<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'voucher_id');
    }

    public function shippingOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_voucher_id');
    }

    /**
     * Tổng số đơn hàng đã từng áp dụng voucher này (bao gồm cả đơn giảm giá & freeship).
     */
    public function getTotalOrdersCount(): int
    {
        if (isset($this->orders_count) && isset($this->shipping_orders_count)) {
            return (int) $this->orders_count + (int) $this->shipping_orders_count;
        }

        return Order::where('voucher_id', $this->id)
            ->orWhere('shipping_voucher_id', $this->id)
            ->count();
    }

    /**
     * Số đơn hàng đang trong quá trình xử lý (chưa hoàn tất hoặc chưa hủy).
     */
    public function getActiveOrdersCount(): int
    {
        if (isset($this->active_orders_count) && isset($this->active_shipping_orders_count)) {
            return (int) $this->active_orders_count + (int) $this->active_shipping_orders_count;
        }

        return Order::where(function ($query) {
                $query->where('voucher_id', $this->id)
                      ->orWhere('shipping_voucher_id', $this->id);
            })
            ->whereNotIn('order_status', ['COMPLETED', 'CANCELLED'])
            ->count();
    }

    /**
     * Đếm số lần khách hàng này đã áp dụng voucher (không tính các đơn đã hủy).
     */
    public function countUsedByCustomer(int $userId): int
    {
        return Order::where('customer_id', $userId)
            ->where(function ($q) {
                $q->whereNull('order_status')
                  ->orWhere('order_status', '!=', 'CANCELLED');
            })
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
        if ($this->usage_limit !== null && (int)$this->usage_limit > 0 && $this->used_count >= (int)$this->usage_limit) {
            return [
                'valid' => false,
                'message' => "Mã giảm giá [{$this->code}] đã hết lượt sử dụng.",
            ];
        }

        // 4. Kiểm tra giới hạn lượt dùng của mỗi khách hàng
        $limitPerUser = max(1, (int) ($this->usage_limit_per_user ?? 1));
        $timesUsed = $this->countUsedByCustomer($userId);
        if ($timesUsed >= $limitPerUser) {
            return [
                'valid' => false,
                'message' => 'Bạn đã hết lượt dùng',
            ];
        }

        // 5. Kiểm tra phạm vi áp dụng (Category / Product / All)
        $eligibleSubtotal = 0;

        if (!empty($cartItems)) {
            if ($this->apply_scope === 'CATEGORY') {
                $allowedCategoryIds = $this->categories->pluck('id')->toArray();
                $hasMatchingProduct = false;

                foreach ($cartItems as $item) {
                    $details = $this->resolveItemPriceAndDetails($item);
                    if ($details['category_id'] && in_array($details['category_id'], $allowedCategoryIds)) {
                        $hasMatchingProduct = true;
                        $eligibleSubtotal += ($details['price'] * $details['quantity']);
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
                $hasMatchingProduct = false;

                foreach ($cartItems as $item) {
                    $details = $this->resolveItemPriceAndDetails($item);
                    if ($details['product_id'] && in_array($details['product_id'], $allowedProductIds)) {
                        $hasMatchingProduct = true;
                        $eligibleSubtotal += ($details['price'] * $details['quantity']);
                    }
                }

                if (!$hasMatchingProduct) {
                    return [
                        'valid' => false,
                        'message' => "Mã giảm giá [{$this->code}] chỉ áp dụng cho một số sản phẩm nhất định trong chương trình khuyến mãi.",
                    ];
                }
            } else {
                // ALL: Áp dụng trên toàn bộ sản phẩm con trong giỏ hàng
                foreach ($cartItems as $item) {
                    $details = $this->resolveItemPriceAndDetails($item);
                    $eligibleSubtotal += ($details['price'] * $details['quantity']);
                }
            }
        } else {
            $eligibleSubtotal = $orderSubtotal;
        }

        // 6. Kiểm tra điều kiện giá trị đơn hàng tối thiểu (áp dụng trên phần tiền hợp lệ của sản phẩm con)
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

    /**
     * Tự động chuyển các voucher đã hết hạn vào thùng rác (xóa mềm).
     *
     * @return int Số lượng voucher đã được tự động xóa mềm
     */
    public static function autoTrashExpired(): int
    {
        $now = Carbon::now();

        $expiredVouchers = static::query()
            ->whereNull('deleted_at')
            ->whereNotNull('end_date')
            ->where('end_date', '<', $now)
            ->get();

        $count = 0;
        foreach ($expiredVouchers as $voucher) {
            $voucher->delete();
            $count++;
        }

        return $count;
    }

    /**
     * Trích xuất thông tin sản phẩm, phân loại con (variant) và đơn giá thực tế.
     * Đảm bảo luôn lấy đúng đơn giá của sản phẩm con (biến thể) thay vì giá của sản phẩm cha.
     */
    public function resolveItemPriceAndDetails($item): array
    {
        $productId = null;
        $variantId = null;
        $categoryId = null;
        $quantity = 1;
        $price = null;

        if (is_object($item)) {
            $productId = $item->product_id ?? ($item->product?->id ?? null);
            $variantId = $item->product_variant_id ?? ($item->variant?->id ?? null);
            $categoryId = $item->product?->category_id ?? null;
            $quantity = (int) ($item->quantity ?? 1);

            // 1. Nếu trên item đã có giá đơn vị rõ ràng (ví dụ product_price trong OrderDetail)
            if (isset($item->product_price) && is_numeric($item->product_price) && (float) $item->product_price > 0) {
                $price = (float) $item->product_price;
            }

            // 2. ƯU TIÊN SỐ 1: Nếu có sản phẩm con (variant) -> Lấy giá của sản phẩm con
            if ($price === null && $variantId) {
                $variant = !empty($item->variant) ? $item->variant : \App\Models\ProductVariant::withTrashed()->find($variantId);
                if ($variant) {
                    $price = (float) ($variant->effective_price ?? ($variant->is_on_sale ? $variant->sale_price : $variant->price));
                    if (!$productId && $variant->product_id) {
                        $productId = $variant->product_id;
                    }
                }
            }

            // 3. Nếu là CartItem có accessor effective_price
            if ($price === null && isset($item->effective_price) && (float) $item->effective_price > 0) {
                $price = (float) $item->effective_price;
            }

            // 4. Fallback: Chỉ lấy giá sản phẩm cha nếu sản phẩm đó không có sản phẩm con nào
            if ($price === null) {
                $product = !empty($item->product) ? $item->product : ($productId ? \App\Models\Product::withTrashed()->find($productId) : null);
                if ($product) {
                    $price = (float) ($product->sale_price ?? $product->price);
                    if (!$categoryId) {
                        $categoryId = $product->category_id;
                    }
                }
            }
        } elseif (is_array($item)) {
            $productId = $item['product_id'] ?? ($item['product']['id'] ?? null);
            $variantId = $item['product_variant_id'] ?? ($item['variant']['id'] ?? null);
            $categoryId = $item['category_id'] ?? ($item['product']['category_id'] ?? null);
            $quantity = (int) ($item['quantity'] ?? 1);

            if (isset($item['product_price']) && is_numeric($item['product_price']) && (float) $item['product_price'] > 0) {
                $price = (float) $item['product_price'];
            }

            if ($price === null && $variantId) {
                $variant = !empty($item['variant']) && is_object($item['variant']) 
                    ? $item['variant'] 
                    : \App\Models\ProductVariant::withTrashed()->find($variantId);
                if ($variant) {
                    $price = (float) ($variant->effective_price ?? ($variant->is_on_sale ? $variant->sale_price : $variant->price));
                    if (!$productId && $variant->product_id) {
                        $productId = $variant->product_id;
                    }
                }
            }

            if ($price === null && isset($item['effective_price']) && (float) $item['effective_price'] > 0) {
                $price = (float) $item['effective_price'];
            } elseif ($price === null && isset($item['price']) && (float) $item['price'] > 0) {
                $price = (float) $item['price'];
            }

            if ($price === null && $productId) {
                $product = \App\Models\Product::withTrashed()->find($productId);
                if ($product) {
                    $price = (float) ($product->sale_price ?? $product->price);
                    if (!$categoryId) {
                        $categoryId = $product->category_id;
                    }
                }
            }
        }

        if (!$categoryId && $productId) {
            $categoryId = \App\Models\Product::withTrashed()->find($productId)?->category_id;
        }

        return [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'category_id' => $categoryId,
            'quantity' => max(1, $quantity),
            'price' => (float) ($price ?? 0),
        ];
    }
}

