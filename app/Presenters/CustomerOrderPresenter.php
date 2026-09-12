<?php

namespace App\Presenters;

use App\Models\Order;
use App\Models\OrderDetail;

class CustomerOrderPresenter
{
    /**
     * Transform an Order into the exact customer order card data format.
     */
    public static function format(Order $order): array
    {
        $statusLabels = [
            'PENDING' => 'Chờ xác nhận',
            'CONFIRMED' => 'Đã xác nhận',
            'PREPARING' => 'Chờ lấy hàng',
            'SHIPPING' => 'Đang giao hàng',
            'COMPLETED' => 'Hoàn thành',
            'CANCELLED' => 'Đã hủy',
            'RETURNED' => 'Trả hàng',
        ];

        $deliveryStatuses = [
            'COMPLETED' => 'Đơn hàng đã được giao thành công',
            'SHIPPING' => 'Đơn hàng đang trên đường giao đến bạn',
            'PREPARING' => 'Người bán đang chuẩn bị kiện hàng',
            'CONFIRMED' => 'Người bán đã xác nhận đơn hàng',
            'PENDING' => 'Đang chờ người bán xác nhận đơn',
            'CANCELLED' => 'Đơn hàng đã bị hủy',
            'RETURNED' => 'Đơn hàng đã được trả hàng / hoàn tiền',
        ];

        $products = $order->details->map(function (OrderDetail $detail) {
            $product = $detail->product;

            $rawImg = $detail->variant_image_url
                ?: ($product?->images?->where('is_primary', true)->first()?->image_url
                    ?? $product?->images?->first()?->image_url);

            $imageUrl = '';
            if ($rawImg) {
                $imageUrl = str_starts_with($rawImg, 'http') ? $rawImg : asset($rawImg);
            } else {
                $imageUrl = 'https://placehold.co/120x120/fef3c7/78350f?text=Bear';
            }

            $variationParts = [];
            $size = $detail->variant_size ?: $product?->size;
            $color = $detail->variant_color ?: $product?->color;
            if (! empty($size)) {
                $variationParts[] = 'Size: '.$size;
            }
            if (! empty($color)) {
                $variationParts[] = 'Màu: '.$color;
            }
            if (! empty($detail->variant_sku)) {
                $variationParts[] = 'SKU: '.$detail->variant_sku;
            }
            $variation = ! empty($variationParts) ? implode(', ', $variationParts) : 'Phân loại tiêu chuẩn';

            $currentPrice = (float) $detail->product_price;
            $snapshotOriginalPrice = (float) ($detail->original_unit_price ?? 0);
            $originalPrice = $snapshotOriginalPrice > $currentPrice
                ? $snapshotOriginalPrice
                : (($product && $product->price > $currentPrice) ? (float) $product->price : $currentPrice);

            $productUrl = $detail->product_id
                ? route('products.show', $detail->product_id)
                : url('/');

            return [
                'id' => (string) ($detail->product_id ?? $detail->id),
                'name' => (string) $detail->product_name,
                'image' => $imageUrl,
                'variation' => $variation,
                'quantity' => (int) $detail->quantity,
                'price' => [
                    'original' => $originalPrice,
                    'current' => $currentPrice,
                    'currency' => 'VND',
                ],
                'productUrl' => $productUrl,
            ];
        })->values()->all();

        $subtotal = (float) ($order->subtotal ?? 0);
        if ($subtotal <= 0 && $order->details->isNotEmpty()) {
            $subtotal = (float) $order->details->sum('line_total');
        }

        $discount = (float) ($order->discount_amount ?? 0);
        $shippingFee = (float) ($order->shipping_fee ?? 0);
        $voucherDiscount = (float) (($order->discount_amount ?? 0) + ($order->shipping_discount_amount ?? 0));
        $total = (float) $order->total_amount;

        $isWaitingConfirmation = $order->isDeliveredWaitingConfirmation();
        $hasPendingReturn = $order->hasPendingReturnRequest();

        $statusLabel = $statusLabels[$order->order_status] ?? $order->order_status;
        $deliveryStatus = $deliveryStatuses[$order->order_status] ?? 'Đang xử lý đơn hàng';

        if ($hasPendingReturn) {
            $statusLabel = 'Yêu cầu trả hàng';
            $deliveryStatus = 'Đang xử lý yêu cầu Trả hàng / Hoàn tiền từ bạn';
        } elseif ($isWaitingConfirmation) {
            $statusLabel = 'Chờ bạn xác nhận';
            $deliveryStatus = 'Kiện hàng đã giao thành công. Vui lòng kiểm tra và xác nhận "Đã nhận được hàng".';
        } elseif ($order->order_status === 'COMPLETED' && $order->isCustomerConfirmed()) {
            $statusLabel = 'Hoàn thành';
            $deliveryStatus = 'Đơn hàng đã giao thành công và hoàn tất.';
        }

        $hasUnreviewed = false;
        if ($order->order_status === 'COMPLETED' && $order->isCustomerConfirmed() && $order->details->isNotEmpty()) {
            $reviewedProductIds = $order->reviews?->pluck('product_id')->all() ?? [];
            $hasUnreviewed = $order->details->contains(function ($detail) use ($reviewedProductIds) {
                return ! in_array($detail->product_id, $reviewedProductIds);
            });
        }

        return [
            'shop' => [
                'name' => config('app.name', 'Mật Ngọt Bear'),
                'favorite' => true,
                'chatEnabled' => true,
                'shopUrl' => url('/'),
            ],
            'order' => [
                'id' => (string) $order->order_code,
                'status' => (string) $order->order_status,
                'statusLabel' => $statusLabel,
                'deliveryStatus' => $deliveryStatus,
                'isWaitingConfirmation' => $isWaitingConfirmation,
                'hasPendingReturn' => $hasPendingReturn,
                'isCustomerConfirmed' => $order->isCustomerConfirmed(),
            ],
            'products' => $products,
            'payment' => [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shippingFee' => $shippingFee,
                'voucherDiscount' => $voucherDiscount,
                'total' => $total,
                'currency' => 'VND',
            ],
            'actions' => [
                'confirmReceived' => $isWaitingConfirmation || $order->order_status === 'SHIPPING',
                'requestReturn' => $isWaitingConfirmation && ! $hasPendingReturn,
                'hasPendingReturn' => $hasPendingReturn,
                'buyAgain' => $order->canBeReordered(),
                'contactSeller' => true,
                'review' => $hasUnreviewed,
            ],
        ];
    }
}
