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

    public function test_shared_order_pages_keep_role_specific_actions(): void
    {
        $order = $this->createOrder($this->customer);
        $payment = $this->createPayment($order);
        $order->update(['payment_method' => 'BANK_TRANSFER', 'payment_status' => 'UNPAID']);

        $this->actingAs($this->staff);
        $this->get(route('staff.orders.index'))
            ->assertOk()
            ->assertSee(route('staff.orders.show', $order), false)
            ->assertSee($order->recipient_phone)
            ->assertDontSee(route('customer.payment.retry', $order), false);
        $this->get(route('staff.orders.show', $order))
            ->assertOk()
            ->assertSee('Thông tin nhận hàng')
            ->assertSee('Sản phẩm đã đặt')
            ->assertSee(route('staff.orders.updateStatus', $order), false)
            ->assertSee(route('staff.payments.updateStatus', $payment), false)
            ->assertDontSee(route('customer.orders.update_shipping_address', $order), false)
            ->assertDontSee(route('customer.orders.cancel', $order), false)
            ->assertDontSee(route('customer.payment.retry', $order), false);

        $this->actingAs($this->customer);
        $this->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee(route('customer.orders.show', $order), false)
            ->assertSee(route('customer.payment.retry', $order), false)
            ->assertSee('name="search"', false);
        $this->get(route('customer.orders.show', $order))
            ->assertOk()
            ->assertSee('Thông tin nhận hàng')
            ->assertSee('Sản phẩm đã đặt')
            ->assertSee(route('customer.orders.update_shipping_address', $order), false)
            ->assertSee(route('customer.orders.cancel', $order), false)
            ->assertSee(route('customer.payment.retry', $order), false)
            ->assertDontSee(route('staff.orders.updateStatus', $order), false)
            ->assertDontSee(route('staff.payments.updateStatus', $payment), false);

        $order->update(['order_status' => 'SHIPPING']);
        $this->get(route('customer.orders.show', $order))
            ->assertOk()
            ->assertSee(route('customer.orders.complete', $order), false)
            ->assertDontSee(route('customer.orders.update_shipping_address', $order), false);
        $this->actingAs($this->staff)->get(route('staff.orders.show', $order))
            ->assertOk()
            ->assertDontSee(route('customer.orders.complete', $order), false);
    }

    public function test_shared_order_list_preserves_customer_scope_and_staff_filters(): void
    {
        $mine = $this->createOrder($this->customer);
        $other = $this->createOrder(User::factory()->create(['role' => 'CUSTOMER']));
        $other->update(['order_status' => 'CONFIRMED', 'payment_status' => 'PAID']);

        $this->actingAs($this->customer)->get(route('customer.orders.index'))
            ->assertOk()->assertSee($mine->order_code)->assertDontSee($other->order_code)
            ->assertViewHas('stats', fn ($stats) => $stats['total'] === 1 && $stats['pending'] === 1);
        $this->get(route('customer.orders.index', ['search' => $other->order_code]))
            ->assertOk()->assertDontSee('href="'.route('customer.orders.show', $other).'"', false)
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 0);
        $this->get(route('customer.orders.index', ['payment_status' => 'PAID']))
            ->assertOk()->assertViewHas('orders', fn ($orders) => $orders->total() === 0);
        $response = $this->actingAs($this->staff)->get(route('staff.orders.index', [
            'search' => $other->order_code,
            'payment_status' => 'PAID',
            'order_status' => 'CONFIRMED',
        ]))->assertOk()->assertSee($other->order_code)->assertDontSee($mine->order_code);
        $response->assertSee(route('staff.orders.index', [
            'search' => $other->order_code,
            'payment_status' => 'PAID',
            'order_status' => 'SHIPPING',
        ]));
        $this->get(route('staff.orders.index', ['search' => 'NO-MATCH']))
            ->assertOk()->assertSee('Không tìm thấy đơn hàng nào phù hợp với điều kiện lọc.');
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

    public function test_invalid_status_transition_is_blocked(): void
    {
        $order = $this->createOrder($this->customer);

        $this->actingAs($this->staff);

        $this->patch('/staff/orders/'.$order->id.'/status', ['order_status' => 'PREPARING'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status' => 'PENDING']);

        $this->patch('/staff/orders/'.$order->id.'/status', ['order_status' => 'CANCELLED', 'cancel_reason' => 'Hết hàng'])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status' => 'CANCELLED']);

        $this->patch('/staff/orders/'.$order->id.'/status', ['order_status' => 'CONFIRMED', 'cancel_reason' => 'Lý do'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status' => 'CANCELLED']);
    }

    public function test_full_order_flow_reaches_completed(): void
    {
        $order = $this->createOrder($this->customer);
        $payment = $this->createPayment($order);

        $this->actingAs($this->staff);

        foreach (['CONFIRMED', 'PREPARING', 'SHIPPING', 'COMPLETED'] as $status) {
            $this->patch('/staff/orders/'.$order->id.'/status', ['order_status' => $status])
                ->assertRedirect();
        }

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status' => 'COMPLETED']);
        $this->assertDatabaseHas('products', ['id' => $this->product->id, 'sold_count' => 2]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'PAID']);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'PAID']);
    }

    public function test_completed_order_can_be_returned_and_refunded(): void
    {
        $order = $this->createOrder($this->customer);
        $payment = $this->createPayment($order);

        $this->actingAs($this->staff);

        foreach (['CONFIRMED', 'PREPARING', 'SHIPPING', 'COMPLETED'] as $status) {
            $this->patch('/staff/orders/'.$order->id.'/status', ['order_status' => $status])
                ->assertRedirect();
        }

        $this->patch('/staff/orders/'.$order->id.'/status', ['order_status' => 'RETURNED'])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'RETURNED',
            'payment_status' => 'REFUNDED',
        ]);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'REFUNDED']);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => 'RETURNED',
        ]);
    }

    public function test_customer_cannot_cancel_confirmed_order(): void
    {
        $order = $this->createOrder($this->customer);
        $this->createPayment($order);

        $this->actingAs($this->staff);
        $this->patch('/staff/orders/'.$order->id.'/status', ['order_status' => 'CONFIRMED'])
            ->assertRedirect();

        $this->actingAs($this->customer);

        $this->post('/customer/orders/'.$order->id.'/cancel')->assertSessionHas('error');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status' => 'CONFIRMED']);
    }

    public function test_payment_refund_requires_paid_payment(): void
    {
        $order = $this->createOrder($this->customer);
        $payment = $this->createPayment($order);

        $this->actingAs($this->staff);

        $this->patch('/staff/payments/'.$payment->id.'/status', ['status' => 'REFUNDED'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'PENDING']);

        $this->patch('/staff/payments/'.$payment->id.'/status', ['status' => 'PAID'])
            ->assertRedirect();

        $this->patch('/staff/payments/'.$payment->id.'/status', ['status' => 'PAID'])
            ->assertSessionHas('error');

        $this->patch('/staff/payments/'.$payment->id.'/status', ['status' => 'REFUNDED'])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'REFUNDED']);
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

    public function test_customer_can_only_reorder_a_terminal_order(): void
    {
        $order = $this->createOrder($this->customer);

        $this->actingAs($this->customer)
            ->post(route('customer.orders.reorder', $order))
            ->assertSessionHas('error');
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]);

        $order->update(['order_status' => 'COMPLETED']);
        $this->post(route('customer.orders.reorder', $order))->assertRedirect(route('customer.cart'));
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_customer_payment_action_is_limited_to_an_active_prepaid_order(): void
    {
        $order = $this->createOrder($this->customer);
        $order->update([
            'payment_method' => 'BANK_TRANSFER',
            'payment_status' => 'UNPAID',
        ]);

        $this->assertTrue($order->fresh()->canPayOnline());

        $order->update(['order_status' => 'COMPLETED']);
        $this->assertFalse($order->fresh()->canPayOnline());

        $order->update([
            'order_status' => 'SHIPPING',
            'payment_method' => 'COD',
            'payment_status' => 'UNPAID',
        ]);
        $this->assertFalse($order->fresh()->canPayOnline());
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

    public function test_admin_revenue_report_renders_and_filters_by_date(): void
    {
        $this->actingAs($this->admin);

        $this->get('/admin/reports/revenue')->assertOk();
        $this->get('/admin/reports/revenue?from_date=2026-01-01&to_date=2026-12-31')->assertOk();
        $this->get('/admin/reports/revenue?from_date=2026-12-31&to_date=2026-01-01')->assertSessionHasErrors('to_date');
    }

    public function test_revenue_report_only_counts_completed_paid_orders_in_range(): void
    {
        $this->actingAs($this->admin);

        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        $inRange = $this->createOrder($this->customer);
        $inRange->update(['created_at' => $from->copy()->addDays(1)]);
        $this->advanceOrderToCompleted($inRange);

        $otherStatus = $this->createOrder($this->customer);
        $this->advanceOrderToCompleted($otherStatus);
        $otherStatus->update(['payment_status' => 'PENDING']);

        $outside = $this->createOrder($this->customer);
        $outside->update(['created_at' => $from->copy()->subMonth(2)]);
        $this->advanceOrderToCompleted($outside);

        $this->get('/admin/reports/revenue?from_date='.$from->format('Y-m-d').'&to_date='.$to->format('Y-m-d'))
            ->assertOk()
            ->assertSee('Doanh thu theo ngày')
            ->assertSee($inRange->order_code);
    }

    public function test_admin_can_export_revenue_csv(): void
    {
        $this->actingAs($this->admin);

        $order = $this->createOrder($this->customer);
        $this->advanceOrderToCompleted($order);

        $from = now()->startOfMonth()->format('Y-m-d');
        $to = now()->endOfMonth()->format('Y-m-d');

        $response = $this->get("/admin/reports/revenue/export?from_date={$from}&to_date={$to}")
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString($order->order_code, $response->streamedContent());
    }

    public function test_stale_order_cannot_complete_or_restore_stock_twice(): void
    {
        $service = app(OrderService::class);
        $order = $this->createOrder($this->customer);
        $order->update(['order_status' => 'SHIPPING']);
        $stale = $order->fresh();
        $service->updateStatus($order, 'COMPLETED', $this->staff->id);
        try {
            $service->updateStatus($stale, 'COMPLETED', $this->staff->id);
            $this->fail('Duplicate completion was accepted.');
        } catch (\Exception $e) {
            $this->assertSame('Đơn hàng đã ở trạng thái này rồi.', $e->getMessage());
        }
        $this->assertSame(2, $this->product->fresh()->sold_count);
        $this->assertSame(1, $order->statusHistories()->where('to_status', 'COMPLETED')->count());

        $cancelled = $this->createOrder($this->customer);
        $stale = $cancelled->fresh();
        $service->cancelOrder($cancelled, $this->customer->id, 'Hủy');
        try {
            $service->cancelOrder($stale, $this->customer->id, 'Hủy');
            $this->fail('Duplicate cancellation was accepted.');
        } catch (\Exception $e) {
            $this->assertSame('Bạn chỉ có thể hủy đơn hàng đang chờ xác nhận.', $e->getMessage());
        }
        $this->assertSame(8, $this->product->fresh()->stock_quantity);
        $this->assertSame(1, $cancelled->statusHistories()->where('to_status', 'CANCELLED')->count());
    }

    public function test_bulk_shipping_obeys_single_order_transitions(): void
    {
        $pending = $this->createOrder($this->customer);
        $ready = $this->createOrder($this->customer);
        $ready->update(['order_status' => 'PREPARING']);
        foreach ([$this->admin, $this->staff] as $user) {
            $ready->refresh()->update(['order_status' => 'PREPARING']);
            $prefix = strtolower($user->role);
            $this->actingAs($user)->post('/'.$prefix.'/orders/bulk-update-status', [
                'order_ids' => [$pending->id, $ready->id], 'target_status' => 'SHIPPING',
            ])->assertSessionHas('success');
            $this->assertSame('PENDING', $pending->fresh()->order_status);
            $this->assertSame('SHIPPING', $ready->fresh()->order_status);
        }
    }

    public function test_confirmed_order_must_be_prepared_before_shipping(): void
    {
        $order = $this->createOrder($this->customer);
        $service = app(OrderService::class);
        $service->updateStatus($order, 'CONFIRMED', $this->staff->id);

        try {
            $service->updateStatus($order, 'SHIPPING', $this->staff->id);
            $this->fail('A confirmed order skipped the preparing state.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Không thể chuyển đơn hàng', $e->getMessage());
        }

        $this->assertSame('CONFIRMED', $order->fresh()->order_status);
    }

    public function test_prepaid_order_must_be_paid_before_shipping(): void
    {
        $order = $this->createOrder($this->customer);
        $order->update([
            'order_status' => 'PREPARING',
            'payment_method' => 'BANK_TRANSFER',
            'payment_status' => 'UNPAID',
        ]);

        $this->actingAs($this->staff)
            ->patch('/staff/orders/'.$order->id.'/status', ['order_status' => 'SHIPPING'])
            ->assertSessionHas('error', 'Đơn thanh toán trước phải được xác nhận đã thanh toán trước khi giao hàng.');
        $this->assertSame('PREPARING', $order->fresh()->order_status);

        $order->update(['payment_status' => 'PAID']);
        $this->patch('/staff/orders/'.$order->id.'/status', ['order_status' => 'SHIPPING'])
            ->assertSessionHas('success');
        $this->assertSame('SHIPPING', $order->fresh()->order_status);
    }

    public function test_admin_and_staff_cards_only_show_shipping_controls_when_eligible(): void
    {
        $order = $this->createOrder($this->customer);
        $order->update([
            'order_status' => 'PREPARING',
            'payment_method' => 'BANK_TRANSFER',
            'payment_status' => 'UNPAID',
        ]);
        $order->load('details.product.images');

        foreach ([['admin.orders', false], ['staff.orders', true]] as [$routePrefix, $isStaff]) {
            $html = view('orders.partials.staff-order-card', compact('order', 'routePrefix', 'isStaff'))->render();
            $this->assertStringNotContainsString('title="Chọn đơn để giao hàng"', $html);
            $this->assertStringNotContainsString('> Bắt đầu giao hàng', $html);
            $this->assertStringContainsString('Chờ thanh toán', $html);
        }

        $order->update(['payment_status' => 'PAID']);
        foreach ([['admin.orders', false], ['staff.orders', true]] as [$routePrefix, $isStaff]) {
            $html = view('orders.partials.staff-order-card', compact('order', 'routePrefix', 'isStaff'))->render();
            $this->assertStringContainsString('title="Chọn đơn để giao hàng"', $html);
            $this->assertStringContainsString('> Bắt đầu giao hàng', $html);
        }

        $order->update(['order_status' => 'CONFIRMED']);
        $html = view('orders.partials.staff-order-card', [
            'order' => $order, 'routePrefix' => 'staff.orders', 'isStaff' => true,
        ])->render();
        $this->assertStringContainsString('Chuẩn bị hàng', $html);
        $this->assertStringNotContainsString('> Bắt đầu giao hàng', $html);
    }

    public function test_manual_shipping_method_and_start_time_are_recorded(): void
    {
        $order = app(OrderService::class)->createOrder([
            'customer_id' => $this->customer->id,
            'recipient_name' => $this->customer->full_name,
            'recipient_phone' => '0980000000',
            'recipient_address' => 'Hà Nội',
            'payment_method' => 'COD',
            'shipping_method' => 'express',
        ], [(object) [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]]);

        $this->assertSame('express', $order->shipping_method);
        $this->assertSame('Giao hàng hỏa tốc', $order->shipping_method_label);

        $this->actingAs($this->staff);
        foreach (['CONFIRMED', 'PREPARING', 'SHIPPING'] as $status) {
            $this->patch('/staff/orders/'.$order->id.'/status', ['order_status' => $status])
                ->assertRedirect();
        }

        $order->refresh();
        $this->assertNotNull($order->shipped_at);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => 'SHIPPING',
            'note' => 'Shop bắt đầu giao hàng thủ công.',
        ]);
    }

    public function test_shipping_order_can_only_be_cancelled_after_stock_returns(): void
    {
        $order = $this->createOrder($this->customer);
        $service = app(OrderService::class);
        foreach (['CONFIRMED', 'PREPARING', 'SHIPPING'] as $status) {
            $service->updateStatus($order, $status, $this->staff->id);
        }

        $this->actingAs($this->staff)
            ->patch('/staff/orders/'.$order->id.'/status', [
                'order_status' => 'CANCELLED',
                'cancel_reason' => 'Khách không nhận hàng',
            ])
            ->assertSessionHas('error', 'Chỉ được hủy đơn đang giao sau khi xác nhận hàng đã quay lại kho.');

        $this->assertSame('SHIPPING', $order->fresh()->order_status);
        $this->assertSame(8, $this->product->fresh()->stock_quantity);

        $this->patch('/staff/orders/'.$order->id.'/status', [
            'order_status' => 'CANCELLED',
            'cancel_reason' => 'Khách không nhận hàng',
            'stock_returned' => true,
        ])->assertSessionHas('success');

        $this->assertSame('CANCELLED', $order->fresh()->order_status);
        $this->assertSame(10, $this->product->fresh()->stock_quantity);
    }

    public function test_customer_receipt_requires_shipping_and_does_not_confirm_bank_transfer(): void
    {
        $order = $this->createOrder($this->customer);
        $order->update(['payment_method' => 'BANK_TRANSFER']);
        $payment = app(OrderService::class)->createPayment($order, ['method' => 'BANK_TRANSFER']);
        $this->actingAs($this->customer);
        foreach (['PENDING', 'CONFIRMED', 'PREPARING'] as $status) {
            $order->update(['order_status' => $status]);
            $this->post(route('customer.orders.complete', $order))->assertSessionHas('error');
            $this->assertSame($status, $order->fresh()->order_status);
        }
        $order->update(['order_status' => 'SHIPPING']);
        $this->post(route('customer.orders.complete', $order))->assertSessionHas('success');
        $this->assertSame('COMPLETED', $order->fresh()->order_status);
        $this->assertSame('UNPAID', $order->fresh()->payment_status);
        $this->assertSame('PENDING', $payment->fresh()->status);
        $this->post(route('customer.orders.complete', $order))->assertSessionHas('error');
        $this->assertSame(2, $this->product->fresh()->sold_count);
    }

    public function test_status_forms_only_offer_allowed_transitions(): void
    {
        $order = $this->createOrder($this->customer);
        foreach ([$this->admin, $this->staff] as $user) {
            $response = $this->actingAs($user)->get('/'.strtolower($user->role).'/orders/'.$order->id)->assertOk();
            preg_match('/<select name="order_status".*?<\/select>/s', $response->getContent(), $matches);
            $this->assertNotEmpty($matches);
            $this->assertStringContainsString('value="CONFIRMED"', $matches[0]);
            $this->assertStringContainsString('value="CANCELLED"', $matches[0]);
            $this->assertStringNotContainsString('value="COMPLETED"', $matches[0]);
            $this->assertStringNotContainsString('value="PENDING"', $matches[0]);
        }
    }

    public function test_customer_receipt_confirms_cod_payment_once(): void
    {
        $order = $this->createOrder($this->customer);
        $payment = $this->createPayment($order);
        $order->update(['order_status' => 'SHIPPING']);
        $this->actingAs($this->customer)
            ->post(route('customer.orders.complete', $order))->assertSessionHas('success');
        $this->assertSame('PAID', $order->fresh()->payment_status);
        $this->assertSame('PAID', $payment->fresh()->status);
        $this->assertSame(1, $order->payments()->count());
        $this->post(route('customer.orders.complete', $order))->assertSessionHas('error');
        $this->assertSame(2, $this->product->fresh()->sold_count);
    }

    public function test_customer_cancellation_rechecks_persisted_status(): void
    {
        $order = $this->createOrder($this->customer);
        $stale = $order->fresh();
        app(OrderService::class)->updateStatus($order, 'CONFIRMED', $this->staff->id);
        try {
            app(OrderService::class)->cancelOrder($stale, $this->customer->id, 'Hủy');
            $this->fail('A confirmed order was cancelled by the customer.');
        } catch (\Exception $e) {
            $this->assertSame('Bạn chỉ có thể hủy đơn hàng đang chờ xác nhận.', $e->getMessage());
        }
        $this->assertSame('CONFIRMED', $order->fresh()->order_status);
        $this->assertSame(8, $this->product->fresh()->stock_quantity);
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

    private function advanceOrderToCompleted(Order $order): void
    {
        $this->createPayment($order);

        foreach (['CONFIRMED', 'PREPARING', 'SHIPPING', 'COMPLETED'] as $status) {
            app(OrderService::class)->updateStatus($order, $status, $this->admin->id, null);
        }
    }
}
