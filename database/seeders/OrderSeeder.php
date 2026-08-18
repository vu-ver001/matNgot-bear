<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use App\Services\OrderService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $orderService = app(OrderService::class);

        $customers = User::where('role', 'CUSTOMER')->get();
        $products = Product::where('status', 'ACTIVE')->get();
        $vouchers = Voucher::where('status', 'ACTIVE')->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Chưa có khách hàng/sản phẩm, bỏ qua tạo đơn hàng mẫu.');

            return;
        }

        $scenarios = [
            ['status' => 'PENDING', 'count' => 3],
            ['status' => 'CONFIRMED', 'count' => 2],
            ['status' => 'PREPARING', 'count' => 2],
            ['status' => 'SHIPPING', 'count' => 2],
            ['status' => 'COMPLETED', 'count' => 4],
            ['status' => 'CANCELLED', 'count' => 2],
        ];

        $paymentMethods = ['COD', 'BANK_TRANSFER', 'E_WALLET', 'CARD'];

        foreach ($scenarios as $scenario) {
            for ($i = 0; $i < $scenario['count']; $i++) {
                $customer = $customers->random();
                $selectedProducts = $products->random(rand(1, 3));

                $cartItems = $selectedProducts->map(fn (Product $product) => (object) [
                    'product_id' => $product->id,
                    'quantity' => rand(1, 3),
                ])->all();

                $subtotal = collect($cartItems)->sum(function ($item) {
                    $product = Product::find($item->product_id);

                    return ($product->sale_price ?? $product->price) * $item->quantity;
                });

                $voucher = $vouchers
                    ->filter(fn (Voucher $v) => $subtotal >= $v->min_order_value)
                    ->random(rand(0, 1) ? 1 : 0)
                    ->first();

                try {
                    $order = $orderService->createOrder([
                        'customer_id' => $customer->id,
                        'recipient_name' => $customer->full_name,
                        'recipient_phone' => $customer->phone ?? '0980000000',
                        'recipient_address' => $customer->address ?? 'Hà Nội',
                        'note' => $i % 2 ? null : 'Giao hàng giờ hành chính.',
                        'voucher_id' => $voucher?->id,
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    ], $cartItems);
                } catch (\Throwable $e) {
                    $this->command->error("Tạo đơn thất bại: {$e->getMessage()}");

                    continue;
                }

                $this->advanceOrder($orderService, $order, $scenario['status']);
            }
        }
    }

    private function advanceOrder(OrderService $orderService, Order $order, string $targetStatus): void
    {
        $flow = ['PENDING', 'CONFIRMED', 'PREPARING', 'SHIPPING', 'COMPLETED'];
        $targetIndex = array_search($targetStatus, $flow);

        if ($targetStatus === 'CANCELLED') {
            $orderService->cancelOrder($order, null, 'Dữ liệu mẫu: khách hàng đổi ý.');

            return;
        }

        foreach (array_slice($flow, 1, $targetIndex) as $nextStatus) {
            $orderService->updateStatus($order, $nextStatus, null, 'Dữ liệu mẫu');
        }

        if ($targetStatus === 'COMPLETED' || $targetIndex > 1) {
            $payment = $orderService->createPayment($order, [
                'method' => $order->payment_method,
                'transaction_ref' => 'TXN'.strtoupper(uniqid()),
            ]);

            if ($targetStatus === 'COMPLETED') {
                $orderService->confirmPayment($payment, null);
            }
        }
    }
}
