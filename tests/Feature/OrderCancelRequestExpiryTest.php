<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancelRequestExpiryTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $staff;
    private Product $product;
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'CUSTOMER']);
        $this->staff = User::factory()->create(['role' => 'STAFF']);
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

    private function createConfirmedOrderWithCancelRequest(int $hoursAgo = 0): Order
    {
        $order = $this->orderService->createOrder([
            'customer_id' => $this->customer->id,
            'recipient_name' => $this->customer->full_name,
            'recipient_phone' => '0987654321',
            'recipient_address' => 'Hà Nội',
            'payment_method' => 'COD',
        ], [(object) [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]]);

        // Confirm the order first
        $order->update(['order_status' => 'CONFIRMED']);

        // Request cancel
        $requestTime = $hoursAgo > 0 ? Carbon::now()->subHours($hoursAgo) : Carbon::now();
        $order->update([
            'cancel_request_status' => 'PENDING',
            'cancel_requested_at' => $requestTime,
            'cancel_request_reason' => 'Khách đổi ý muốn mua màu khác',
        ]);

        return $order->fresh();
    }

    public function test_cancel_request_within_24h_is_not_expired(): void
    {
        $order = $this->createConfirmedOrderWithCancelRequest(hoursAgo: 6);

        $this->assertTrue($order->hasPendingCancelRequest());
        $this->assertFalse($order->isCancelRequestExpired());
        $this->assertNotNull($order->cancelRequestExpiresAt());
        $this->assertGreaterThan(17, $order->cancelRequestHoursRemaining());
        $this->assertStringContainsString('Còn', $order->cancelRequestTimeRemainingText());
    }

    public function test_cancel_request_older_than_24h_is_expired(): void
    {
        $order = $this->createConfirmedOrderWithCancelRequest(hoursAgo: 25);

        $this->assertTrue($order->hasPendingCancelRequest());
        $this->assertTrue($order->isCancelRequestExpired());
        $this->assertEquals(0, $order->cancelRequestHoursRemaining());
        $this->assertStringContainsString('Hết hạn', $order->cancelRequestTimeRemainingText());
    }

    public function test_service_auto_rejects_expired_cancel_request_and_preserves_order(): void
    {
        $orderExpired = $this->createConfirmedOrderWithCancelRequest(hoursAgo: 26);
        $orderActive = $this->createConfirmedOrderWithCancelRequest(hoursAgo: 4);

        $processedCount = $this->orderService->autoRejectExpiredCancelRequests();

        $this->assertSame(1, $processedCount);

        // Expired order should now be REJECTED, not CANCELLED
        $orderExpired->refresh();
        $this->assertSame('REJECTED', $orderExpired->cancel_request_status);
        $this->assertSame('CONFIRMED', $orderExpired->order_status);
        $this->assertStringContainsString('24 giờ', $orderExpired->cancel_rejection_reason);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $orderExpired->id,
            'note' => 'Hệ thống tự động từ chối yêu cầu hủy do quá thời hạn 24 giờ mà nhân viên chưa xử lý.',
        ]);

        // Active order should remain PENDING
        $orderActive->refresh();
        $this->assertSame('PENDING', $orderActive->cancel_request_status);
        $this->assertSame('CONFIRMED', $orderActive->order_status);
    }

    public function test_console_command_expires_cancel_requests(): void
    {
        $this->createConfirmedOrderWithCancelRequest(hoursAgo: 25);
        $this->createConfirmedOrderWithCancelRequest(hoursAgo: 2);

        $this->artisan('orders:expire-cancel-requests')
            ->expectsOutputToContain('1')
            ->assertExitCode(0);
    }

    public function test_customer_view_auto_expires_cancel_request_if_accessed_after_24h(): void
    {
        $order = $this->createConfirmedOrderWithCancelRequest(hoursAgo: 25);

        $response = $this->actingAs($this->customer)->get(route('customer.orders.show', $order));

        $response->assertOk();
        $order->refresh();
        $this->assertSame('REJECTED', $order->cancel_request_status);
        $this->assertSame('CONFIRMED', $order->order_status);
        $response->assertSee('Yêu cầu hủy đã quá thời hạn xử lý 24 giờ');
    }

    public function test_staff_order_view_shows_expiration_countdown(): void
    {
        $order = $this->createConfirmedOrderWithCancelRequest(hoursAgo: 5);

        $response = $this->actingAs($this->staff)->get(route('staff.orders.show', $order));

        $response->assertOk();
        $response->assertSee('Hạn chót xử lý (24h)');
        $response->assertSee('Còn');
    }
}
