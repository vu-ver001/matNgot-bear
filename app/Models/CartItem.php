<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'product_variant_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'product_variant_id' => 'integer',
    ];

    protected $appends = [
        'effective_price',
        'effective_stock',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * Đơn giá thực tế (ưu tiên tuyệt đối giá của sản phẩm con / biến thể nếu có).
     */
    public function getEffectivePriceAttribute(): float
    {
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
     * Tồn kho thực tế (ưu tiên tồn kho của biến thể nếu có).
     */
    public function getEffectiveStockAttribute(): int
    {
        if ($this->variant) {
            return (int) $this->variant->stock_quantity;
        }

        if ($this->product) {
            return (int) $this->product->stock_quantity;
        }

        return 0;
    }

    /**
     * Ảnh đại diện thực tế (ưu tiên ảnh của biến thể nếu có).
     */
    public function getEffectiveImageAttribute(): string
    {
        if ($this->variant && !empty($this->variant->image_url)) {
            return $this->variant->image_url;
        }

        if ($this->product) {
            $primary = $this->product->images->firstWhere('is_primary', true) ?? $this->product->images->first();
            if ($primary && !empty($primary->image_url)) {
                return $primary->image_url;
            }
        }

        return '/images/products/butterbear-chef.jpg';
    }
}
