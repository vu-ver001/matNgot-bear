<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'size',
        'color',
        'price',
        'sale_price',
        'sale_start_at',
        'sale_end_at',
        'stock_quantity',
        'image_url',
        'is_default',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'sale_start_at' => 'datetime',
        'sale_end_at' => 'datetime',
        'stock_quantity' => 'integer',
        'is_default' => 'boolean',
    ];

    protected $appends = [
        'effective_price',
        'is_in_stock',
        'is_on_sale',
        'is_sale_upcoming',
        'discount_percent',
        'sale_remaining_seconds',
    ];

    /**
     * Sản phẩm cha sở hữu biến thể này.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Kiểm tra xem biến thể có đang trong thời gian sale hợp lệ hay không.
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
     * Kiểm tra xem biến thể có khuyến mãi sắp diễn ra trong tương lai hay không.
     */
    public function getIsSaleUpcomingAttribute(): bool
    {
        if (empty($this->sale_price) || $this->sale_price >= $this->price) {
            return false;
        }

        $now = now();
        return $this->sale_start_at && $now->lt($this->sale_start_at);
    }

    /**
     * Tính % tiết kiệm: round(((price - sale_price) / price) * 100).
     */
    public function getDiscountPercentAttribute(): int
    {
        if ($this->price > 0 && $this->sale_price && $this->sale_price < $this->price) {
            return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    /**
     * Số giây còn lại đến khi kết thúc khuyến mãi (dành cho bộ đếm countdown).
     */
    public function getSaleRemainingSecondsAttribute(): int
    {
        if ($this->is_on_sale && $this->sale_end_at) {
            return max(0, now()->diffInSeconds($this->sale_end_at, false));
        }
        return 0;
    }

    /**
     * Giá bán thực tế của biến thể (có khuyến mãi hay không).
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->is_on_sale) {
            return (float) $this->sale_price;
        }
        return (float) $this->price;
    }

    /**
     * Kiểm tra biến thể còn hàng trong kho hay không.
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }
}
