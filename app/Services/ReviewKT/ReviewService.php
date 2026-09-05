<?php

namespace App\Services\ReviewKT;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    /**
     * Kiểm tra tính hợp lệ của khách hàng đối với việc đánh giá sản phẩm:
     * - Khách hàng phải mua sản phẩm trong đơn hàng đã Giao thành công (COMPLETED).
     * - Trong 1 đơn có nhiều sản phẩm khác nhau thì khách hàng được đánh giá riêng từng sản phẩm.
     * - Nếu 1 sản phẩm mua ở 2 đơn hàng khác nhau thì được đánh giá 2 lần (mỗi đơn 1 lần, chuẩn luồng Shopee).
     *
     * @return array{eligible: bool, message: string, order_id: ?int, existing_review: ?Review}
     */
    public function checkEligibility(User $user, int $productId, ?int $orderId = null): array
    {
        $product = Product::query()->find($productId);

        if (! $product) {
            return [
                'eligible' => false,
                'message' => 'Sản phẩm không tồn tại.',
                'order_id' => null,
                'existing_review' => null,
            ];
        }

        // 1. Nếu mở từ một đơn hàng cụ thể
        if ($orderId) {
            $order = Order::query()
                ->where('id', $orderId)
                ->where('customer_id', $user->id)
                ->first();

            if (! $order) {
                return [
                    'eligible' => false,
                    'message' => 'Không tìm thấy đơn hàng của bạn.',
                    'order_id' => null,
                    'existing_review' => null,
                ];
            }

            if ($order->order_status !== 'COMPLETED') {
                return [
                    'eligible' => false,
                    'message' => 'Đơn hàng chưa hoàn thành. Bạn chỉ có thể đánh giá khi đơn hàng đã giao thành công.',
                    'order_id' => null,
                    'existing_review' => null,
                ];
            }

            $hasProduct = $order->details()->where('product_id', $productId)->exists();
            if (! $hasProduct) {
                return [
                    'eligible' => false,
                    'message' => 'Sản phẩm này không nằm trong đơn hàng được chọn.',
                    'order_id' => null,
                    'existing_review' => null,
                ];
            }

            $existingReview = Review::query()
                ->where('user_id', $user->id)
                ->where('product_id', $productId)
                ->where('order_id', $orderId)
                ->first();

            if ($existingReview) {
                return [
                    'eligible' => false,
                    'message' => 'Bạn đã đánh giá sản phẩm này cho đơn hàng này rồi.',
                    'order_id' => $orderId,
                    'existing_review' => $existingReview,
                ];
            }

            return [
                'eligible' => true,
                'message' => 'Đủ điều kiện đánh giá sản phẩm.',
                'order_id' => $orderId,
                'existing_review' => null,
            ];
        }

        // 2. Nếu không chỉ định order_id: Tìm đơn hàng COMPLETED có chứa sản phẩm mà CHƯA ĐƯỢC REVIEW
        $unreviewedCompletedOrder = Order::query()
            ->where('customer_id', $user->id)
            ->where('order_status', 'COMPLETED')
            ->whereHas('details', fn ($q) => $q->where('product_id', $productId))
            ->whereDoesntHave('reviews', fn ($q) => $q->where('product_id', $productId)->where('user_id', $user->id))
            ->latest('completed_at')
            ->first();

        if ($unreviewedCompletedOrder) {
            return [
                'eligible' => true,
                'message' => 'Đủ điều kiện đánh giá sản phẩm.',
                'order_id' => $unreviewedCompletedOrder->id,
                'existing_review' => null,
            ];
        }

        // Kiểm tra xem đã từng mua và đã đánh giá hết các đơn hay chưa
        $hasAnyCompleted = Order::query()
            ->where('customer_id', $user->id)
            ->where('order_status', 'COMPLETED')
            ->whereHas('details', fn ($q) => $q->where('product_id', $productId))
            ->exists();

        if ($hasAnyCompleted) {
            return [
                'eligible' => false,
                'message' => 'Bạn đã đánh giá sản phẩm này cho tất cả các đơn hàng đã mua.',
                'order_id' => null,
                'existing_review' => Review::query()
                    ->where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->latest()
                    ->first(),
            ];
        }

        // Kiểm tra xem có đơn hàng nào chứa sản phẩm nhưng chưa hoàn thành không
        $hasPendingOrder = Order::query()
            ->where('customer_id', $user->id)
            ->where('order_status', '!=', 'COMPLETED')
            ->whereHas('details', fn ($q) => $q->where('product_id', $productId))
            ->exists();

        if ($hasPendingOrder) {
            return [
                'eligible' => false,
                'message' => 'Đơn hàng của bạn chưa hoàn thành. Bạn chỉ có thể đánh giá khi đơn hàng đã giao thành công.',
                'order_id' => null,
                'existing_review' => null,
            ];
        }

        return [
            'eligible' => false,
            'message' => 'Bạn chưa mua sản phẩm này hoặc chưa có đơn hàng nào giao thành công.',
            'order_id' => null,
            'existing_review' => null,
        ];
    }

    /**
     * Tạo đánh giá mới cho sản phẩm theo đơn hàng.
     *
     * @param  array{product_id: int, rating: int, comment: string, order_id?: ?int}  $data
     *
     * @throws ValidationException
     */
    public function createReview(User $user, array $data): Review
    {
        $productId = (int) $data['product_id'];
        $orderId = isset($data['order_id']) && $data['order_id'] ? (int) $data['order_id'] : null;

        $check = $this->checkEligibility($user, $productId, $orderId);

        if (! $check['eligible']) {
            throw ValidationException::withMessages([
                'product_id' => $check['message'],
            ]);
        }

        return Review::query()->create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'order_id' => $check['order_id'],
            'rating' => (int) $data['rating'],
            'comment' => trim((string) $data['comment']),
            'is_hidden' => false,
            'is_edited' => false,
        ]);
    }

    /**
     * Cập nhật đánh giá của chính mình.
     * Quy tắc Shopee: Mỗi đánh giá chỉ được chỉnh sửa 1 lần duy nhất!
     *
     * @param  array{rating: int, comment: string}  $data
     *
     * @throws ValidationException
     */
    public function updateReview(User $user, Review $review, array $data): Review
    {
        if ($review->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'review' => 'Bạn không có quyền chỉnh sửa đánh giá này.',
            ]);
        }

        if ($review->is_edited) {
            throw ValidationException::withMessages([
                'review' => 'Bạn chỉ được chỉnh sửa đánh giá 1 lần duy nhất.',
            ]);
        }

        $review->update([
            'rating' => (int) $data['rating'],
            'comment' => trim((string) $data['comment']),
            'is_edited' => true,
        ]);

        return $review;
    }

    /**
     * Xóa đánh giá của chính mình.
     *
     * @throws ValidationException
     */
    public function deleteReview(User $user, Review $review): bool
    {
        if ($review->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'review' => 'Bạn không có quyền xóa đánh giá này.',
            ]);
        }

        return (bool) $review->delete();
    }

    /**
     * Lấy dữ liệu sản phẩm và đánh giá hiện tại của đơn hàng để hiển thị trong popup.
     *
     * @throws ValidationException
     */
    public function getOrderReviewData(User $user, Order $order): array
    {
        if ($order->customer_id !== $user->id) {
            throw ValidationException::withMessages([
                'order' => 'Không tìm thấy đơn hàng của bạn.',
            ]);
        }

        if ($order->order_status !== 'COMPLETED') {
            throw ValidationException::withMessages([
                'order' => 'Đơn hàng chưa hoàn thành. Bạn chỉ có thể đánh giá khi đơn hàng đã giao thành công.',
            ]);
        }

        $order->load(['details.product.images', 'reviews']);

        $items = $order->details->map(function ($detail) use ($order, $user) {
            $product = $detail->product;
            $primaryImage = $product?->images?->firstWhere('is_primary', true)?->image_url
                ?? $product?->images?->first()?->image_url
                ?? asset('images/customer/product-placeholder.png');

            $review = $order->reviews
                ->where('user_id', $user->id)
                ->where('product_id', $detail->product_id)
                ->first();

            return [
                'product_id' => $detail->product_id,
                'product_name' => $detail->product_name,
                'product_image' => $primaryImage,
                'price' => (float) $detail->product_price,
                'quantity' => (int) $detail->quantity,
                'review' => $review ? [
                    'id' => $review->id,
                    'rating' => (int) $review->rating,
                    'comment' => $review->comment,
                    'is_edited' => (bool) $review->is_edited,
                ] : null,
            ];
        })->values()->all();

        return [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'order_status' => $order->order_status,
            'completed_at' => $order->completed_at?->format('d/m/Y H:i'),
            'items' => $items,
        ];
    }

    /**
     * Lưu hoặc cập nhật đánh giá hàng loạt cho các sản phẩm trong cùng 1 đơn hàng (Chuẩn Shopee).
     *
     * @param  array<int, array{product_id: int, rating: int, comment: string, review_id?: ?int}>  $items
     * @return array<int, Review>
     *
     * @throws ValidationException
     */
    public function createReviewsForOrder(User $user, Order $order, array $items): array
    {
        if ($order->customer_id !== $user->id) {
            throw ValidationException::withMessages([
                'order' => 'Không tìm thấy đơn hàng của bạn.',
            ]);
        }

        if ($order->order_status !== 'COMPLETED') {
            throw ValidationException::withMessages([
                'order' => 'Đơn hàng chưa hoàn thành. Bạn chỉ có thể đánh giá khi đơn hàng đã giao thành công.',
            ]);
        }

        $orderProductIds = $order->details()->pluck('product_id')->all();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($user, $order, $items, $orderProductIds) {
            $savedReviews = [];

            foreach ($items as $itemData) {
                $productId = (int) $itemData['product_id'];

                if (! in_array($productId, $orderProductIds, true)) {
                    continue;
                }

                $existingReview = Review::query()
                    ->where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->where('order_id', $order->id)
                    ->first();

                if ($existingReview) {
                    if (! $existingReview->is_edited) {
                        $existingReview = $this->updateReview($user, $existingReview, [
                            'rating' => (int) $itemData['rating'],
                            'comment' => trim((string) $itemData['comment']),
                        ]);
                    }
                    $savedReviews[] = $existingReview;
                } else {
                    $newReview = Review::query()->create([
                        'user_id' => $user->id,
                        'product_id' => $productId,
                        'order_id' => $order->id,
                        'rating' => (int) $itemData['rating'],
                        'comment' => trim((string) $itemData['comment']),
                        'is_hidden' => false,
                        'is_edited' => false,
                    ]);
                    $savedReviews[] = $newReview;
                }
            }

            return $savedReviews;
        });
    }
}
