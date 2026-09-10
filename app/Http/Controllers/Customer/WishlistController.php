<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Toggle thêm hoặc xóa sản phẩm khỏi bảng wishlist_items trong CSDL.
     */
    public function toggle(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'require_login' => true,
                'message' => 'Vui lòng đăng nhập để lưu sản phẩm vào danh sách yêu thích.',
            ], 401);
        }

        $userId = auth()->id();
        $productId = $request->input('product_id');

        if (!$productId || !Product::where('id', $productId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại.',
            ], 404);
        }

        $existing = WishlistItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
            $message = 'Đã bỏ sản phẩm khỏi danh sách yêu thích.';
        } else {
            WishlistItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
            $action = 'added';
            $message = 'Đã thêm sản phẩm vào danh sách yêu thích!';
        }

        $wishlistCount = WishlistItem::where('user_id', $userId)->count();

        return response()->json([
            'success' => true,
            'action' => $action,
            'message' => $message,
            'wishlist_count' => $wishlistCount,
            'product_id' => (int) $productId,
        ]);
    }

    /**
     * Lấy danh sách ID các sản phẩm đã yêu thích của người dùng đang đăng nhập.
     */
    public function userWishlistIds(): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => true,
                'ids' => [],
                'wishlist_count' => 0,
            ]);
        }

        $ids = WishlistItem::where('user_id', auth()->id())
            ->pluck('product_id')
            ->map(fn($id) => (int)$id)
            ->toArray();

        return response()->json([
            'success' => true,
            'ids' => $ids,
            'wishlist_count' => count($ids),
        ]);
    }
}
