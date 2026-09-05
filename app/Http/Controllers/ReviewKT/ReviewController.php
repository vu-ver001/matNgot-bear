<?php

namespace App\Http\Controllers\ReviewKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewKT\StoreReviewRequest;
use App\Http\Requests\ReviewKT\UpdateReviewRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\ReviewKT\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService
    ) {}

    /**
     * Trang "Đánh giá của tôi" cho khách hàng.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $reviews = Review::query()
            ->with(['product.images', 'order'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $sampleProduct = Product::query()->first();

        if ($request->expectsJson() && ! $request->hasHeader('X-Inertia')) {
            return response()->json([
                'success' => true,
                'data' => $reviews,
            ]);
        }

        return view('ReviewKT.index', compact('reviews', 'sampleProduct'));
    }

    /**
     * Lấy toàn bộ sản phẩm và trạng thái đánh giá trong đơn hàng để mở popup (Chuẩn Shopee).
     */
    public function orderReviewData(Request $request, Order $order): JsonResponse
    {
        $data = $this->reviewService->getOrderReviewData($request->user(), $order);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * API kiểm tra xem người dùng có đủ điều kiện đánh giá sản phẩm hay không.
     */
    public function checkEligibility(Request $request, Product $product): JsonResponse
    {
        $orderId = $request->filled('order_id') ? (int) $request->input('order_id') : null;
        $check = $this->reviewService->checkEligibility($request->user(), $product->id, $orderId);

        $primaryImage = $product->images->firstWhere('is_primary', true)?->image_url
            ?? $product->images->first()?->image_url
            ?? asset('images/customer/product-placeholder.png');

        return response()->json([
            'success' => true,
            'data' => [
                'eligible' => $check['eligible'],
                'message' => $check['message'],
                'order_id' => $check['order_id'],
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'primary_image' => $primaryImage,
                ],
                'existing_review' => $check['existing_review'],
            ],
        ]);
    }

    /**
     * Lưu đánh giá mới của khách hàng (hỗ trợ cả batch đơn hàng và đơn lẻ).
     */
    public function store(StoreReviewRequest $request): JsonResponse|RedirectResponse
    {
        if ($request->has('items')) {
            $order = Order::query()->findOrFail($request->input('order_id'));
            $reviews = $this->reviewService->createReviewsForOrder($request->user(), $order, $request->input('items'));

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cảm ơn bạn đã đánh giá đơn hàng!',
                    'data' => $reviews,
                ], 201);
            }

            return back()->with('success', 'Cảm ơn bạn đã đánh giá đơn hàng!');
        }

        $review = $this->reviewService->createReview($request->user(), $request->validated());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn bạn đã đánh giá sản phẩm!',
                'data' => $review->load(['product', 'order']),
            ], 201);
        }

        return back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }

    /**
     * Cập nhật đánh giá của chính mình.
     */
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse|RedirectResponse
    {
        $updated = $this->reviewService->updateReview($request->user(), $review, $request->validated());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật đánh giá thành công.',
                'data' => $updated,
            ]);
        }

        return back()->with('success', 'Đã cập nhật đánh giá thành công.');
    }

    /**
     * Xóa đánh giá của chính mình.
     */
    public function destroy(Request $request, Review $review): JsonResponse|RedirectResponse
    {
        $this->reviewService->deleteReview($request->user(), $review);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa đánh giá thành công.',
            ]);
        }

        return back()->with('success', 'Đã xóa đánh giá thành công.');
    }
}
