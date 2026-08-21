<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Get current user ID or fallback to first customer for direct URL testing.
     */
    protected function getUserId(): int
    {
        return auth()->id() ?? \App\Models\User::where('role', 'CUSTOMER')->first()?->id ?? 1;
    }

    /**
     * Display customer cart.
     */
    public function index(Request $request): View|JsonResponse
    {
        $userId = $this->getUserId();

        $cartItems = CartItem::where('user_id', $userId)
            ->with(['product' => function ($query) {
                $query->with(['category', 'images' => function ($q) {
                    $q->orderBy('is_primary', 'desc')->orderBy('sort_order', 'asc');
                }]);
            }])
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'items' => $cartItems,
            ]);
        }

        return view('customer.cart', compact('cartItems'));
    }

    /**
     * Add product to cart.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->status !== 'ACTIVE') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm hiện đang ngưng kinh doanh.'], 400);
            }
            return back()->with('error', 'Sản phẩm hiện đang ngưng kinh doanh.');
        }

        $userId = $this->getUserId();

        $existingItem = CartItem::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        $currentQuantity = $existingItem ? $existingItem->quantity : 0;
        $newQuantity = $currentQuantity + $validated['quantity'];

        if ($newQuantity > $product->stock_quantity) {
            $msg = "Số lượng trong giỏ hàng vượt quá tồn kho khả dụng ({$product->stock_quantity} sản phẩm).";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return back()->with('error', $msg);
        }

        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => $userId,
                'product_id' => $product->id,
            ],
            [
                'quantity' => $newQuantity,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng!',
                'cartItem' => $cartItem,
            ]);
        }

        return redirect()->route('customer.cart.index')->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');
    }

    /**
     * Update quantity of a cart item.
     */
    public function update(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $userId = $this->getUserId();
        if ($cartItem->user_id !== $userId && auth()->check()) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = $cartItem->product;

        if ($validated['quantity'] > $product->stock_quantity) {
            $msg = "Số lượng tồn kho tối đa là {$product->stock_quantity}.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return back()->with('error', $msg);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        $effectivePrice = $product->sale_price ?? $product->price;
        $lineTotal = $effectivePrice * $cartItem->quantity;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'quantity' => $cartItem->quantity,
                'lineTotal' => $lineTotal,
                'lineTotalFormatted' => number_format($lineTotal, 0, ',', '.') . 'đ',
            ]);
        }

        return back()->with('success', 'Đã cập nhật số lượng!');
    }

    /**
     * Remove item from cart.
     */
    public function destroy(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $userId = $this->getUserId();
        if ($cartItem->user_id !== $userId && auth()->check()) {
            abort(403);
        }

        $cartItem->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.',
            ]);
        }

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    /**
     * Clear all items in cart.
     */
    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $userId = $this->getUserId();
        CartItem::where('user_id', $userId)->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa toàn bộ giỏ hàng.',
            ]);
        }

        return back()->with('success', 'Đã xóa toàn bộ giỏ hàng.');
    }
}

