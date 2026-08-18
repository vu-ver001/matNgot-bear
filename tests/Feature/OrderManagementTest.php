<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $customer;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'ADMIN']);
        $this->staff = User::factory()->create(['role' => 'STAFF']);
        $this->customer = User::factory()->create(['role' => 'CUSTOMER']);

        $category = Category::create(['name' => 'Test Category', 'is_active' => true]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu bông test',
            'description' => 'Test',
            'price' => 200000,
            'sale_price' => 150000,
            'stock_quantity' => 10,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->admin);

        $this->get('/admin')->assertOk();
        $this->get('/admin/orders')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/reviews')->assertOk();
    }

    public function test_staff_pages_render(): void
    {
        $this->actingAs($this->staff);

        $this->get('/staff')->assertOk();
        $this->get('/staff/orders')->assertOk();
    }

    public function test_customer_order_pages_render(): void
    {
        $order = $this->createOrder($this->customer);

        $this->actingAs($this->customer);

        $this->get('/customer/orders')->assertOk();
        $this->get('/customer/orders/'.$order->id)->assertOk();
    }

    public function test_staff_can_update_order_status_and_payment(): void
    {
        $order = $this->createOrder($this->customer);
        $payment = $this->createPayment($order);

        $this->actingAs($this->staff);

        $this->patch('/staff/orders/'.$order->id.'/status', ['order_status' => 'CONFIRMED'])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status' => 'CONFIRMED']);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => 'CONFIRMED',
            'changed_by' => $this->staff->id,
        ]);

        $this->patch('/staff/payments/'.$payment->id.'/status', ['status' => 'PAID'])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'PAID']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'PAID']);
    }

    public function test_staff_cancel_requires_reason(): void
    {
        $order = $this->createOrder($this->customer);

        $this->actingAs($this->staff);

        $this->patch('/staff/orders/'.$order->id.'/status', ['order_status' => 'CANCELLED'])
            ->assertSessionHasErrors('cancel_reason');
    }

    public function test_customer_cancel_restores_stock(): void
    {
        $order = $this->createOrder($this->customer);

        $this->actingAs($this->customer);

        $this->post('/customer/orders/'.$order->id.'/cancel')->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'CANCELLED',
            'stock_restored' => true,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 10,
        ]);
    }

    public function test_customer_cannot_view_others_order(): void
    {
        $other = User::factory()->create(['role' => 'CUSTOMER']);
        $order = $this->createOrder($other);

        $this->actingAs($this->customer);

        $this->get('/customer/orders/'.$order->id)->assertForbidden();
    }

    public function test_admin_can_block_user_and_toggle_review(): void
    {
        $order = $this->createOrder($this->customer);
        $review = Review::create([
            'product_id' => $this->product->id,
            'user_id' => $this->customer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Rất dễ thương',
            'is_hidden' => false,
        ]);

        $this->actingAs($this->admin);

        $this->patch('/admin/users/'.$this->customer->id.'/status', ['status' => 'BLOCKED'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $this->customer->id, 'status' => 'BLOCKED']);

        $this->patch('/admin/reviews/'.$review->id.'/toggle')->assertRedirect();

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'is_hidden' => true]);
    }

    public function test_role_middleware_blocks_customer_from_admin(): void
    {
        $this->actingAs($this->customer);

        $this->get('/admin')->assertForbidden();
    }

    private function createOrder(User $customer): Order
    {
        return app(OrderService::class)->createOrder([
            'customer_id' => $customer->id,
            'recipient_name' => $customer->full_name,
            'recipient_phone' => '0980000000',
            'recipient_address' => 'Hà Nội',
            'payment_method' => 'COD',
        ], [(object) [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]]);
    }

    private function createPayment(Order $order): Payment
    {
        return app(OrderService::class)->createPayment($order, [
            'method' => 'COD',
            'transaction_ref' => 'TXN-TEST',
        ]);
    }
}
