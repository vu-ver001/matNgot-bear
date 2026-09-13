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
     * Lấy số lượng tồn kho của sản phẩm:
     * Nếu sản phẩm có biến thể, luôn đồng bộ theo tổng tồn kho của các biến thể.
     */
    public function getStockQuantityAttribute($value): int
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            $activeVariants = $this->variants->where('status', 'ACTIVE');
            return $activeVariants->isNotEmpty() 
                ? (int) $activeVariants->sum('stock_quantity') 
                : (int) $this->variants->sum('stock_quantity');
        }
        return (int) ($value ?? 0);
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
     * Xác định biến thể con có giá bán thực tế thấp nhất (Lowest Effective Variant).
     * Giá thực tế: nếu biến thể đang sale thì lấy sale_price (kể cả 0đ), nếu không thì lấy price.
     */
    public function getLowestEffectiveVariantAttribute(): ?ProductVariant
    {
        $variants = $this->relationLoaded('variants') 
            ? $this->variants->where('status', 'ACTIVE') 
            : $this->variants()->where('status', 'ACTIVE')->get();

        if ($variants->isEmpty()) {
            return null;
        }

        return $variants->sortBy(function (ProductVariant $v) {
            $eff = $v->is_on_sale ? (float)$v->sale_price : (float)$v->price;
            // Sắp xếp tăng dần theo giá thực tế; nếu bằng nhau, ưu tiên biến thể đang sale (0 trước 1)
            return sprintf('%014.2f_%d', $eff, $v->is_on_sale ? 0 : 1);
        })->first();
    }

    /**
     * Kiểm tra xem sản phẩm có đang trong thời gian khuyến mãi hợp lệ hay không.
     * Khi hết hạn thời gian kết thúc hoặc chưa tới ngày bắt đầu, giá gốc sẽ tự động áp dụng trở lại.
     */
    public function getIsOnSaleAttribute(): bool
    {
        $lowestVariant = $this->lowest_effective_variant;
        if ($lowestVariant) {
            return $lowestVariant->is_on_sale;
        }

        // 1. Ràng buộc bất biến: Nếu không có giá khuyến mãi hợp lệ (>= 0) hoặc giá sale >= giá gốc thì tuyệt đối KHÔNG PHẢI là đang sale
        if ($this->sale_price === null || $this->sale_price === '' || (float)$this->sale_price < 0 || (float)$this->sale_price >= (float)$this->price) {
            return false;
        }

        return true;
    }

    /**
     * Lấy giá bán gốc tương ứng của biến thể có giá thực tế thấp nhất (hoặc giá cha nếu không có biến thể).
     */
    public function getLowestPriceAttribute(): float
    {
        $lowestVariant = $this->lowest_effective_variant;
        if ($lowestVariant) {
            return (float) $lowestVariant->price;
        }
        return (float) $this->price;
    }

    /**
     * Lấy giá sale tương ứng của biến thể có giá thực tế thấp nhất nếu đang sale (kể cả 0.0), ngược lại null.
     */
    public function getLowestSalePriceAttribute(): ?float
    {
        $lowestVariant = $this->lowest_effective_variant;
        if ($lowestVariant) {
            return $lowestVariant->is_on_sale ? (float) $lowestVariant->sale_price : null;
        }
        if ($this->is_on_sale && $this->sale_price !== null && (float)$this->sale_price >= 0 && (float)$this->sale_price < (float)$this->price) {
            return (float) $this->sale_price;
        }
        return null;
    }

    /**
     * Lấy giá bán thực tế hiện tại (giá thấp nhất dù là giá gốc hay giá sale, kể cả 0đ).
     */
    public function getEffectivePriceAttribute(): float
    {
        $lowestVariant = $this->lowest_effective_variant;
        if ($lowestVariant) {
            return (float) $lowestVariant->effective_price;
        }
        if ($this->is_on_sale && $this->sale_price !== null && (float)$this->sale_price >= 0 && (float)$this->sale_price < (float)$this->price) {
            return (float) $this->sale_price;
        }
        return (float) $this->price;
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
     * Thời gian kết thúc Flash Sale của biến thể có giá thấp nhất (nếu có).
     */
    public function getFlashSaleEndAtAttribute(): ?string
    {
        $lowestVariant = $this->lowest_effective_variant;
        if ($lowestVariant && $lowestVariant->is_on_sale && $lowestVariant->sale_end_at) {
            return (string) $lowestVariant->sale_end_at;
        }
        return null;
    }

    /**
     * Số giây còn lại của Flash Sale cho biến thể có giá thấp nhất.
     */
    public function getFlashSaleRemainingSecondsAttribute(): int
    {
        $lowestVariant = $this->lowest_effective_variant;
        if ($lowestVariant && $lowestVariant->is_on_sale && $lowestVariant->sale_end_at) {
            return max(0, now()->diffInSeconds($lowestVariant->sale_end_at, false));
        }
        return 0;
    }

    /**
     * Đồng bộ giá bán của sản phẩm cha lấy theo biến thể con có giá bán thực tế thấp nhất,
     * đồng thời đồng bộ lại sale_price và tổng tồn kho.
     */
    public function syncLowestPriceFromVariants(): bool
    {
        $variants = $this->variants()->where('status', 'ACTIVE')->get();
        if ($variants->isEmpty()) {
            return false;
        }

        $lowestVariant = $variants->sortBy(function (ProductVariant $v) {
            $eff = $v->is_on_sale ? (float)$v->sale_price : (float)$v->price;
            return sprintf('%014.2f_%d', $eff, $v->is_on_sale ? 0 : 1);
        })->first();

        if (!$lowestVariant) {
            return false;
        }

        $totalStock = (int) $variants->sum('stock_quantity');

        $this->price = (float) $lowestVariant->price;
        $this->sale_price = $lowestVariant->is_on_sale ? (float) $lowestVariant->sale_price : null;
        $this->stock_quantity = $totalStock;

        return $this->saveQuietly();
    }
}

