<?php

namespace App\Http\Controllers\Customer\WishlistKT;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WishlistKT\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlistService) {}

    public function index(Request $request): JsonResponse|View
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'in:latest,price_asc,price_desc'],
        ]);

        $wishlist = $this->wishlistService->getWishlist(
            $request->user(),
            $validated['per_page'] ?? 12,
            $validated['sort'] ?? 'latest',
        );

        if (! $request->expectsJson()) {
            return view('customer.wishlistKT.index', compact('wishlist'));
        }

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
            return redirect()
                ->route('customer.wishlist.index')
                ->with('success', 'Đã xóa sản phẩm khỏi danh sách yêu thích.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi danh sách yêu thích.',
            'data' => [
                'product_id' => $product->id,
            ],
        ]);
    }

    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $removedCount = $this->wishlistService->clearWishlist($request->user());

        if (! $request->expectsJson()) {
            return redirect()
                ->route('customer.wishlist.index')
                ->with('success', 'Đã xóa tất cả sản phẩm khỏi danh sách yêu thích.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa tất cả sản phẩm khỏi danh sách yêu thích.',
            'data' => [
                'removed_count' => $removedCount,
            ],
        ]);
    }
}
