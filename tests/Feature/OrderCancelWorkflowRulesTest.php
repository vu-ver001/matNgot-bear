<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentRefundRequest;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancelWorkflowRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $staff;
    private User $admin;
    private Product $product;
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'CUSTOMER']);
        $this->staff = User::factory()->create(['role' => 'STAFF']);
        $this->admin = User::factory()->create(['role' => 'ADMIN']);
        $category = Category::create(['name' => 'Gấu bông', 'is_active' => true]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu bông Teddy Workflow Test',
            'description' => 'Mô tả test',
            'price' => 200000,
            'sale_price' => 180000,
            'stock_quantity' => 10,
            'status' => 'ACTIVE',
        ]);

        $this->orderService = app(OrderService::class);
    }

    private function createTestOrder(string $paymentMethod = 'COD', string $paymentStatus = 'UNPAID', string $orderStatus = 'PENDING'): Order
    {
        $order = $this->orderService->createOrder([
            'customer_id' => $this->customer->id,
            'recipient_name' => $this->customer->full_name,
            'recipient_phone' => '0987654321',
            'recipient_address' => 'Hà Nội',
            'payment_method' => $paymentMethod,
        ], [(object) [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]]);

        $order->update([
            'order_status' => $orderStatus,
            'payment_status' => $paymentStatus,
        ]);

        if ($paymentStatus === 'PAID') {
            Payment::create([
                'order_id' => $order->id,
                'method' => $paymentMethod,
                'status' => 'PAID',
                'amount' => $order->total_amount,
                'paid_at' => now(),
            ]);
        }

        return $order->fresh();
    }

    /**
     * TH 1a: Đơn PENDING chưa thanh toán -> KH hủy trực tiếp ngay lập tức
     */
    public function test_pending_unpaid_order_can_be_cancelled_directly_by_customer(): void
    {
        $order = $this->createTestOrder('COD', 'UNPAID', 'PENDING');

        $this->assertTrue($order->canCancelDirectly());
        $this->assertFalse($order->canRequestCancel());

        $this->actingAs($this->customer);
        $response = $this->post(route('customer.orders.cancel', $order), [
            'reason' => 'Đổi ý không mua nữa',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('CANCELLED', $order->order_status);
        $this->assertSame($this->customer->id, $order->cancelled_by);
    }

    /**
     * TH 1b: Đơn PENDING thanh toán VNPay/QR đã trả tiền -> KH gửi yêu cầu hủy gửi thẳng Admin, tự động tạo PaymentRefundRequest
     */
    public function test_pending_paid_online_order_cancel_request_routed_directly_to_admin(): void
    {
        $order = $this->createTestOrder('E_WALLET', 'PAID', 'PENDING');

        $this->assertFalse($order->canCancelDirectly());
        $this->assertTrue($order->canRequestCancel());

        $this->actingAs($this->customer);
        $response = $this->post(route('customer.orders.request_cancel', $order), [
            'reason' => 'Đặt nhầm mẫu gấu, muốn hoàn tiền',
            'refund_bank_name' => 'Vietcombank',
            'refund_bank_account' => '0123456789',
            'refund_account_holder' => 'NGUYEN VAN A',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('PENDING', $order->order_status);
        $this->assertSame('PENDING', $order->cancel_request_status);
        $this->assertSame('0123456789', $order->refund_bank_account);

        // Kiểm tra tự động tạo PaymentRefundRequest gửi Admin
        $refundReq = PaymentRefundRequest::where('order_id', $order->id)->first();
        $this->assertNotNull($refundReq);
        $this->assertSame('PENDING', $refundReq->status);
        $this->assertSame((float) $order->total_amount, (float) $refundReq->amount);
        $this->assertSame($this->customer->id, $refundReq->requested_by);

        // Nhân viên KHÔNG được phép duyệt hoặc từ chối đơn PENDING này
        $this->actingAs($this->staff);
        $staffApprove = $this->post(route('staff.orders.approve_cancel', $order));
        $staffApprove->assertSessionHas('error');

        $staffReject = $this->post(route('staff.orders.reject_cancel', $order), [
            'rejection_reason' => 'Nhân viên muốn từ chối',
        ]);
        $staffReject->assertSessionHas('error');

        // Admin CÓ QUYỀN duyệt hủy đơn này và phê duyệt hoàn tiền
        $this->actingAs($this->admin);
        $adminApprove = $this->post(route('admin.orders.approve_cancel', $order), [
            'refund_note' => 'Admin đã chuyển tiền hoàn qua STK VCB',
        ]);
        $adminApprove->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('CANCELLED', $order->order_status);
        $this->assertSame('APPROVED', $order->cancel_request_status);

        $refundReq->refresh();
        $this->assertSame('APPROVED', $refundReq->status);
        $this->assertSame($this->admin->id, $refundReq->approved_by);
    }

    /**
     * TH 2a: Đơn PREPARING ("Đang chuẩn bị hàng") -> KH gửi yêu cầu hủy, Nhân viên duyệt hủy thành công
     */
    public function test_preparing_order_cancel_request_can_be_approved_by_staff(): void
    {
        $order = $this->createTestOrder('COD', 'UNPAID', 'PREPARING');

        $this->assertFalse($order->canCancelDirectly());
        $this->assertTrue($order->canRequestCancel());

        $this->actingAs($this->customer);
        $response = $this->post(route('customer.orders.request_cancel', $order), [
            'reason' => 'Trùng đơn hàng, xin hủy đơn',
        ]);
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('PREPARING', $order->order_status);
        $this->assertSame('PENDING', $order->cancel_request_status);

        // Nhân viên kiểm tra đơn chưa gửi vận chuyển -> Duyệt hủy thành công
        $this->actingAs($this->staff);
        $staffApprove = $this->post(route('staff.orders.approve_cancel', $order));
        $staffApprove->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('CANCELLED', $order->order_status);
        $this->assertSame('APPROVED', $order->cancel_request_status);
        $this->assertSame($this->staff->id, $order->cancelled_by);
    }

    /**
     * TH 2b: Đơn PREPARING ("Đang chuẩn bị hàng") -> Đã giao cho đơn vị vận chuyển nên nhân viên Từ chối hủy
     */
    public function test_preparing_order_cancel_request_can_be_rejected_by_staff_when_already_handed_to_carrier(): void
    {
        $order = $this->createTestOrder('COD', 'UNPAID', 'PREPARING');

        $this->actingAs($this->customer);
        $this->post(route('customer.orders.request_cancel', $order), [
            'reason' => 'Đổi ý không muốn mua nữa',
        ]);

        $order->refresh();
        $this->assertSame('PENDING', $order->cancel_request_status);

        // Nhân viên đã giao cho bên vận chuyển nhưng chưa kịp cập nhật trạng thái -> Bấm Từ chối hủy kèm lý do
        $this->actingAs($this->staff);
        $staffReject = $this->post(route('staff.orders.reject_cancel', $order), [
            'rejection_reason' => 'Đơn hàng đã được bàn giao cho đơn vị vận chuyển, không thể hủy đơn.',
        ]);
        $staffReject->assertSessionHas('success');

        $order->refresh();
        // Đơn hàng vẫn tiếp tục ở trạng thái PREPARING
        $this->assertSame('PREPARING', $order->order_status);
        $this->assertSame('REJECTED', $order->cancel_request_status);
        $this->assertSame('Đơn hàng đã được bàn giao cho đơn vị vận chuyển, không thể hủy đơn.', $order->cancel_rejection_reason);
    }

    /**
     * TH 3: Khách hàng ở trang thanh toán online chọn Quay lại trang chủ (Hủy đơn)
     */
    public function test_unpaid_online_order_cancelled_from_payment_page_via_json_redirects_home(): void
    {
        $order = $this->createTestOrder('BANK_TRANSFER', 'UNPAID', 'PENDING');

        $this->actingAs($this->customer);
        $response = $this->postJson(route('customer.orders.cancel', $order), [
            'reason' => 'Khách hàng hủy đơn khi đang ở trang thanh toán online',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'redirect_url' => route('home'),
        ]);

        $order->refresh();
        $this->assertSame('CANCELLED', $order->order_status);
        $this->assertSame($this->customer->id, $order->cancelled_by);
    }

    public function test_unpaid_online_order_cancelled_from_payment_page_via_redirect_param(): void
    {
        $order = $this->createTestOrder('CARD', 'UNPAID', 'PENDING');

        $this->actingAs($this->customer);
        $response = $this->post(route('customer.orders.cancel', $order), [
            'reason' => 'Khách hàng hủy đơn khi đang ở trang thanh toán online',
            'redirect_to' => 'home',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('CANCELLED', $order->order_status);
    }

    /**
     * TH 4: Nhân viên từ chối đơn hàng đang chờ xác nhận (PENDING) với lý do bắt buộc.
     * Kiểm tra trạng thái sang CANCELLED, hoàn lại tồn kho, lưu lý do và ghi nhận lịch sử.
     */
    public function test_staff_can_reject_pending_order_with_reason(): void
    {
        $initialStock = $this->product->fresh()->stock_quantity; // Ban đầu sau khi tạo đơn
        $order = $this->createTestOrder('COD', 'UNPAID', 'PENDING');
        $this->product->decrement('stock_quantity', 1);

        $this->actingAs($this->staff);

        // Trường hợp không nhập lý do -> Bị lỗi validation
        $failResponse = $this->post(route('staff.orders.reject', $order), [
            'reject_reason' => '',
        ]);
        $failResponse->assertSessionHasErrors('reject_reason');
        $this->assertSame('PENDING', $order->fresh()->order_status);

        // Nhập lý do hợp lệ
        $rejectReason = 'Sản phẩm còn lại trong kho bị lỗi kiểm định chất lượng (dính bẩn/rách), shop xin phép từ chối để đảm bảo quyền lợi cho bạn';
        $response = $this->post(route('staff.orders.reject', $order), [
            'reject_reason' => $rejectReason,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('CANCELLED', $order->order_status);
        $this->assertStringContainsString($rejectReason, $order->cancel_reason);
        $this->assertSame($this->staff->id, $order->cancelled_by);

        // Kiểm tra tồn kho được hoàn lại
        $this->assertTrue($order->stock_restored);

        // Kiểm tra trang chi tiết đơn hàng của khách hàng hiển thị rõ ràng lý do từ chối
        $this->actingAs($this->customer);
        $customerViewResponse = $this->get(route('customer.orders.show', $order));
        $customerViewResponse->assertOk();
        $customerViewResponse->assertSee('Đơn hàng đã bị Cửa hàng từ chối tiếp nhận');
        $customerViewResponse->assertSee($rejectReason);
    }

    /**
     * TH 5: Đơn hàng thanh toán online (PAID) bị shop từ chối -> tự động tạo PaymentRefundRequest
     * Khách hàng có thể tự nhập STK nhận tiền hoàn qua trang chi tiết đơn hàng.
     * Nhân viên cũng có thể cập nhật STK cho khách nếu khách báo qua hotline.
     */
    public function test_paid_order_rejected_by_staff_allows_customer_and_staff_to_update_bank_account(): void
    {
        $order = $this->createTestOrder('BANK_TRANSFER', 'PAID', 'PENDING');

        // 1. Staff từ chối đơn
        $this->actingAs($this->staff);
        $rejectResponse = $this->post(route('staff.orders.reject', $order), [
            'reject_reason' => 'Không đủ số lượng giao kịp cho khách',
        ]);
        $rejectResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('CANCELLED', $order->order_status);

        // Kiểm tra đã tự động tạo PaymentRefundRequest
        $refundReq = \App\Models\PaymentRefundRequest::where('order_id', $order->id)->first();
        $this->assertNotNull($refundReq);
        $this->assertSame('PENDING', $refundReq->status);
        $this->assertNull($order->refund_bank_account);

        // 2. Khách hàng vào trang đơn hàng và nhập thông tin STK
        $this->actingAs($this->customer);
        $customerUpdate = $this->post(route('customer.orders.update_refund_account', $order), [
            'refund_bank_name' => 'Vietcombank',
            'refund_bank_account' => '0123456789',
            'refund_account_holder' => 'NGUYEN VAN A',
        ]);
        $customerUpdate->assertSessionHas('success');

        $order->refresh();
        $refundReq->refresh();
        $this->assertSame('Vietcombank', $order->refund_bank_name);
        $this->assertSame('0123456789', $order->refund_bank_account);
        $this->assertSame('NGUYEN VAN A', $order->refund_account_holder);
        $this->assertSame('0123456789', $refundReq->bank_account);

        // 3. Nhân viên gọi cho khách xác minh và có thể điều chỉnh lại STK nếu khách báo sai
        $this->actingAs($this->staff);
        $staffUpdate = $this->post(route('staff.orders.update_refund_account', $order), [
            'refund_bank_name' => 'MB Bank',
            'refund_bank_account' => '999988887777',
            'refund_account_holder' => 'NGUYEN VAN A',
        ]);
        $staffUpdate->assertSessionHas('success');

        $order->refresh();
        $refundReq->refresh();
        $this->assertSame('MB Bank', $order->refund_bank_name);
        $this->assertSame('999988887777', $order->refund_bank_account);
        $this->assertSame('999988887777', $refundReq->bank_account);
    }
}

