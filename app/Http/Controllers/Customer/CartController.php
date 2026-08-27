<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $result = DB::transaction(function () use ($request, $product): array {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            if ($product->status !== Product::STATUS_ACTIVE) {
                return ['error' => 'Sản phẩm hiện không còn được bán.'];
            }

            if ($product->stock_quantity <= 0) {
                return ['error' => 'Sản phẩm hiện đã hết hàng.'];
            }

            $cartItem = CartItem::query()
                ->where('user_id', $request->user()->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $newQuantity = ($cartItem?->quantity ?? 0) + 1;

            if ($newQuantity > $product->stock_quantity) {
                return ['error' => 'Số lượng trong giỏ đã đạt mức tồn kho hiện tại.'];
            }

            if ($cartItem) {
                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                $cartItem = CartItem::query()->create([
                    'user_id' => $request->user()->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                ]);
            }

            return ['cart_item' => $cartItem->fresh()];
        });

        if (isset($result['error'])) {
            if (! $request->expectsJson()) {
                return back()->with('error', $result['error']);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'],
                'errors' => [],
            ], 422);
        }

        /** @var CartItem $cartItem */
        $cartItem = $result['cart_item'];
        $message = 'Đã thêm sản phẩm vào giỏ hàng.';

        if (! $request->expectsJson()) {
            return back()->with('success', $message);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'cart_item_id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
            ],
        ]);
    }
}
