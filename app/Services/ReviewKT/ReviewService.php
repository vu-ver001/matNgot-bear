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

            $completedDate = $order->completed_at ?? $order->updated_at;
            if ($completedDate && $completedDate->copy()->addDays(30)->isPast()) {
                return [
                    'eligible' => false,
                    'message' => 'Đã quá thời hạn 30 ngày kể từ khi giao hàng thành công. Không thể đánh giá đơn hàng này nữa.',
                    'order_id' => $orderId,
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

        $trashed = Review::onlyTrashed()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        $newImageUrls = !empty($data['images']) && is_array($data['images'])
            ? $this->handleReviewImageUploads($data['images'])
            : [];
        $existingImageUrls = !empty($data['existing_images']) && is_array($data['existing_images'])
            ? array_values(array_filter($data['existing_images'], fn ($u) => is_string($u) && !empty($u)))
            : [];
        $imageUrls = array_slice(array_merge($existingImageUrls, $newImageUrls), 0, 5);

        if ($trashed) {
            $trashed->restore();
            $trashed->update([
                'order_id' => $check['order_id'],
                'rating' => (int) $data['rating'],
                'comment' => trim((string) $data['comment']),
                'images' => !empty($imageUrls) ? $imageUrls : null,
                'is_hidden' => false,
                'is_edited' => false,
            ]);

            return $trashed;
        }

        return Review::query()->create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'order_id' => $check['order_id'],
            'rating' => (int) $data['rating'],
            'comment' => trim((string) $data['comment']),
            'images' => !empty($imageUrls) ? $imageUrls : null,
            'is_hidden' => false,
            'is_edited' => false,
        ]);
    }

    /**
     * Upload và lưu danh sách ảnh đánh giá.
     *
     * @param  array<mixed>  $files
     * @return array<string>
     */
    protected function handleReviewImageUploads(array $files): array
    {
        $urls = [];
        foreach ($files as $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                $path = $file->store('reviews', 'public');
                $urls[] = asset('storage/' . $path);
            } elseif (is_string($file) && !empty($file)) {
                $urls[] = $file;
            }
        }
        return array_slice($urls, 0, 5);
    }

    /**
     * Cập nhật đánh giá của chính mình.
     * Quy tắc Shopee: Mỗi đánh giá chỉ được chỉnh sửa 1 lần duy nhất!
     *
     * @param  array{rating: int, comment: string, images?: ?array}  $data
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

        if ($review->created_at && $review->created_at->copy()->addDays(7)->isPast()) {
            throw ValidationException::withMessages([
                'review' => 'Đã quá thời hạn 7 ngày kể từ khi gửi đánh giá. Bạn không thể chỉnh sửa đánh giá này nữa.',
            ]);
        }

        $payload = [
            'rating' => (int) $data['rating'],
            'comment' => trim((string) $data['comment']),
            'is_edited' => true,
        ];

        if (array_key_exists('images', $data) || array_key_exists('existing_images', $data)) {
            $newUrls = isset($data['images']) && is_array($data['images'])
                ? $this->handleReviewImageUploads($data['images'])
                : [];
            $existingUrls = isset($data['existing_images']) && is_array($data['existing_images'])
                ? array_values(array_filter($data['existing_images'], fn ($u) => is_string($u) && !empty($u)))
                : [];
            $allUrls = array_slice(array_merge($existingUrls, $newUrls), 0, 5);
            $payload['images'] = !empty($allUrls) ? $allUrls : null;
        }

        $review->update($payload);

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

        $completedDate = $order->completed_at ?? $order->updated_at;
        if ($completedDate && $completedDate->copy()->addDays(30)->isPast()) {
            throw ValidationException::withMessages([
                'order' => 'Đã quá thời hạn 30 ngày kể từ khi giao hàng thành công. Đơn hàng này không còn trong thời hạn đánh giá.',
            ]);
        }

        $order->load(['details.product.images', 'details.variant', 'reviews']);

        $items = $order->details->map(function ($detail) use ($order, $user) {
            $product = $detail->product;
            $productImage = self::resolveItemImageUrl($detail);

            $variantText = self::resolveVariantText($detail);

            $review = $order->reviews
                ->where('user_id', $user->id)
                ->where('product_id', $detail->product_id)
                ->first();

            return [
                'product_id' => $detail->product_id,
                'product_name' => $detail->product_name ?? $product?->name ?? 'Sản phẩm',
                'product_image' => $productImage,
                'variant_text' => $variantText,
                'variant_sku' => $detail->variant_sku ?? $detail->variant?->sku,
                'price' => (float) $detail->product_price,
                'quantity' => (int) $detail->quantity,
                'review' => $review ? [
                    'id' => $review->id,
                    'rating' => (int) $review->rating,
                    'comment' => $review->comment,
                    'images' => $review->images,
                    'is_edited' => (bool) $review->is_edited,
                    'can_be_edited' => $review->canBeEdited(),
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

        $completedDate = $order->completed_at ?? $order->updated_at;
        if ($completedDate && $completedDate->copy()->addDays(30)->isPast()) {
            throw ValidationException::withMessages([
                'order' => 'Đã quá thời hạn 30 ngày kể từ khi giao hàng thành công. Bạn không thể gửi đánh giá cho đơn hàng này nữa.',
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

                $newImageUrls = !empty($itemData['images']) && is_array($itemData['images'])
                    ? $this->handleReviewImageUploads($itemData['images'])
                    : [];

                $existingImageUrls = !empty($itemData['existing_images']) && is_array($itemData['existing_images'])
                    ? array_values(array_filter($itemData['existing_images'], fn ($u) => is_string($u) && !empty($u)))
                    : [];

                $imageUrls = array_slice(array_merge($existingImageUrls, $newImageUrls), 0, 5);

                $existingReview = Review::query()
                    ->where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->where('order_id', $order->id)
                    ->first();

                if ($existingReview) {
                    if ($existingReview->canBeEdited()) {
                        $updateData = [
                            'rating' => (int) $itemData['rating'],
                            'comment' => trim((string) $itemData['comment']),
                            'images' => !empty($imageUrls) ? $imageUrls : null,
                        ];
                        $existingReview = $this->updateReview($user, $existingReview, $updateData);
                    }
                    $savedReviews[] = $existingReview;
                } else {
                    $trashed = Review::onlyTrashed()
                        ->where('user_id', $user->id)
                        ->where('product_id', $productId)
                        ->first();

                    if ($trashed) {
                        $trashed->restore();
                        $trashed->update([
                            'order_id' => $order->id,
                            'rating' => (int) $itemData['rating'],
                            'comment' => trim((string) $itemData['comment']),
                            'images' => !empty($imageUrls) ? $imageUrls : $trashed->images,
                            'is_hidden' => false,
                            'is_edited' => false,
                        ]);
                        $savedReviews[] = $trashed;
                    } else {
                        $newReview = Review::query()->create([
                            'user_id' => $user->id,
                            'product_id' => $productId,
                            'order_id' => $order->id,
                            'rating' => (int) $itemData['rating'],
                            'comment' => trim((string) $itemData['comment']),
                            'images' => !empty($imageUrls) ? $imageUrls : null,
                            'is_hidden' => false,
                            'is_edited' => false,
                        ]);
                        $savedReviews[] = $newReview;
                    }
                }
            }

            return $savedReviews;
        });
    }

    /**
     * Lấy danh sách sản phẩm khách hàng đã mua trong các đơn hàng COMPLETED nhưng chưa viết đánh giá cho đơn đó (trong vòng 30 ngày).
     * Quy tắc chuẩn Shopee: Mỗi đơn hàng hoàn thành có các sản phẩm cần đánh giá riêng biệt, sau 30 ngày không thể đánh giá.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPendingReviewItems(User $user, string $sort = 'latest')
    {
        $query = \App\Models\OrderDetail::query()
            ->whereHas('order', function ($q) use ($user) {
                $q->where('customer_id', $user->id)
                  ->where('order_status', 'COMPLETED')
                  ->where(function ($dateQ) {
                      $dateQ->where('completed_at', '>=', now()->subDays(30))
                            ->orWhere(function ($fallbackQ) {
                                $fallbackQ->whereNull('completed_at')
                                          ->where('updated_at', '>=', now()->subDays(30));
                            });
                  });
            })
            ->whereNotExists(function ($q) use ($user) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('reviews')
                  ->whereColumn('reviews.order_id', 'order_details.order_id')
                  ->whereColumn('reviews.product_id', 'order_details.product_id')
                  ->where('reviews.user_id', $user->id)
                  ->whereNull('reviews.deleted_at');
            })
            ->with(['product.images', 'order', 'variant']);

        if ($sort === 'oldest') {
            $query->orderBy('id', 'asc');
        } else {
            $query->orderByDesc('id');
        }

        return $query->get()
            ->filter(fn ($detail) => $detail->product !== null)
            ->filter(function ($detail) {
                $cDate = $detail->order?->completed_at ?? $detail->order?->updated_at;
                return $cDate && ! $cDate->copy()->addDays(30)->isPast();
            })
            ->unique(fn ($detail) => $detail->order_id . '-' . $detail->product_id)
            ->values();
    }

    /**
     * Đếm số lượng sản phẩm chưa đánh giá.
     */
    public function getPendingReviewsCount(User $user): int
    {
        return $this->getPendingReviewItems($user)->count();
    }

    /**
     * Lấy danh sách các đánh giá của chính khách hàng (phân trang).
     */
    public function getUserReviews(User $user, int $perPage = 9, string $sort = 'latest')
    {
        $query = Review::query()
            ->with(['product.images', 'order.details.variant'])
            ->where('user_id', $user->id);

        match ($sort) {
            'oldest' => $query->oldest(),
            'rating_desc' => $query->orderByDesc('rating')->latest(),
            'rating_asc' => $query->orderBy('rating')->latest(),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    /**
     * Đếm số lượng đánh giá khách hàng đã viết.
     */
    public function getUserReviewsCount(User $user): int
    {
        return Review::query()
            ->where('user_id', $user->id)
            ->count();
    }

    /**
     * Xác định ảnh hiển thị cho sản phẩm / phân loại con chuẩn theo trang Đơn hàng:
     * - Nếu sản phẩm có phân loại biến thể: Ưu tiên lấy ảnh biến thể nếu file thực sự tồn tại.
     *   Nếu biến thể không có ảnh riêng hoặc ảnh lỗi, fallback về placeholder Bear như bên Đơn hàng
     *   (không lấy ảnh của phân loại khác tránh gây hiểu nhầm).
     * - Nếu sản phẩm thường không có phân loại: Lấy ảnh chính của sản phẩm đó.
     */
    public static function resolveItemImageUrl($detail): string
    {
        $placeholder = 'https://placehold.co/120x120/fef3c7/78350f?text=Bear';
        if (! $detail) {
            return $placeholder;
        }

        $product = $detail->product ?? null;
        $hasVariant = ! empty($detail->product_variant_id)
            || ! empty($detail->variant_sku)
            || ! empty($detail->variant_name)
            || ! empty($detail->variant_color)
            || ! empty($detail->variant_size);

        if ($hasVariant) {
            $variantUrl = $detail->variant_image_url ?? $detail->variant?->image_url;
            if (! empty($variantUrl)) {
                $path = parse_url($variantUrl, PHP_URL_PATH);
                if ($path && str_contains($path, '/uploads/')) {
                    if (file_exists(public_path(ltrim($path, '/')))) {
                        return $variantUrl;
                    }
                    // File biến thể không tồn tại trên đĩa -> dùng placeholder Bear như bên Đơn hàng
                    return $placeholder;
                }
                return $variantUrl;
            }
            // Biến thể không có ảnh riêng -> không lấy ảnh của phân loại khác, hiển thị placeholder Bear
            return $placeholder;
        }

        // Sản phẩm thường không có phân loại biến thể
        $primaryUrl = $product?->images?->firstWhere('is_primary', true)?->image_url
            ?? $product?->images?->first()?->image_url;

        if (! empty($primaryUrl)) {
            $path = parse_url($primaryUrl, PHP_URL_PATH);
            if ($path && str_contains($path, '/uploads/')) {
                if (file_exists(public_path(ltrim($path, '/')))) {
                    return $primaryUrl;
                }
                return $placeholder;
            }
            return $primaryUrl;
        }

        return $placeholder;
    }

    /**
     * Trích xuất chuỗi hiển thị phân loại sản phẩm con (VD: "Màu Nâu / Size 1m")
     */
    public static function resolveVariantText($detail, string $separator = ' / '): ?string
    {
        if (! $detail) {
            return null;
        }

        $product = $detail->product ?? null;
        $variantParts = [];
        $color = $detail->variant_color ?? $detail->variant?->color ?? $product?->color;
        $size = $detail->variant_size ?? $detail->variant?->size ?? $product?->size;

        if (! empty($color)) {
            $variantParts[] = 'Màu ' . $color;
        }
        if (! empty($size)) {
            $variantParts[] = 'Size ' . $size;
        }
        if (empty($variantParts) && ! empty($detail->variant_name)) {
            $variantParts[] = $detail->variant_name;
        }
        if (empty($variantParts) && ! empty($product?->material)) {
            $variantParts[] = $product->material;
        }

        return ! empty($variantParts) ? implode($separator, $variantParts) : null;
    }
}

