<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data, array $cartItems): Order
    {
        return DB::transaction(function () use ($data, $cartItems) {
            $subtotal = 0;
            $orderDetails = [];

            foreach ($cartItems as $cartItem) {
                $product = Product::lockForUpdate()->find($cartItem->product_id);

                if (! $product || $product->stock_quantity < $cartItem->quantity) {
                    $productName = $product->name ?? 'không xác định';
                    throw new \Exception("Sản phẩm '{$productName}' không đủ tồn kho.");
                }

                $price = $product->sale_price ?? $product->price;
                $lineTotal = $price * $cartItem->quantity;
                $subtotal += $lineTotal;

                $orderDetails[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $price,
                    'quantity' => $cartItem->quantity,
                    'line_total' => $lineTotal,
                ];

                $product->decrement('stock_quantity', $cartItem->quantity);
            }

            $shippingFee = isset($data['shipping_fee']) ? (float) $data['shipping_fee'] : 30000;
            $discountAmount = 0;
            $shippingDiscountAmount = 0;

            // Voucher giảm giá đơn hàng (voucher_type = ORDER)
            if (! empty($data['voucher_id'])) {
                $voucher = Voucher::find($data['voucher_id']);

                if ($voucher && $voucher->voucher_type === 'ORDER') {
                    $result = $voucher->validateForCustomer((int) $data['customer_id'], $subtotal, $shippingFee, $cartItems);

                    if (! $result['valid']) {
                        throw new \Exception($result['message']);
                    }

                    $discountAmount = (float) $result['discount_amount'];
                }
            }

            // Voucher freeship (voucher_type = SHIPPING)
            if (! empty($data['shipping_voucher_id'])) {
                $shippingVoucher = Voucher::find($data['shipping_voucher_id']);

                if ($shippingVoucher && $shippingVoucher->voucher_type === 'SHIPPING') {
                    $result = $shippingVoucher->validateForCustomer((int) $data['customer_id'], $subtotal, $shippingFee, $cartItems);

                    if (! $result['valid']) {
                        throw new \Exception($result['message']);
                    }

                    $shippingDiscountAmount = min((float) $result['discount_amount'], $shippingFee);
                }
            }

            $totalAmount = max(0, $subtotal - $discountAmount) + $shippingFee - $shippingDiscountAmount;

            $order = Order::create([
                'order_code' => $this->generateOrderCode(),
                'customer_id' => $data['customer_id'],
                'recipient_name' => $data['recipient_name'],
                'recipient_phone' => $data['recipient_phone'],
                'recipient_address' => $data['recipient_address'],
                'note' => $data['note'] ?? null,
                'voucher_id' => $data['voucher_id'] ?? null,
                'shipping_voucher_id' => $data['shipping_voucher_id'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'shipping_discount_amount' => $shippingDiscountAmount,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'order_status' => 'PENDING',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'UNPAID',
            ]);

            foreach ($orderDetails as $detail) {
                OrderDetail::create(array_merge($detail, ['order_id' => $order->id]));
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'PENDING',
                'changed_by' => null,
                'note' => 'Đơn hàng được tạo',
                'changed_at' => now(),
            ]);

            if ($discountAmount > 0 && ! empty($data['voucher_id'])) {
                Voucher::where('id', $data['voucher_id'])->increment('used_count');
            }

            if ($shippingDiscountAmount > 0 && ! empty($data['shipping_voucher_id'])) {
                Voucher::where('id', $data['shipping_voucher_id'])->increment('used_count');
            }

            return $order->load(['details.product', 'voucher', 'shippingVoucher']);
        });
    }

    public function cancelOrder(Order $order, ?int $cancelledBy = null, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $cancelledBy, $reason) {
            $lockedOrder = Order::lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->order_status !== 'PENDING') {
                throw new \Exception('Bạn chỉ có thể hủy đơn hàng đang chờ xác nhận.');
            }

            return $this->updateStatus($lockedOrder, 'CANCELLED', $cancelledBy, $reason);
        });
    }

    public function updateStatus(Order $order, string $newStatus, ?int $changedBy = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $changedBy, $note) {
            $order->setRawAttributes(Order::lockForUpdate()->findOrFail($order->id)->getAttributes(), true);
            $order->unsetRelations();
            $oldStatus = $order->order_status;

            $this->assertValidTransition($order, $newStatus, $note);

            $updateData = ['order_status' => $newStatus];

            if ($newStatus === 'CONFIRMED') {
                $updateData['confirmed_at'] = now();
            } elseif ($newStatus === 'COMPLETED') {
                $updateData['completed_at'] = now();
            } elseif ($newStatus === 'CANCELLED') {
                $updateData['cancelled_at'] = now();
                $updateData['cancelled_by'] = $changedBy;
                $updateData['cancel_reason'] = $note;

                $paidPayment = $order->payments->firstWhere('status', 'PAID');

                if ($paidPayment) {
                    $this->refundPayment($paidPayment);
                }
            } elseif ($newStatus === 'RETURNED') {
                $paidPayment = $order->payments->firstWhere('status', 'PAID');

                if ($paidPayment) {
                    $this->refundPayment($paidPayment);
                }
            }

            $order->update($updateData);

            if ($newStatus === 'COMPLETED') {
                $this->confirmCodPayment($order, $changedBy);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by' => $changedBy,
                'note' => $note,
                'changed_at' => now(),
            ]);

            if ($newStatus === 'CANCELLED' && ! $order->stock_restored) {
                $this->restoreStock($order);
                $order->update(['stock_restored' => true]);
            }

            if ($newStatus === 'COMPLETED') {
                foreach ($order->details as $detail) {
                    $detail->product()->withTrashed()->first()?->increment('sold_count', $detail->quantity);
                }
            }

            return $order->fresh();
        });
    }

    public function restoreStock(Order $order): void
    {
        foreach ($order->details as $detail) {
            $detail->product()->withTrashed()->first()?->increment('stock_quantity', $detail->quantity);
        }
    }

    /**
     * Cập nhật trạng thái hàng loạt (Giao hàng loạt / Xác nhận hàng loạt).
     *
     * @param array<int> $orderIds
     * @param string $targetStatus
     * @param int|null $changedBy
     * @param string|null $note
     * @return array{updated: int, skipped: int, target_status: string}
     */
    public function bulkUpdateStatus(array $orderIds, string $targetStatus = 'SHIPPING', ?int $changedBy = null, ?string $note = null): array
    {
        return DB::transaction(function () use ($orderIds, $targetStatus, $changedBy, $note) {
            $orders = Order::whereIn('id', $orderIds)->orderBy('id')->lockForUpdate()->get();
            $updated = 0;
            $skipped = 0;

            foreach ($orders as $order) {
                if (! in_array($targetStatus, $order->allowedNextStatuses(), true)) {
                    $skipped++;
                    continue;
                }

                $this->updateStatus($order, $targetStatus, $changedBy, $note);
                $updated++;
            }

            return [
                'updated' => $updated,
                'skipped' => $skipped,
                'target_status' => $targetStatus,
            ];
        });
    }

    private function assertValidTransition(Order $order, string $newStatus, ?string $note = null): void
    {
        $allowedTransitions = Order::STATUS_TRANSITIONS;
        $oldStatus = $order->order_status;

        if ($oldStatus === $newStatus) {
            throw new \Exception('Đơn hàng đã ở trạng thái này rồi.');
        }

        if (! isset($allowedTransitions[$oldStatus])) {
            throw new \Exception("Trạng thái đơn hàng '{$oldStatus}' không hợp lệ.");
        }

        if (! in_array($newStatus, $allowedTransitions[$oldStatus])) {
            throw new \Exception("Không thể chuyển đơn hàng từ '{$oldStatus}' sang '{$newStatus}'.");
        }

        if (! $order->canTransitionTo($newStatus)) {
            throw new \Exception('Đơn thanh toán trước phải được xác nhận đã thanh toán trước khi giao hàng.');
        }

        if ($newStatus === 'CANCELLED' && blank($note)) {
            throw new \Exception('Lý do hủy đơn là bắt buộc.');
        }
    }

    public function createPayment(Order $order, array $data): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'method' => $data['method'],
            'status' => 'PENDING',
            'amount' => $order->total_amount,
            'transaction_ref' => $data['transaction_ref'] ?? null,
            'gateway_response' => $data['gateway_response'] ?? null,
        ]);
    }

    private function confirmCodPayment(Order $order, ?int $confirmedBy): void
    {
        if ($order->payment_method !== 'COD') {
            return;
        }

        $payment = $order->payments()->where('method', 'COD')->where('status', 'PENDING')->latest('id')->first();

        if (! $payment && ! $order->payments()->where('method', 'COD')->where('status', 'PAID')->exists()) {
            $payment = $this->createPayment($order, ['method' => 'COD']);
        }

        if ($payment) {
            $this->confirmPayment($payment, $confirmedBy);
        } elseif ($order->payment_status !== 'PAID') {
            $order->update(['payment_status' => 'PAID']);
        }
    }

    public function confirmPayment(Payment $payment, ?int $confirmedBy = null): Payment
    {
        if ($payment->status !== 'PENDING') {
            throw new \Exception('Chỉ xác nhận được giao dịch đang chờ thanh toán.');
        }

        $payment->update([
            'status' => 'PAID',
            'confirmed_by' => $confirmedBy,
            'paid_at' => now(),
        ]);

        $payment->order->update(['payment_status' => 'PAID']);

        return $payment->fresh();
    }

    public function markPaymentFailed(Payment $payment): Payment
    {
        if ($payment->status !== 'PENDING') {
            throw new \Exception('Chỉ đánh dấu thất bại cho giao dịch đang chờ thanh toán.');
        }

        $payment->update(['status' => 'FAILED']);
        $payment->order->update(['payment_status' => 'FAILED']);

        return $payment->fresh();
    }

    public function refundPayment(Payment $payment): Payment
    {
        if ($payment->status !== 'PAID') {
            throw new \Exception('Chỉ hoàn tiền cho giao dịch đã thanh toán.');
        }

        $payment->update(['status' => 'REFUNDED']);
        $payment->order->update(['payment_status' => 'REFUNDED']);

        return $payment->fresh();
    }

    private function generateOrderCode(): string
    {
        return 'MNB'.strtoupper(uniqid());
    }
}
