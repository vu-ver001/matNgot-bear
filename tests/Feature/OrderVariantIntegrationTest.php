<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderVariantIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_uses_variant_price_stock_and_snapshot(): void
    {
        [$customer, $product, $variant] = $this->makeVariantProduct();

        $order = app(OrderService::class)->createOrder([
            'customer_id' => $customer->id,
            'recipient_name' => 'Khách kiểm thử',
            'recipient_phone' => '0900000000',
            'recipient_address' => 'Hà Nội',
            'payment_method' => 'COD',
        ], [[
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]]);

        $detail = $order->details()->firstOrFail();

        $this->assertSame($variant->id, $detail->product_variant_id);
        $this->assertSame('SKU-BEAR-RED', $detail->variant_sku);
        $this->assertSame(150000.0, (float) $detail->product_price);
        $this->assertSame(200000.0, (float) $detail->original_unit_price);
        $this->assertSame(300000.0, (float) $order->subtotal);
        $this->assertSame(1, (int) $variant->fresh()->stock_quantity);
        $this->assertSame(6, (int) $product->fresh()->stock_quantity);
    }

    public function test_cancelling_variant_order_restores_variant_stock(): void
    {
        [$customer, $product, $variant] = $this->makeVariantProduct();
        $service = app(OrderService::class);
        $order = $service->createOrder([
            'customer_id' => $customer->id,
            'recipient_name' => 'Khách kiểm thử',
            'recipient_phone' => '0900000000',
            'recipient_address' => 'Hà Nội',
            'payment_method' => 'COD',
        ], [[
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]]);

        $service->cancelOrder($order, $customer->id, 'Đổi ý');

        $this->assertSame('CANCELLED', $order->fresh()->order_status);
        $this->assertSame(3, (int) $variant->fresh()->stock_quantity);
        $this->assertSame(8, (int) $product->fresh()->stock_quantity);
    }

    public function test_variant_product_cannot_be_ordered_without_variant_id(): void
    {
        [$customer, $product] = $this->makeVariantProduct();

        $this->expectExceptionMessage('Vui lòng chọn size/màu');

        app(OrderService::class)->createOrder([
            'customer_id' => $customer->id,
            'recipient_name' => 'Khách kiểm thử',
            'recipient_phone' => '0900000000',
            'recipient_address' => 'Hà Nội',
            'payment_method' => 'COD',
        ], [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]]);
    }

    /** @return array{0: User, 1: Product, 2: ProductVariant} */
    private function makeVariantProduct(): array
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $category = Category::create(['name' => 'Gấu kiểm thử', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu biến thể kiểm thử',
            'description' => 'Variant integration test',
            'price' => 200000,
            'stock_quantity' => 0,
            'status' => Product::STATUS_ACTIVE,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-BEAR-RED',
            'size' => '40cm',
            'color' => 'Đỏ',
            'price' => 200000,
            'sale_price' => 150000,
            'stock_quantity' => 3,
            'is_default' => true,
            'status' => Product::STATUS_ACTIVE,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-BEAR-BROWN',
            'size' => '60cm',
            'color' => 'Nâu',
            'price' => 300000,
            'stock_quantity' => 5,
            'is_default' => false,
            'status' => Product::STATUS_ACTIVE,
        ]);

        $product->syncLowestPriceFromVariants();

        return [$customer, $product->fresh(), $variant->fresh()];
    }
}
