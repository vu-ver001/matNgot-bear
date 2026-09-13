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
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        if (auth()->check() && auth()->user()->role !== 'CUSTOMER') {
            $roleLabel = auth()->user()->role === 'ADMIN' ? 'Quản trị viên (Admin)' : 'Nhân viên (Staff)';
            return redirect()->route('home')->with('error', "Tài khoản {$roleLabel} không sử dụng giỏ hàng để mua sắm. Vui lòng chuyển sang tài khoản Khách hàng.");
        }

        $userId = auth()->id();
        $cartItems = CartItem::where('user_id', $userId)
            ->with([
                'variant',
                'product' => function ($query) {
                    $query->with([
                        'category',
                        'images' => function ($q) {
                            $q->orderBy('is_primary', 'desc')->orderBy('sort_order', 'asc');
                        },
                        'variants' => function ($q) {
                            $q->where('status', 'ACTIVE')->orderBy('price', 'asc');
                        },
                    ]);
                },
            ])
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $suggestedProducts = $cartItems->isEmpty()
            ? Product::where('status', 'ACTIVE')
                ->with(['category', 'images' => function ($q) {
                    $q->orderBy('is_primary', 'desc')->orderBy('sort_order', 'asc');
                }])
                ->take(4)
                ->get()
            : collect();

        return view('customer.cart', compact('cartItems', 'suggestedProducts'));
    }

    /**
     * Add product to cart (Requires Authenticated Customer).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if (!auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'require_login' => true,
                    'message' => 'Vui lòng đăng nhập tài khoản để thêm sản phẩm vào giỏ hàng.',
                ], 401);
            }
            return redirect()->route('login')->with('info', 'Vui lòng đăng nhập tài khoản để thêm sản phẩm vào giỏ hàng.');
        }

        if (auth()->user()->role !== 'CUSTOMER') {
            $roleLabel = auth()->user()->role === 'ADMIN' ? 'Quản trị viên (Admin)' : 'Nhân viên (Staff)';
            $msg = "Tài khoản {$roleLabel} không có chức năng thêm vào giỏ hàng hay mua sản phẩm. Vui lòng sử dụng tài khoản Khách hàng để mua sắm.";
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'is_staff' => true,
                    'message' => $msg,
                ], 403);
            }
            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->status !== 'ACTIVE') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm hiện đang ngưng kinh doanh.'], 400);
            }
            return back()->with('error', 'Sản phẩm hiện đang ngưng kinh doanh.');
        }

        $variant = null;
        if (!empty($validated['product_variant_id'])) {
            $variant = \App\Models\ProductVariant::where('id', $validated['product_variant_id'])
                ->where('product_id', $product->id)
                ->first();

            if (!$variant || $variant->status !== 'ACTIVE') {
                $msg = 'Phiên bản sản phẩm được chọn không hợp lệ hoặc đã ngừng kinh doanh.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }
                return back()->with('error', $msg);
            }
        }

        $maxStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
        if ($maxStock <= 0) {
            $msg = $variant ? "Phân loại {$variant->color} · {$variant->size} hiện đang tạm hết hàng!" : 'Sản phẩm này hiện đang tạm hết hàng!';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return back()->with('error', $msg);
        }

        $userId = auth()->id();
        $existingItem = CartItem::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        $currentQuantity = $existingItem ? $existingItem->quantity : 0;
        $newQuantity = $currentQuantity + $validated['quantity'];

        if ($newQuantity > $maxStock) {
            $msg = "Số lượng trong giỏ hàng vượt quá tồn kho khả dụng ({$maxStock} sản phẩm).";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return back()->with('error', $msg);
        }

        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => $userId,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
            ],
            [
                'quantity' => $newQuantity,
            ]
        );

        $cartItem->touch();
        $cartCount = (int) CartItem::where('user_id', $userId)->count();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng!',
                'cartItem' => $cartItem->load('variant'),
                'cart_count' => $cartCount,
            ]);
        }

        return redirect()->route('customer.cart.index')->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');
    }

    /**
     * Update quantity of a cart item.
     */
    public function update(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $userId = auth()->id();
        if ($cartItem->user_id !== $userId) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $maxStock = $cartItem->effective_stock;
        if ($validated['quantity'] > $maxStock) {
            $msg = "Số lượng tồn kho tối đa là {$maxStock}.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return back()->with('error', $msg);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);
        $effectivePrice = $cartItem->effective_price;
        $lineTotal = $effectivePrice * $cartItem->quantity;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'quantity' => $validated['quantity'],
                'lineTotal' => $lineTotal,
                'lineTotalFormatted' => number_format($lineTotal, 0, ',', '.') . 'đ',
            ]);
        }

        return back()->with('success', 'Đã cập nhật số lượng!');
    }

    /**
     * Change variant of a cart item (Shopee style).
     */
    public function updateVariant(Request $request, CartItem $cartItem): JsonResponse
    {
        $userId = auth()->id();
        if ($cartItem->user_id !== $userId) {
            abort(403);
        }

        $validated = $request->validate([
            'product_variant_id' => 'required|integer|exists:product_variants,id',
        ]);

        $newVariant = \App\Models\ProductVariant::where('id', $validated['product_variant_id'])
            ->where('product_id', $cartItem->product_id)
            ->where('status', 'ACTIVE')
            ->first();

        if (! $newVariant) {
            return response()->json([
                'success' => false,
                'message' => 'Phân loại sản phẩm không hợp lệ hoặc đã ngừng kinh doanh.',
            ], 400);
        }

        if ($newVariant->stock_quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Phân loại {$newVariant->color} · {$newVariant->size} hiện đã hết hàng!",
            ], 400);
        }

        // Kiểm tra xem giỏ hàng đã có item nào khác với cùng variant này chưa
        $existingItem = CartItem::where('user_id', $userId)
            ->where('product_id', $cartItem->product_id)
            ->where('product_variant_id', $newVariant->id)
            ->where('id', '!=', $cartItem->id)
            ->first();

        if ($existingItem) {
            // Gộp số lượng
            $combinedQty = $existingItem->quantity + $cartItem->quantity;
            if ($combinedQty > $newVariant->stock_quantity) {
                $combinedQty = $newVariant->stock_quantity;
            }
            $existingItem->update(['quantity' => $combinedQty]);
            $cartItem->delete();

            $existingItem->refresh();
            $mergedPrice = (float) $existingItem->effective_price;
            $cartCount = (int) CartItem::where('user_id', $userId)->count();

            return response()->json([
                'success' => true,
                'merged' => true,
                'message' => "Đã gộp vào phân loại {$newVariant->color} · {$newVariant->size} có sẵn trong giỏ!",
                'merged_id' => $existingItem->id,
                'deleted_id' => $cartItem->id,
                'quantity' => $combinedQty,
                'unit_price' => $mergedPrice,
                'line_total' => $mergedPrice * $combinedQty,
                'stock_quantity' => $newVariant->stock_quantity,
                'variant_display' => "{$newVariant->color} · {$newVariant->size}",
                'image_url' => $existingItem->effective_image,
                'cart_count' => $cartCount,
            ]);
        }

        // Cập nhật phân loại mới cho item hiện tại
        $newQuantity = min($cartItem->quantity, $newVariant->stock_quantity);
        $cartItem->update([
            'product_variant_id' => $newVariant->id,
            'quantity' => $newQuantity,
        ]);

        $cartItem->refresh();
        $newPrice = (float) $cartItem->effective_price;
        $cartCount = (int) CartItem::where('user_id', $userId)->count();

        return response()->json([
            'success' => true,
            'merged' => false,
            'message' => "Đã đổi sang phân loại {$newVariant->color} · {$newVariant->size}!",
            'cart_item_id' => $cartItem->id,
            'product_variant_id' => $newVariant->id,
            'quantity' => $newQuantity,
            'unit_price' => $newPrice,
            'line_total' => $newPrice * $newQuantity,
            'stock_quantity' => $newVariant->stock_quantity,
            'variant_display' => "{$newVariant->color} · {$newVariant->size}",
            'image_url' => $cartItem->effective_image,
            'cart_count' => $cartCount,
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function destroy(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $userId = auth()->id();
        if ($cartItem->user_id !== $userId) {
            abort(403);
        }

        $cartItem->delete();
        $cartCount = CartItem::where('user_id', $userId)->count();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.',
                'cart_count' => $cartCount,
            ]);
        }

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    /**
     * Clear all items in cart.
     */
    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $userId = auth()->id();
        CartItem::where('user_id', $userId)->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa toàn bộ giỏ hàng.',
                'cart_count' => 0,
            ]);
        }

        return back()->with('success', 'Đã xóa toàn bộ giỏ hàng.');
    }

    /**
     * Get real-time distinct cart item count.
     */
    public function count(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $cartCount = $userId ? (int) CartItem::where('user_id', $userId)->count() : 0;

        return response()->json([
            'success' => true,
            'cart_count' => $cartCount,
        ]);
    }

    /**
     * Log immediately when a user unchecks/deselects a product in the cart.
     */
    public function logUncheck(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_item_id' => 'nullable|integer',
            'product_name' => 'nullable|string',
            'action' => 'nullable|string',
            'remaining_count' => 'nullable|integer',
        ]);

        $userId = auth()->id() ?? $this->getUserId();
        $userEmail = auth()->user()?->email ?? ('Khách vãng lai (ID: ' . $userId . ')');
        $time = now()->format('d/m/Y H:i:s');

        if (($validated['action'] ?? '') === 'uncheck_all') {
            \Illuminate\Support\Facades\Log::info("🛒 [CART LOG] Người dùng {$userEmail} đã BỎ CHỌN TẤT CẢ sản phẩm trong giỏ hàng lúc {$time}.");
        } else {
            $productName = $validated['product_name'] ?? ('Mã giỏ: #' . ($validated['cart_item_id'] ?? ''));
            $remaining = $validated['remaining_count'] ?? 0;
            \Illuminate\Support\Facades\Log::info("🛒 [CART LOG] Người dùng {$userEmail} đã BỎ TÍCH sản phẩm '{$productName}' (CartItem ID: {$validated['cart_item_id']}) lúc {$time}. Số sản phẩm còn được chọn: {$remaining}.");
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã ghi nhận log bỏ tích sản phẩm thành công.',
            'logged_at' => $time,
        ]);
    }
}
