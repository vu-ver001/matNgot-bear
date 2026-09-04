<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Voucher;
use App\Models\WishlistItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShoppingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $customer = User::query()->where('email', 'customer@matngotbear.test')->firstOrFail();
            $linh = User::query()->where('email', 'linh@matngotbear.test')->firstOrFail();
            $staff = User::query()->where('email', 'staff@matngotbear.test')->firstOrFail();

            $teddy = $this->product('Gấu Teddy Mật Ong 45cm');
            $strawberryBear = $this->product('Gấu Dâu Hồng 60cm');
            $classicBear = $this->product('Gấu Nâu Cổ Điển 80cm');
            $rabbit = $this->product('Thỏ Bông Kem 40cm');
            $capybara = $this->product('Capybara Đội Quýt 35cm');
            $christmasBear = $this->product('Gấu Noel Phiên Bản Giới Hạn');

            $honeyVoucher = Voucher::query()->updateOrCreate(
                ['code' => 'HONEY10'],
                [
                    'discount_type' => 'PERCENTAGE',
                    'discount_value' => 10,
                    'min_order_value' => 300000,
                    'max_discount_value' => 100000,
                    'start_date' => now()->subMonth(),
                    'end_date' => now()->addMonths(3),
                    'usage_limit' => 500,
                    'used_count' => 37,
                    'status' => 'ACTIVE',
                ],
            );

            Voucher::query()->updateOrCreate(
                ['code' => 'BEAR50'],
                [
                    'discount_type' => 'FIXED',
                    'discount_value' => 50000,
                    'min_order_value' => 500000,
                    'max_discount_value' => null,
                    'start_date' => now()->subWeek(),
                    'end_date' => now()->addMonth(),
                    'usage_limit' => 200,
                    'used_count' => 12,
                    'status' => 'ACTIVE',
                ],
            );

            Voucher::query()->updateOrCreate(
                ['code' => 'WELCOME20'],
                [
                    'discount_type' => 'PERCENTAGE',
                    'discount_value' => 20,
                    'min_order_value' => 200000,
                    'max_discount_value' => 80000,
                    'start_date' => now()->subMonths(3),
                    'end_date' => now()->subMonth(),
                    'usage_limit' => 100,
                    'used_count' => 100,
                    'status' => 'INACTIVE',
                ],
            );

            foreach ([[$teddy, 1], [$rabbit, 2]] as [$product, $quantity]) {
                CartItem::query()->updateOrCreate(
                    ['user_id' => $customer->id, 'product_id' => $product->id],
                    ['quantity' => $quantity],
                );
            }

            foreach ([$teddy, $strawberryBear, $classicBear, $capybara, $christmasBear] as $product) {
                WishlistItem::query()->firstOrCreate([
                    'user_id' => $customer->id,
                    'product_id' => $product->id,
                ]);
            }

            WishlistItem::query()->firstOrCreate([
                'user_id' => $linh->id,
                'product_id' => $teddy->id,
            ]);

            $completedOrder = Order::query()->updateOrCreate(
                ['order_code' => 'MNB-DEMO-001'],
                [
                    'customer_id' => $customer->id,
                    'recipient_name' => $customer->full_name,
                    'recipient_phone' => $customer->phone,
                    'recipient_address' => $customer->address,
                    'note' => 'Gói quà giúp mình nhé.',
                    'voucher_id' => $honeyVoucher->id,
                    'subtotal' => 548000,
                    'discount_amount' => 54800,
                    'shipping_fee' => 30000,
                    'total_amount' => 523200,
                    'order_status' => 'COMPLETED',
                    'payment_method' => 'COD',
                    'payment_status' => 'PAID',
                    'cancel_reason' => null,
                    'cancelled_by' => null,
                    'stock_restored' => false,
                    'confirmed_at' => now()->subDays(31),
                    'completed_at' => now()->subDays(27),
                    'cancelled_at' => null,
                ],
            );

            $this->seedOrderDetail($completedOrder, $teddy, 299000, 1);
            $this->seedOrderDetail($completedOrder, $rabbit, 249000, 1);

            Payment::query()->updateOrCreate(
                ['order_id' => $completedOrder->id, 'method' => 'COD'],
                [
                    'status' => 'PAID',
                    'amount' => 523200,
                    'transaction_ref' => 'COD-MNB-DEMO-001',
                    'gateway_response' => null,
                    'confirmed_by' => $staff->id,
                    'paid_at' => now()->subDays(27),
                ],
            );

            $this->seedHistory($completedOrder, null, 'PENDING', $customer, 'Khách hàng đặt đơn.');
            $this->seedHistory($completedOrder, 'PENDING', 'CONFIRMED', $staff, 'Đã xác nhận thông tin đơn hàng.');
            $this->seedHistory($completedOrder, 'CONFIRMED', 'PREPARING', $staff, 'Đang đóng gói sản phẩm.');
            $this->seedHistory($completedOrder, 'PREPARING', 'SHIPPING', $staff, 'Đã bàn giao cho đơn vị vận chuyển.');
            $this->seedHistory($completedOrder, 'SHIPPING', 'COMPLETED', $staff, 'Khách hàng đã nhận hàng.');

            Review::query()->updateOrCreate(
                ['user_id' => $customer->id, 'product_id' => $teddy->id],
                [
                    'order_id' => $completedOrder->id,
                    'rating' => 5,
                    'comment' => 'Gấu mềm, màu đẹp và được đóng gói rất cẩn thận.',
                    'is_hidden' => false,
                ],
            );

            $shippingOrder = Order::query()->updateOrCreate(
                ['order_code' => 'MNB-DEMO-002'],
                [
                    'customer_id' => $linh->id,
                    'recipient_name' => $linh->full_name,
                    'recipient_phone' => $linh->phone,
                    'recipient_address' => $linh->address,
                    'note' => null,
                    'voucher_id' => null,
                    'subtotal' => 518000,
                    'discount_amount' => 0,
                    'shipping_fee' => 30000,
                    'total_amount' => 548000,
                    'order_status' => 'SHIPPING',
                    'payment_method' => 'COD',
                    'payment_status' => 'UNPAID',
                    'cancel_reason' => null,
                    'cancelled_by' => null,
                    'stock_restored' => false,
                    'confirmed_at' => now()->subDays(2),
                    'completed_at' => null,
                    'cancelled_at' => null,
                ],
            );

            $this->seedOrderDetail($shippingOrder, $capybara, 259000, 2);

            Payment::query()->updateOrCreate(
                ['order_id' => $shippingOrder->id, 'method' => 'COD'],
                [
                    'status' => 'PENDING',
                    'amount' => 548000,
                    'transaction_ref' => 'COD-MNB-DEMO-002',
                    'gateway_response' => null,
                    'confirmed_by' => null,
                    'paid_at' => null,
                ],
            );

            $this->seedHistory($shippingOrder, null, 'PENDING', $linh, 'Khách hàng đặt đơn.');
            $this->seedHistory($shippingOrder, 'PENDING', 'CONFIRMED', $staff, 'Đơn hàng hợp lệ.');
            $this->seedHistory($shippingOrder, 'CONFIRMED', 'PREPARING', $staff, 'Đã đóng gói.');
            $this->seedHistory($shippingOrder, 'PREPARING', 'SHIPPING', $staff, 'Đơn hàng đang được giao.');

            $cancelledOrder = Order::query()->updateOrCreate(
                ['order_code' => 'MNB-DEMO-003'],
                [
                    'customer_id' => $customer->id,
                    'recipient_name' => $customer->full_name,
                    'recipient_phone' => $customer->phone,
                    'recipient_address' => $customer->address,
                    'note' => null,
                    'voucher_id' => null,
                    'subtotal' => 399000,
                    'discount_amount' => 0,
                    'shipping_fee' => 30000,
                    'total_amount' => 429000,
                    'order_status' => 'CANCELLED',
                    'payment_method' => 'E_WALLET',
                    'payment_status' => 'FAILED',
                    'cancel_reason' => 'Sản phẩm mùa lễ hội đã ngừng bán.',
                    'cancelled_by' => $staff->id,
                    'stock_restored' => true,
                    'confirmed_at' => null,
                    'completed_at' => null,
                    'cancelled_at' => now()->subDays(5),
                ],
            );

            $this->seedOrderDetail($cancelledOrder, $christmasBear, 399000, 1);

            Payment::query()->updateOrCreate(
                ['order_id' => $cancelledOrder->id, 'method' => 'E_WALLET'],
                [
                    'status' => 'FAILED',
                    'amount' => 429000,
                    'transaction_ref' => 'EW-MNB-DEMO-003',
                    'gateway_response' => json_encode(['message' => 'Giao dịch mẫu thất bại'], JSON_UNESCAPED_UNICODE),
                    'confirmed_by' => null,
                    'paid_at' => null,
                ],
            );

            $this->seedHistory($cancelledOrder, null, 'PENDING', $customer, 'Khách hàng đặt đơn.');
            $this->seedHistory($cancelledOrder, 'PENDING', 'CANCELLED', $staff, 'Sản phẩm không còn khả dụng.');
        });
    }

    private function product(string $name): Product
    {
        return Product::query()->where('name', $name)->firstOrFail();
    }

    private function seedOrderDetail(Order $order, Product $product, int $price, int $quantity): void
    {
        OrderDetail::query()->updateOrCreate(
            ['order_id' => $order->id, 'product_id' => $product->id],
            [
                'product_name' => $product->name,
                'product_price' => $price,
                'quantity' => $quantity,
                'line_total' => $price * $quantity,
            ],
        );
    }

    private function seedHistory(
        Order $order,
        ?string $fromStatus,
        string $toStatus,
        User $changedBy,
        string $note,
    ): void {
        OrderStatusHistory::query()->updateOrCreate(
            ['order_id' => $order->id, 'to_status' => $toStatus],
            [
                'from_status' => $fromStatus,
                'changed_by' => $changedBy->id,
                'note' => $note,
                'changed_at' => now(),
            ],
        );
    }
}
