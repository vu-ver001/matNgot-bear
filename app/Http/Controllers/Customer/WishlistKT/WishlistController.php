<?php

namespace App\Http\Controllers\Customer\WishlistKT;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WishlistKT\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Chức năng: Quản lý danh sách sản phẩm yêu thích của khách hàng (WishlistKT)
class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlistService) {}

    /**
     * [Giao diện / API] Xem danh sách sản phẩm yêu thích (hỗ trợ phân trang và sắp xếp).
     */
    public function index(Request $request): JsonResponse|View
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'in:latest,price_asc,price_desc'],
        ]);

        // Lấy danh sách sản phẩm yêu thích qua service
        $wishlist = $this->wishlistService->getWishlist(
            $request->user(),
            $validated['per_page'] ?? 12,
            $validated['sort'] ?? 'latest',
        );

        // Nếu là yêu cầu web HTML: trả về view tab tài khoản hoặc trang wishlist độc lập
        if (! $request->expectsJson()) {
            if ($request->query('view') === 'account') {
                return view('customer.wishlistKT.account', compact('wishlist'));
            }

            return view('customer.wishlistKT.index', compact('wishlist'));
        }

        // Nếu là yêu cầu AJAX JSON: trả về danh sách items kèm phân trang
        return response()->json([
            'success' => true,
            'message' => $wishlist->isEmpty()
                ? 'Danh sách yêu thích đang trống.'
                : 'Lấy danh sách yêu thích thành công.',
            'data' => [
                'items' => $wishlist->items(),
                'pagination' => [
                    'current_page' => $wishlist->currentPage(),
                    'last_page' => $wishlist->lastPage(),
                    'per_page' => $wishlist->perPage(),
                    'total' => $wishlist->total(),
                ],
            ],
        ]);
    }

    /**
     * Xóa một sản phẩm khỏi danh sách yêu thích.
     */
    public function destroy(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $removed = $this->wishlistService->removeProduct($request->user(), $product);

        if (! $removed) {
            if (! $request->expectsJson()) {
                return back()->with('error', 'Không tìm thấy sản phẩm trong danh sách yêu thích.');
            }

            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm trong danh sách yêu thích.',
                'errors' => [],
            ], 404);
        }

        if (! $request->expectsJson()) {
            return back()->with('success', 'Đã xóa sản phẩm khỏi danh sách yêu thích.');
        }

        // Lấy số lượng sản phẩm yêu thích còn lại để cập nhật badge trái tim trên header
        $remainingCount = \App\Models\WishlistItem::where('user_id', $request->user()->id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi danh sách yêu thích.',
            'wishlist_count' => $remainingCount,
            'data' => [
                'product_id' => $product->id,
                'wishlist_count' => $remainingCount,
            ],
        ]);
    }

    /**
     * Xóa toàn bộ sản phẩm khỏi danh sách yêu thích.
     */
    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $removedCount = $this->wishlistService->clearWishlist($request->user());

        if (! $request->expectsJson()) {
            return back()->with('success', 'Đã xóa tất cả sản phẩm khỏi danh sách yêu thích.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa tất cả sản phẩm khỏi danh sách yêu thích.',
            'wishlist_count' => 0,
            'data' => [
                'removed_count' => $removedCount,
                'wishlist_count' => 0,
            ],
        ]);
    }
}
