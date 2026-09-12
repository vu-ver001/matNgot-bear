<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_INACTIVE = 'INACTIVE';

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'sale_price',
        'size',
        'color',
        'material',
        'stock_quantity',
        'status',
        'sold_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'sold_count' => 'integer',
    ];

    protected $appends = [
        'is_on_sale',
        'effective_price',
        'lowest_price',
        'lowest_sale_price',
        'avg_rating',
        'available_sizes',
        'available_colors',
        'has_pending_orders',
        'has_been_ordered',
    ];

    /**
     * Ràng buộc nghiệp vụ: Khi sản phẩm cha chuyển sang tạm dừng kinh doanh (INACTIVE),
     * tự động tắt toàn bộ trạng thái của các sản phẩm con (variants).
     */
    protected static function booted(): void
    {
        static::saved(function (Product $product) {
            if ($product->status === self::STATUS_INACTIVE && $product->wasChanged('status')) {
                $product->variants()->update(['status' => 'INACTIVE']);
            }
        });

        // Khi xóa mềm sản phẩm cha, tự động đổi trạng thái sang INACTIVE và xóa mềm tất cả sản phẩm con (cột deleted_at)
        static::deleting(function (Product $product) {
            $product->status = self::STATUS_INACTIVE;
            $product->saveQuietly();
            $product->variants()->update(['status' => 'INACTIVE']);
            $product->variants()->delete();
        });
    }

    /**
     * Điểm đánh giá trung bình của sản phẩm (mặc định 5.0 nếu chưa có đánh giá).
     */
    public function getAvgRatingAttribute(): float
    {
        if (isset($this->attributes['avg_rating']) && $this->attributes['avg_rating'] !== null) {
            return round((float) $this->attributes['avg_rating'], 1);
        }
        if ($this->relationLoaded('reviews')) {
            $avg = $this->reviews->where('is_hidden', false)->avg('rating');
            return $avg ? round((float) $avg, 1) : 5.0;
        }
        $avg = $this->reviews()->where('is_hidden', false)->avg('rating');
        return $avg ? round((float) $avg, 1) : 5.0;
    }

    public function getAvailableSizesAttribute(): array
    {
        if ($this->relationLoaded('variants')) {
            $variantSizes = $this->variants
                ->where('status', 'ACTIVE')
                ->pluck('size')
                ->filter(fn($s) => !empty($s))
                ->values()
                ->all();
            if (!empty($variantSizes)) {
                return array_values(array_unique($variantSizes));
            }
        } else {
            $variantSizes = $this->variants()
                ->whereNotNull('size')
                ->where('size', '!=', '')
                ->where('status', 'ACTIVE')
                ->pluck('size')
                ->toArray();
            if (!empty($variantSizes)) {
                return array_values(array_unique($variantSizes));
            }
        }

        $sizes = static::where('category_id', $this->category_id)
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->distinct()
            ->pluck('size')
            ->toArray();

        if ($this->size && !in_array($this->size, $sizes)) {
            array_unshift($sizes, $this->size);
        }

        return !empty($sizes) ? array_values(array_unique($sizes)) : [$this->size ?: 'Size chuẩn'];
    }

    /**
     * Danh sách màu sắc có sẵn của dòng sản phẩm từ cơ sở dữ liệu.
     */
    public function getAvailableColorsAttribute(): array
    {
        if ($this->relationLoaded('variants')) {
            $variantColors = $this->variants
                ->where('status', 'ACTIVE')
                ->pluck('color')
                ->filter(fn($c) => !empty($c))
                ->values()
                ->all();
            if (!empty($variantColors)) {
                return array_values(array_unique($variantColors));
            }
        } else {
            $variantColors = $this->variants()
                ->whereNotNull('color')
                ->where('color', '!=', '')
                ->where('status', 'ACTIVE')
                ->pluck('color')
                ->toArray();
            if (!empty($variantColors)) {
                return array_values(array_unique($variantColors));
            }
        }

        $colors = static::where('category_id', $this->category_id)
            ->whereNotNull('color')
            ->where('color', '!=', '')
            ->distinct()
            ->pluck('color')
            ->toArray();

        if ($this->color && !in_array($this->color, $colors)) {
            array_unshift($colors, $this->color);
        }

        return !empty($colors) ? array_values(array_unique($colors)) : [$this->color ?: 'Màu tự nhiên'];
    }

    /**
     * Kiểm tra xem sản phẩm có đang trong thời gian khuyến mãi hợp lệ hay không.
     * Khi hết hạn thời gian kết thúc hoặc chưa tới ngày bắt đầu, giá gốc sẽ tự động áp dụng trở lại.
     */
    public function getIsOnSaleAttribute(): bool
    {
        // Nếu sản phẩm có biến thể, kiểm tra theo biến thể mặc định (hoặc bất kỳ biến thể nào đang sale)
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            $defaultVar = $this->variants->firstWhere('is_default', true) ?? $this->variants->first();
            return $defaultVar ? $defaultVar->is_on_sale : false;
        }

        if (empty($this->sale_price) || $this->sale_price >= $this->price) {
            return false;
        }

        return true;
    }

    /**
     * Lấy giá bán gốc thấp nhất từ các sản phẩm con (hoặc giá của cha nếu không có biến thể).
     */
    public function getLowestPriceAttribute(): float
    {
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->where('status', 'ACTIVE')->get();
        if ($variants->isNotEmpty()) {
            $prices = $variants->pluck('price')
                ->filter(fn($p) => is_numeric($p) && (float)$p > 0)
                ->map(fn($p) => (float)$p)
                ->values()
                ->all();
            if (!empty($prices)) {
                return min($prices);
            }
        }
        return (float) $this->price;
    }

    /**
     * Lấy giá sale thấp nhất từ các sản phẩm con (nếu có giá sale hợp lệ < lowest_price).
     */
    public function getLowestSalePriceAttribute(): ?float
    {
        $lowestPrice = $this->lowest_price;
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->where('status', 'ACTIVE')->get();
        if ($variants->isNotEmpty()) {
            $salePrices = $variants->pluck('sale_price')
                ->filter(fn($sp) => is_numeric($sp) && (float)$sp > 0 && (float)$sp < $lowestPrice)
                ->map(fn($sp) => (float)$sp)
                ->values()
                ->all();
            if (!empty($salePrices)) {
                return min($salePrices);
            }
        }
        return ($this->sale_price && (float)$this->sale_price < $lowestPrice) ? (float)$this->sale_price : null;
    }

    /**
     * Lấy giá bán thực tế hiện tại (nếu đang sale thì lấy sale_price, nếu hết hạn sale thì lấy price gốc).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->is_on_sale ? (float) $this->sale_price : (float) $this->price;
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function vouchers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Voucher::class, 'voucher_products');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Kiểm tra sản phẩm có đơn hàng chưa hoàn tất (chờ xử lý, đang giao) không.
     */
    public function hasPendingOrders(): bool
    {
        return $this->orderDetails()
            ->whereHas('order', function ($q) {
                $q->whereIn('order_status', ['PENDING', 'CONFIRMED', 'PREPARING', 'SHIPPING']);
            })
            ->exists();
    }

    /**
     * Kiểm tra sản phẩm đã từng phát sinh đơn hàng trong quá khứ chưa.
     */
    public function hasBeenOrdered(): bool
    {
        return $this->orderDetails()->exists();
    }

    public function getHasPendingOrdersAttribute(): bool
    {
        if (isset($this->attributes['has_pending_orders'])) {
            return (bool) $this->attributes['has_pending_orders'];
        }
        return $this->hasPendingOrders();
    }

    public function getHasBeenOrderedAttribute(): bool
    {
        if (isset($this->attributes['has_been_ordered'])) {
            return (bool) $this->attributes['has_been_ordered'];
        }
        return $this->hasBeenOrdered();
    }

    /**
     * Đồng bộ giá bán của sản phẩm cha lấy giá bán thấp nhất của sản phẩm con (biến thể),
     * đồng thời đồng bộ lại sale_price và tổng tồn kho.
     */
    public function syncLowestPriceFromVariants(): bool
    {
        $variants = $this->variants()->get();
        if ($variants->isEmpty()) {
            return false;
        }

        $prices = $variants->pluck('price')
            ->filter(fn($p) => is_numeric($p) && (float)$p >= 0)
            ->map(fn($p) => (float)$p)
            ->values()
            ->all();

        if (empty($prices)) {
            return false;
        }

        $minPrice = min($prices);

        // Biến thể có giá thấp nhất
        $cheapestVariant = $variants->sortBy('price')->first();
        $minSalePrice = null;
        if ($cheapestVariant && !empty($cheapestVariant->sale_price) && (float)$cheapestVariant->sale_price < $minPrice) {
            $minSalePrice = (float)$cheapestVariant->sale_price;
        }

        // Nếu có biến thể nào có giá sale hợp lệ còn thấp hơn nữa
        $validSalePrices = $variants->pluck('sale_price')
            ->filter(fn($sp) => is_numeric($sp) && (float)$sp > 0 && (float)$sp < $minPrice)
            ->map(fn($sp) => (float)$sp)
            ->values()
            ->all();

        if (!empty($validSalePrices)) {
            $minSalePrice = min($validSalePrices);
        }

        $totalStock = (int) $variants->sum('stock_quantity');

        $this->price = $minPrice;
        $this->sale_price = $minSalePrice;
        $this->stock_quantity = $totalStock;

        return $this->save();
    }
}

