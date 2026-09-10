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
        'avg_rating',
        'available_sizes',
        'available_colors',
    ];

    /**
     * Điểm đánh giá trung bình của sản phẩm (mặc định 5.0 nếu chưa có đánh giá).
     */
    public function getAvgRatingAttribute(): float
    {
        if (isset($this->attributes['avg_rating']) && $this->attributes['avg_rating'] !== null) {
            return round((float) $this->attributes['avg_rating'], 1);
        }
        $avg = $this->reviews()->where('is_hidden', false)->avg('rating');
        return $avg ? round((float) $avg, 1) : 5.0;
    }

    public function getAvailableSizesAttribute(): array
    {
        $variantSizes = $this->variants()
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->where('status', 'ACTIVE')
            ->pluck('size')
            ->toArray();

        if (!empty($variantSizes)) {
            return array_values(array_unique($variantSizes));
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
        $variantColors = $this->variants()
            ->whereNotNull('color')
            ->where('color', '!=', '')
            ->where('status', 'ACTIVE')
            ->pluck('color')
            ->toArray();

        if (!empty($variantColors)) {
            return array_values(array_unique($variantColors));
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
}

