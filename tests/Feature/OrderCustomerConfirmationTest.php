<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use App\Presenters\CustomerOrderPresenter;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCustomerConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $staff;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'CUSTOMER']);
        $this->staff = User::factory()->create(['role' => 'STAFF']);

        $category = Category::create(['name' => 'Gấu Bông', 'is_active' => true]);
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu Dâu Lotso',
            'description' => 'Mềm mịn',
            'price' => 150000,
            'sale_price' => 150000,
            'stock_quantity' => 20,
            'status' => 'ACTIVE',
        ]);
    }

    private function createDeliveredOrder(): Order
    {
        $order = Order::create([
            'order_code' => 'TEST' . rand(10000, 99999),
            'customer_id' => $this->customer->id,
            'recipient_name' => 'Khách Hàng',
            'recipient_phone' => '0987654321',
            'recipient_address' => '123 Phố Huế, Hà Nội',
            'subtotal' => 150000,
            'discount_amount' => 0,
            'shipping_discount_amount' => 0,
            'shipping_fee' => 30000,
            'shipping_method' => 'STANDARD',
            'total_amount' => 180000,
            'order_status' => 'COMPLETED',
            'payment_method' => 'COD',
            'payment_status' => 'PAID',
            'customer_confirmed_at' => null,
            'completed_at' => now(),
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'product_price' => 150000,
            'line_total' => 150000,
        ]);

        return $order;
    }

    public function test_delivered_order_waiting_customer_confirmation_hides_review(): void
    {
        $order = $this->createDeliveredOrder();

        $this->assertTrue($order->isDeliveredWaitingConfirmation());
        $this->assertFalse($order->isCustomerConfirmed());

        $card = CustomerOrderPresenter::format($order);

        $this->assertTrue($card['actions']['confirmReceived']);
        $this->assertTrue($card['actions']['requestReturn']);
        $this->assertFalse($card['actions']['review']);
        $this->assertSame('Chờ bạn xác nhận', $card['order']['statusLabel']);

        // Check customer index view renders "Đã nhận được hàng" and "Trả hàng" but NOT "Đánh giá"
        $this->actingAs($this->customer);
        $response = $this->get(route('customer.orders.index'));
        $response->assertOk();
        $response->assertSee('Đã nhận được hàng');
        $response->assertSee('Trả hàng / Hoàn tiền');
        $response->assertDontSee(route('customer.orders.review', $order), false);
    }

    public function test_customer_can_confirm_received_and_unlocks_review(): void
    {
        $order = $this->createDeliveredOrder();

        $this->actingAs($this->customer);
        $response = $this->post(route('customer.orders.confirm_received', $order));
        $response->assertRedirect(route('customer.orders.review', $order->id));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertNotNull($order->customer_confirmed_at);
        $this->assertTrue($order->isCustomerConfirmed());
        $this->assertFalse($order->isDeliveredWaitingConfirmation());

        $card = CustomerOrderPresenter::format($order);
        $this->assertFalse($card['actions']['confirmReceived']);
        $this->assertFalse($card['actions']['requestReturn']);
        $this->assertTrue($card['actions']['review']);
        $this->assertSame('Hoàn thành', $card['order']['statusLabel']);

        // Now orders index shows "Đánh giá"
        $response = $this->get(route('customer.orders.index'));
        $response->assertOk();
        $response->assertSee(route('customer.orders.review', $order), false);
        $response->assertSee('Đánh giá');
    }

    public function test_customer_can_request_return_refund(): void
    {
        $order = $this->createDeliveredOrder();

        $this->actingAs($this->customer);
        $response = $this->post(route('customer.orders.request_return', $order), [
            'return_reason' => 'Hàng bị lỗi / hỏng hóc hoặc không đúng mô tả',
            'return_note' => 'Gấu bị rách chỉ ở tai',
            'refund_bank_name' => 'Vietcombank',
            'refund_bank_account' => '1234567890',
            'refund_account_holder' => 'NGUYEN VAN A',
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('PENDING', $order->return_request_status);
        $this->assertStringContainsString('Gấu bị rách chỉ ở tai', $order->return_request_reason);
        $this->assertNotNull($order->return_requested_at);
        $this->assertSame('Vietcombank', $order->refund_bank_name);

        $card = CustomerOrderPresenter::format($order);
        $this->assertFalse($card['actions']['requestReturn']);
        $this->assertTrue($card['actions']['hasPendingReturn']);
        $this->assertFalse($card['actions']['review']);
        $this->assertSame('Yêu cầu trả hàng', $card['order']['statusLabel']);
    }

    public function test_customer_order_review_route_redirects_to_reviews_index(): void
    {
        $order = $this->createDeliveredOrder();

        $this->actingAs($this->customer);
        $response = $this->get(route('customer.orders.review', $order));
        $response->assertRedirect(route('customer.reviews.index', [
            'order_id' => $order->id,
            'tab' => 'pending',
        ]));
    }
}
