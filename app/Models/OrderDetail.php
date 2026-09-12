<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'product_price',
        'quantity',
        'line_total',
    ];

    protected $casts = [
        'product_variant_id' => 'integer',
        'product_price' => 'decimal:2',
        'quantity' => 'integer',
        'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
    }

    /**
     * Đơn giá thực tế (ưu tiên giá snapshot product_price hoặc giá sản phẩm con).
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->product_price !== null && (float) $this->product_price > 0) {
            return (float) $this->product_price;
        }

        if (!empty($this->product_variant_id)) {
            $variant = $this->variant ?: ProductVariant::withTrashed()->find($this->product_variant_id);
            if ($variant) {
                return (float) ($variant->effective_price ?? ($variant->is_on_sale ? $variant->sale_price : $variant->price));
            }
        }

        if ($this->product) {
            return (float) ($this->product->sale_price ?? $this->product->price);
        }

        return 0;
    }

    /**
     * Tên hiển thị của phân loại (ưu tiên tên đã snapshot lúc đặt hàng).
     */
    public function getVariantDisplayAttribute(): string
    {
        if (!empty($this->variant_name)) {
            return $this->variant_name;
        }

        if ($this->variant) {
            $parts = array_filter([$this->variant->color, $this->variant->size]);
            if (!empty($parts)) {
                return implode(' · ', $parts);
            }
        }

        return 'Phân loại tiêu chuẩn';
    }
}
