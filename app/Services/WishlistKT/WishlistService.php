<?php

namespace App\Services\WishlistKT;

use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WishlistService
{
    public function getWishlist(User $user, int $perPage = 12, string $sort = 'latest'): LengthAwarePaginator
    {
        $query = WishlistItem::query()
            ->where('user_id', $user->id)
            ->with([
                'product' => fn ($query) => $query
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews'),
                'product.images' => fn ($query) => $query
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order'),
            ]);

        if ($sort === 'price_asc' || $sort === 'price_desc') {
            $price = Product::query()
                ->selectRaw('COALESCE(sale_price, price)')
                ->whereColumn('products.id', 'wishlist_items.product_id');

            $query->orderBy($price, $sort === 'price_asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        return $query
            ->paginate($perPage)
            ->through(fn (WishlistItem $item) => $this->formatItem($item));
    }

    public function removeProduct(User $user, Product $product): bool
    {
        return WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete() > 0;
    }

    public function clearWishlist(User $user): int
    {
        return WishlistItem::query()
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatItem(WishlistItem $item): array
    {
        $product = $item->product;

        return [
            'wishlist_item_id' => $item->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'primary_image' => $product->images->first()?->image_url,
            'stock_quantity' => $product->stock_quantity,
            'status' => $product->status,
            'average_rating' => $product->reviews_avg_rating !== null
                ? round((float) $product->reviews_avg_rating, 1)
                : null,
            'reviews_count' => (int) $product->reviews_count,
            'created_at' => $item->created_at?->toISOString(),
        ];
    }
}
