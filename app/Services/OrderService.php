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

                if ($product->variants()->exists()) {
                    $remainingToDecrement = $cartItem->quantity;
                    $variants = $product->variants()
                        ->orderByDesc('stock_quantity')
                        ->lockForUpdate()
                        ->get();

                    foreach ($variants as $variant) {
                        if ($remainingToDecrement <= 0) break;
                        $dec = min($variant->stock_quantity, $remainingToDecrement);
                        if ($dec > 0) {
                            $variant->decrement('stock_quantity', $dec);
                            $remainingToDecrement -= $dec;
                        }
                    }
                    if ($remainingToDecrement > 0 && $variants->isNotEmpty()) {
                        $variants->first()->decrement('stock_quantity', $remainingToDecrement);
                    }
                    $product->stock_quantity = (int) $product->variants()->sum('stock_quantity');
                    $product->saveQuietly();
                } else {
                    $product->decrement('stock_quantity', $cartItem->quantity);
                }
            }

            $shippingFee = isset($data['shipping_fee']) ? (float) $data['shipping_fee'] : 30000;
            $shippingMethod = in_array($data['shipping_method'] ?? 'standard', ['standard', 'fast', 'express'], true)
                ? $data['shipping_method'] ?? 'standard'
                : 'standard';
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
                'shipping_method' => $shippingMethod,
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

    public function cancelOrder(Order $order, ?int $cancelledBy = null, ?string $reason = null, array $refundData = []): Order
    {
        return DB::transaction(function () use ($order, $cancelledBy, $reason, $refundData) {
            $currentOrder = Order::lockForUpdate()->find($order->id) ?? $order;
            if ($currentOrder->order_status !== 'PENDING') {
                throw new \Exception('Bạn chỉ có thể hủy đơn hàng đang chờ xác nhận.');
            }

            $reason = !empty($reason) ? $reason : 'Khách hàng hủy đơn';

            $oldStatus = $currentOrder->order_status;

            $updateData = [
                'order_status' => 'CANCELLED',
                'cancel_reason' => $reason,
                'cancelled_by' => $cancelledBy,
                'cancelled_at' => now(),
            ];

            if (! empty($refundData['refund_bank_name'])) {
                $updateData['refund_bank_name'] = $refundData['refund_bank_name'];
            }
            if (! empty($refundData['refund_bank_account'])) {
                $updateData['refund_bank_account'] = $refundData['refund_bank_account'];
            }
            if (! empty($refundData['refund_account_holder'])) {
                $updateData['refund_account_holder'] = $refundData['refund_account_holder'];
            }

            $currentOrder->update($updateData);
            $order->fill($updateData);

            $historyNote = $reason;
            if ($currentOrder->payment_status === 'PAID') {
                $historyNote .= ' (Đơn đã thanh toán online - Chờ shop liên hệ hoàn tiền)';
            }

            OrderStatusHistory::create([
                'order_id' => $currentOrder->id,
                'from_status' => $oldStatus,
                'to_status' => 'CANCELLED',
                'changed_by' => $cancelledBy,
                'note' => $historyNote,
                'changed_at' => now(),
            ]);

            if (! $currentOrder->stock_restored) {
                $this->restoreStock($currentOrder);
                $currentOrder->update(['stock_restored' => true]);
                $order->stock_restored = true;
            }

            // Hoàn lại lượt dùng voucher
            if ($order->discount_amount > 0 && ! empty($order->voucher_id)) {
                Voucher::where('id', $order->voucher_id)->where('used_count', '>', 0)->decrement('used_count');
            }
            if ($order->shipping_discount_amount > 0 && ! empty($order->shipping_voucher_id)) {
                Voucher::where('id', $order->shipping_voucher_id)->where('used_count', '>', 0)->decrement('used_count');
            }

            return $order->fresh();
        });
    }

    /**
     * Tự động kiểm tra và hủy đơn hàng nếu đơn quá hạn thanh toán 24 giờ.
     * Trả về true nếu đơn vừa được tự động hủy.
     */
    public function checkAndCancelIfExpired(Order $order): bool
    {
        if ($order->order_status === 'CANCELLED') {
            return false;
        }

        if ($order->isPaymentExpired() && $order->order_status === 'PENDING') {
            $this->cancelOrder(
                $order,
                null,
                'Hệ thống tự động hủy do quá thời hạn thanh toán 24 giờ'
            );
            return true;
        }

        return false;
    }

    /**
     * Quét và tự động hủy tất cả các đơn hàng online chưa thanh toán quá 24h.
     * Trả về số lượng đơn đã hủy.
     */
    public function cancelExpiredUnpaidOrders(): int
    {
        $expiredOrders = Order::query()
            ->where('order_status', 'PENDING')
            ->whereIn('payment_status', ['UNPAID', 'FAILED'])
            ->whereIn('payment_method', ['BANK_TRANSFER', 'CARD', 'E_WALLET'])
            ->where('created_at', '<=', now()->subHours(24))
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            try {
                $this->cancelOrder(
                    $order,
                    null,
                    'Hệ thống tự động hủy do quá thời hạn thanh toán 24 giờ'
                );
                $count++;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Lỗi khi tự động hủy đơn hàng #{$order->order_code}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Nhân viên xác nhận đã chuyển tiền hoàn lại cho khách hàng
     */
    public function confirmRefundOrder(Order $order, int $adminId, ?string $refundNote = null): Order
    {
        return DB::transaction(function () use ($order, $adminId, $refundNote) {
            $paidPayment = $order->payments->firstWhere('status', 'PAID');
            if ($paidPayment) {
                $paidPayment->update(['status' => 'REFUNDED']);
            }

            $order->update([
                'payment_status' => 'REFUNDED',
                'refund_note' => $refundNote,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $order->order_status,
                'to_status' => $order->order_status,
                'changed_by' => $adminId,
                'note' => 'Nhân viên đã xác nhận chuyển tiền hoàn cho khách.' . ($refundNote ? ' Ghi chú: ' . $refundNote : ''),
                'changed_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Khách hàng gửi yêu cầu hủy đơn hàng (chờ nhân viên xác nhận)
     */
    public function requestCancelOrder(Order $order, array $data, int $customerId): Order
    {
        return DB::transaction(function () use ($order, $data, $customerId) {
            if (! $order->canRequestCancel()) {
                throw new \Exception('Đơn hàng hiện tại không thể gửi yêu cầu hủy.');
            }

            if (blank($data['reason'] ?? null)) {
                throw new \Exception('Vui lòng nhập lý do hủy đơn hàng.');
            }

            $order->update([
                'cancel_request_status' => 'PENDING',
                'cancel_request_reason' => $data['reason'],
                'cancel_requested_at' => now(),
                'cancel_rejection_reason' => null,
                'refund_bank_name' => $data['refund_bank_name'] ?? null,
                'refund_bank_account' => $data['refund_bank_account'] ?? null,
                'refund_account_holder' => $data['refund_account_holder'] ?? null,
            ]);

            $isPaidNotice = $order->payment_status === 'PAID' ? ' (Đơn đã thanh toán, chờ shop liên hệ hoàn tiền)' : '';

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $order->order_status,
                'to_status' => $order->order_status,
                'changed_by' => $customerId,
                'note' => 'Khách hàng gửi yêu cầu hủy đơn: ' . $data['reason'] . $isPaidNotice,
                'changed_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Khách hàng rút lại yêu cầu hủy đơn khi còn PENDING
     */
    public function withdrawCancelRequest(Order $order, int $customerId): Order
    {
        return DB::transaction(function () use ($order, $customerId) {
            if (! $order->hasPendingCancelRequest()) {
                throw new \Exception('Đơn hàng không có yêu cầu hủy nào đang chờ duyệt.');
            }

            $order->update([
                'cancel_request_status' => null,
                'cancel_request_reason' => null,
                'cancel_requested_at' => null,
                'refund_bank_name' => null,
                'refund_bank_account' => null,
                'refund_account_holder' => null,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $order->order_status,
                'to_status' => $order->order_status,
                'changed_by' => $customerId,
                'note' => 'Khách hàng đã rút lại yêu cầu hủy đơn.',
                'changed_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Nhân viên duyệt yêu cầu hủy đơn hàng
     */
    public function approveCancelOrder(Order $order, int $adminId, ?string $refundNote = null): Order
    {
        return DB::transaction(function () use ($order, $adminId, $refundNote) {
            if (! in_array($order->order_status, ['PENDING', 'CONFIRMED'])) {
                throw new \Exception('Không thể hủy đơn hàng ở trạng thái hiện tại.');
            }

            $oldStatus = $order->order_status;
            $reason = $order->cancel_request_reason ?: 'Nhân viên đã duyệt hủy theo yêu cầu của khách hàng';

            // Hoàn tiền nếu đơn đã thanh toán
            $paidPayment = $order->payments->firstWhere('status', 'PAID');
            if ($paidPayment) {
                $this->refundPayment($paidPayment);
            }

            $order->update([
                'order_status' => 'CANCELLED',
                'cancel_request_status' => 'APPROVED',
                'cancel_reason' => $reason,
                'cancelled_by' => $adminId,
                'cancelled_at' => now(),
                'refund_note' => $refundNote,
            ]);

            $historyNote = 'Nhân viên đã duyệt yêu cầu hủy đơn hàng.' . ($refundNote ? ' Ghi chú hoàn tiền: ' . $refundNote : '');

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => 'CANCELLED',
                'changed_by' => $adminId,
                'note' => $historyNote,
                'changed_at' => now(),
            ]);

            if (! $order->stock_restored) {
                $this->restoreStock($order);
                $order->update(['stock_restored' => true]);
            }

            // Hoàn lại lượt dùng voucher
            if ($order->discount_amount > 0 && ! empty($order->voucher_id)) {
                Voucher::where('id', $order->voucher_id)->where('used_count', '>', 0)->decrement('used_count');
            }
            if ($order->shipping_discount_amount > 0 && ! empty($order->shipping_voucher_id)) {
                Voucher::where('id', $order->shipping_voucher_id)->where('used_count', '>', 0)->decrement('used_count');
            }

            return $order->fresh();
        });
    }

    /**
     * Nhân viên từ chối yêu cầu hủy đơn hàng
     */
    public function rejectCancelOrder(Order $order, int $adminId, string $rejectionReason): Order
    {
        return DB::transaction(function () use ($order, $adminId, $rejectionReason) {
            if (! $order->hasPendingCancelRequest()) {
                throw new \Exception('Đơn hàng không có yêu cầu hủy nào đang chờ duyệt.');
            }

            if (blank($rejectionReason)) {
                throw new \Exception('Vui lòng nhập lý do từ chối yêu cầu hủy đơn.');
            }

            $order->update([
                'cancel_request_status' => 'REJECTED',
                'cancel_rejection_reason' => $rejectionReason,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $order->order_status,
                'to_status' => $order->order_status,
                'changed_by' => $adminId,
                'note' => 'Nhân viên từ chối yêu cầu hủy đơn. Lý do: ' . $rejectionReason,
                'changed_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    public function updateStatus(
        Order $order,
        string $newStatus,
        ?int $changedBy = null,
        ?string $note = null,
        bool $stockReturned = false
    ): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $changedBy, $note, $stockReturned) {
            $order->setRawAttributes(Order::lockForUpdate()->findOrFail($order->id)->getAttributes(), true);
            $order->unsetRelations();
            $oldStatus = $order->order_status;

            $this->assertValidTransition($order, $newStatus, $note, $stockReturned);

            $updateData = ['order_status' => $newStatus];

            if ($newStatus === 'CONFIRMED') {
                $updateData['confirmed_at'] = now();
            } elseif ($newStatus === 'SHIPPING') {
                $updateData['shipped_at'] = now();
            } elseif ($newStatus === 'COMPLETED') {
                $updateData['completed_at'] = now();
            } elseif ($newStatus === 'CANCELLED') {
                $actualNote = $note ?: ($order->cancel_request_reason ?: 'Nhân viên đã xác nhận hủy đơn');
                $updateData['cancelled_at'] = now();
                $updateData['cancelled_by'] = $changedBy;
                $updateData['cancel_reason'] = $actualNote;
                $note = $actualNote;

                if ($order->hasPendingCancelRequest()) {
                    $updateData['cancel_request_status'] = 'APPROVED';
                }

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
            $product = $detail->product()->withTrashed()->first();
            if ($product) {
                if ($product->variants()->exists()) {
                    $defaultVar = $product->variants()->first();
                    if ($defaultVar) {
                        $defaultVar->increment('stock_quantity', $detail->quantity);
                    }
                    $product->stock_quantity = (int) $product->variants()->sum('stock_quantity');
                    $product->saveQuietly();
                } else {
                    $product->increment('stock_quantity', $detail->quantity);
                }
            }
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

    private function assertValidTransition(
        Order $order,
        string $newStatus,
        ?string $note = null,
        bool $stockReturned = false
    ): void
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

        if ($oldStatus === 'SHIPPING' && $newStatus === 'CANCELLED' && ! $stockReturned) {
            throw new \Exception('Chỉ được hủy đơn đang giao sau khi xác nhận hàng đã quay lại kho.');
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
