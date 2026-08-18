<?php

namespace App\Services;

use App\Models\CartItem;
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

                if (!$product || $product->stock_quantity < $cartItem->quantity) {
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

            $discountAmount = 0;
            if (!empty($data['voucher_id'])) {
                $voucher = Voucher::find($data['voucher_id']);
                if ($voucher && $this->isVoucherValid($voucher, $subtotal)) {
                    $discountAmount = $this->calculateDiscount($voucher, $subtotal);
                }
            }

            $shippingFee = 30000;
            $totalAmount = $subtotal - $discountAmount + $shippingFee;

            $order = Order::create([
                'order_code' => $this->generateOrderCode(),
                'customer_id' => $data['customer_id'],
                'recipient_name' => $data['recipient_name'],
                'recipient_phone' => $data['recipient_phone'],
                'recipient_address' => $data['recipient_address'],
                'note' => $data['note'] ?? null,
                'voucher_id' => $data['voucher_id'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
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

            if (!empty($data['voucher_id']) && $discountAmount > 0) {
                Voucher::where('id', $data['voucher_id'])->increment('used_count');
            }

            return $order->load(['details.product', 'voucher']);
        });
    }

    public function cancelOrder(Order $order, ?int $cancelledBy = null, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $cancelledBy, $reason) {
            if (!in_array($order->order_status, ['PENDING', 'CONFIRMED'])) {
                throw new \Exception('Không thể hủy đơn hàng ở trạng thái hiện tại.');
            }

            if (blank($reason)) {
                throw new \Exception('Lý do hủy đơn là bắt buộc.');
            }

            $oldStatus = $order->order_status;

            $order->update([
                'order_status' => 'CANCELLED',
                'cancel_reason' => $reason,
                'cancelled_by' => $cancelledBy,
                'cancelled_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => 'CANCELLED',
                'changed_by' => $cancelledBy,
                'note' => $reason,
                'changed_at' => now(),
            ]);

            if (!$order->stock_restored) {
                $this->restoreStock($order);
                $order->update(['stock_restored' => true]);
            }

            return $order->fresh();
        });
    }

    public function updateStatus(Order $order, string $newStatus, ?int $changedBy = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $changedBy, $note) {
            $oldStatus = $order->order_status;

            $this->assertValidTransition($oldStatus, $newStatus, $note);

            $updateData = ['order_status' => $newStatus];

            if ($newStatus === 'CONFIRMED') {
                $updateData['confirmed_at'] = now();
            } elseif ($newStatus === 'COMPLETED') {
                $updateData['completed_at'] = now();
            } elseif ($newStatus === 'CANCELLED') {
                $updateData['cancelled_at'] = now();
                $updateData['cancelled_by'] = $changedBy;
                $updateData['cancel_reason'] = $note;
            }

            $order->update($updateData);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by' => $changedBy,
                'note' => $note,
                'changed_at' => now(),
            ]);

            if ($newStatus === 'CANCELLED' && !$order->stock_restored) {
                $this->restoreStock($order);
                $order->update(['stock_restored' => true]);
            }

            if ($newStatus === 'COMPLETED') {
                foreach ($order->details as $detail) {
                    $detail->product->increment('sold_count', $detail->quantity);
                }
            }

            return $order->fresh();
        });
    }

    public function restoreStock(Order $order): void
    {
        foreach ($order->details as $detail) {
            $detail->product->increment('stock_quantity', $detail->quantity);
        }
    }

    private function assertValidTransition(string $oldStatus, string $newStatus, ?string $note = null): void
    {
        $allowedTransitions = [
            'PENDING' => ['CONFIRMED', 'CANCELLED'],
            'CONFIRMED' => ['PREPARING', 'CANCELLED'],
            'PREPARING' => ['SHIPPING'],
            'SHIPPING' => ['COMPLETED'],
            'COMPLETED' => [],
            'CANCELLED' => [],
        ];

        if ($oldStatus === $newStatus) {
            throw new \Exception('Đơn hàng đã ở trạng thái này rồi.');
        }

        if (!isset($allowedTransitions[$oldStatus])) {
            throw new \Exception("Trạng thái đơn hàng '{$oldStatus}' không hợp lệ.");
        }

        if (!in_array($newStatus, $allowedTransitions[$oldStatus])) {
            throw new \Exception("Không thể chuyển đơn hàng từ '{$oldStatus}' sang '{$newStatus}'.");
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

    private function isVoucherValid(Voucher $voucher, float $subtotal): bool
    {
        if ($voucher->status !== 'ACTIVE') {
            return false;
        }

        if ($voucher->usage_limit && $voucher->used_count >= $voucher->usage_limit) {
            return false;
        }

        $now = now();
        if ($now->lt($voucher->start_date) || $now->gt($voucher->end_date)) {
            return false;
        }

        if ($subtotal < $voucher->min_order_value) {
            return false;
        }

        return true;
    }

    private function calculateDiscount(Voucher $voucher, float $subtotal): float
    {
        $discount = $voucher->discount_type === 'PERCENTAGE'
            ? $subtotal * ($voucher->discount_value / 100)
            : $voucher->discount_value;

        if ($voucher->max_discount_value && $discount > $voucher->max_discount_value) {
            $discount = $voucher->max_discount_value;
        }

        return min($discount, $subtotal);
    }

    private function generateOrderCode(): string
    {
        return 'MNB' . strtoupper(uniqid());
    }
}
