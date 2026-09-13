<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
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
        // Chỉ cần danh sách sản phẩm; tồn kho và biến thể được đọc lại ở mỗi
        // vòng lặp vì OrderService sẽ khóa và trừ kho trong transaction.
        $products = Product::where('status', 'ACTIVE')->get();
        $vouchers = Voucher::where('status', 'ACTIVE')->where('voucher_type', 'ORDER')->get();

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
            ['status' => 'RETURNED', 'count' => 1],
            ['status' => 'CANCELLED', 'count' => 2],
        ];

        $paymentMethods = ['COD', 'BANK_TRANSFER', 'E_WALLET', 'CARD'];

        foreach ($scenarios as $scenario) {
            for ($i = 0; $i < $scenario['count']; $i++) {
                $availableProducts = $products
                    ->map(fn (Product $product) => Product::with('variants')->find($product->id))
                    ->filter(fn (?Product $product) => $product && $this->availableStock($product) > 0)
                    ->values();

                if ($availableProducts->isEmpty()) {
                    $this->command->warn('Hết hàng, dừng tạo đơn mẫu.');

                    return;
                }

                $customer = $customers->random();
                $selectedProducts = $availableProducts->random(rand(1, min(3, $availableProducts->count())));

                $cartItems = $selectedProducts->map(function (Product $product) {
                    $variant = $this->selectVariant($product);
                    $availableStock = $variant ? (int) $variant->stock_quantity : (int) $product->stock_quantity;

                    return (object) [
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'quantity' => rand(1, min(3, $availableStock)),
                    ];
                })->all();

                $subtotal = collect($cartItems)->sum(function ($item) use ($selectedProducts) {
                    $product = $selectedProducts->firstWhere('id', $item->product_id);
                    $variant = $item->product_variant_id
                        ? $product?->variants?->firstWhere('id', $item->product_variant_id)
                        : null;
                    $price = $variant ? $variant->effective_price : $this->effectiveProductPrice($product);

                    return $price * $item->quantity;
                });

                $eligibleVouchers = $vouchers->filter(fn (Voucher $v) => $v->voucher_type === 'ORDER' && $subtotal >= $v->min_order_value);
                $voucher = $eligibleVouchers->first(fn (Voucher $v) => ! $v->isUsedByCustomer($customer->id));

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

                $order->update([
                    'created_at' => now()->subMonths(rand(0, 11))->setTime(rand(8, 21), rand(0, 59), rand(0, 59)),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function selectVariant(Product $product): ?ProductVariant
    {
        return $product->variants
            ->where('status', 'ACTIVE')
            ->where('stock_quantity', '>', 0)
            ->sortByDesc('is_default')
            ->sortBy('price')
            ->first();
    }

    private function availableStock(Product $product): int
    {
        $variantStock = $product->variants
            ->where('status', 'ACTIVE')
            ->sum(fn ($variant) => max(0, (int) $variant->stock_quantity));

        return $product->variants->isNotEmpty() ? (int) $variantStock : (int) $product->stock_quantity;
    }

    private function effectiveProductPrice(?Product $product): float
    {
        if (! $product) {
            return 0;
        }

        $base = (float) $product->price;
        $sale = (float) ($product->sale_price ?? 0);

        return $sale > 0 && $sale < $base ? $sale : $base;
    }

    private function advanceOrder(OrderService $orderService, Order $order, string $targetStatus): void
    {
        $flow = ['PENDING', 'CONFIRMED', 'PREPARING', 'SHIPPING', 'COMPLETED', 'RETURNED'];
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

            if ($targetStatus === 'COMPLETED' || $targetStatus === 'RETURNED') {
                $orderService->confirmPayment($payment, null);
            }
        }
    }
}
