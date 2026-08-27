<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'sale_price',
        'sale_start_at',
        'sale_end_at',
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
        'sale_start_at' => 'datetime',
        'sale_end_at' => 'datetime',
        'stock_quantity' => 'integer',
        'sold_count' => 'integer',
    ];

    protected $appends = [
        'is_on_sale',
        'effective_price',
    ];

    /**
     * Kiểm tra xem sản phẩm có đang trong thời gian khuyến mãi hợp lệ hay không.
     * Khi hết hạn thời gian kết thúc hoặc chưa tới ngày bắt đầu, giá gốc sẽ tự động áp dụng trở lại.
     */
    public function getIsOnSaleAttribute(): bool
    {
        if (empty($this->sale_price) || $this->sale_price >= $this->price) {
            return false;
        }

        $now = now();

        if ($this->sale_start_at && $now->lt($this->sale_start_at)) {
            return false;
        }

        if ($this->sale_end_at && $now->gt($this->sale_end_at)) {
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

