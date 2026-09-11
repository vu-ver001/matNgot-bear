<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaymentExpiryTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $product;
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'CUSTOMER']);
        $category = Category::create(['name' => 'Gấu bông', 'is_active' => true]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu bông Teddy Test',
            'description' => 'Mô tả test',
            'price' => 200000,
            'sale_price' => 180000,
            'stock_quantity' => 10,
            'status' => 'ACTIVE',
        ]);

        $this->orderService = app(OrderService::class);
    }

    private function createOnlineOrder(int $hoursAgo = 0): Order
    {
        $order = $this->orderService->createOrder([
            'customer_id' => $this->customer->id,
            'recipient_name' => $this->customer->full_name,
            'recipient_phone' => '0987654321',
            'recipient_address' => 'Hà Nội',
            'payment_method' => 'CARD',
        ], [(object) [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]]);

        if ($hoursAgo > 0) {
            $pastTime = now()->subHours($hoursAgo);
            $order->timestamps = false;
            $order->created_at = $pastTime;
            $order->updated_at = $pastTime;
            $order->save();
            $order->timestamps = true;
        }

        return $order->fresh();
    }

    public function test_order_within_24h_is_not_expired_and_can_pay_online(): void
    {
        $order = $this->createOnlineOrder(hoursAgo: 10);

        $this->assertFalse($order->isPaymentExpired());
        $this->assertTrue($order->canPayOnline());
        $this->assertNotNull($order->paymentExpiresAt());
        $this->assertGreaterThan(0, $order->paymentRemainingSeconds());
    }

    public function test_order_older_than_24h_is_expired_and_cannot_pay_online(): void
    {
        $order = $this->createOnlineOrder(hoursAgo: 25);

        $this->assertTrue($order->isPaymentExpired());
        $this->assertFalse($order->canPayOnline());
        $this->assertSame(0, $order->paymentRemainingSeconds());
    }

    public function test_order_service_cancels_expired_unpaid_order_and_restores_stock(): void
    {
        // Initial stock: 10, order uses 2 -> current stock: 8
        $order = $this->createOnlineOrder(hoursAgo: 26);
        $this->assertSame(8, $this->product->fresh()->stock_quantity);

        $count = $this->orderService->cancelExpiredUnpaidOrders();

        $this->assertSame(1, $count);
        $order->refresh();
        $this->assertSame('CANCELLED', $order->order_status);
        $this->assertStringContainsString('24', $order->cancel_reason);
        $this->assertTrue($order->stock_restored);
        // Stock must be restored to 10
        $this->assertSame(10, $this->product->fresh()->stock_quantity);
    }

    public function test_artisan_command_cancels_expired_unpaid_orders(): void
    {
        $this->createOnlineOrder(hoursAgo: 30);
        $this->createOnlineOrder(hoursAgo: 5); // Still valid

        $this->artisan('orders:cancel-unpaid')
            ->expectsOutputToContain('Đã tự động hủy thành công 1 đơn hàng quá hạn 24 giờ')
            ->assertSuccessful();
    }

    public function test_customer_cannot_retry_payment_for_expired_order(): void
    {
        $order = $this->createOnlineOrder(hoursAgo: 25);

        $this->actingAs($this->customer);

        $response = $this->post(route('customer.payment.retry', $order), [
            'payment_method' => 'CARD',
        ]);

        $response->assertRedirect(route('customer.orders.show', $order->id));
        $response->assertSessionHas('error');

        // Order is now cancelled
        $this->assertSame('CANCELLED', $order->fresh()->order_status);
    }

    public function test_customer_viewing_expired_order_triggers_auto_cancellation(): void
    {
        $order = $this->createOnlineOrder(hoursAgo: 25);

        $this->actingAs($this->customer);

        $response = $this->get(route('customer.orders.show', $order));

        $response->assertOk();
        $this->assertSame('CANCELLED', $order->fresh()->order_status);
    }
}
