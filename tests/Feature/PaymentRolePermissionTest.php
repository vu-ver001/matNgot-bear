<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentRefundRequest;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test_perm@matngotbear.com'],
            [
                'full_name' => 'Admin Boss',
                'password' => bcrypt('password123'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $this->staff = User::firstOrCreate(
            ['email' => 'staff_test_perm@matngotbear.com'],
            [
                'full_name' => 'Staff Worker',
                'password' => bcrypt('password123'),
                'role' => User::ROLE_STAFF,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'customer_test_perm@matngotbear.com'],
            [
                'full_name' => 'Customer VIP',
                'password' => bcrypt('password123'),
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );
    }

    private function createDummyOrder(): Order
    {
        return Order::create([
            'order_code' => 'TEST'.strtoupper(uniqid()),
            'customer_id' => $this->customer->id,
            'recipient_name' => 'Người Nhận Test',
            'recipient_phone' => '0987654321',
            'recipient_address' => 'Hà Nội',
            'subtotal' => 200000,
            'shipping_fee' => 30000,
            'total_amount' => 230000,
            'order_status' => 'CONFIRMED',
            'payment_method' => 'COD',
            'payment_status' => 'UNPAID',
        ]);
    }

    /**
     * Quy định 1 & 6: Staff xem trang thanh toán chỉ trong ngày, không thấy lợi nhuận, không export báo cáo tài chính
     */
    public function test_staff_can_view_payments_today_only()
    {
        $response = $this->actingAs($this->staff)->get(route('staff.payments.index'));
        $response->assertStatus(200);
        $response->assertSee('Giao Dịch Hôm Nay');
        $response->assertDontSee('Xuất Báo Cáo');
    }

    /**
     * Quy định 5: Staff bị khóa hoàn toàn chức năng Cấu hình cổng & API thanh toán (403)
     */
    public function test_staff_cannot_access_payment_settings()
    {
        $response = $this->actingAs($this->staff)->get(route('staff.payments.settings'));
        $response->assertStatus(403);
    }

    /**
     * Quy định 2: Staff xác nhận thủ công BẮT BUỘC nhập lý do và tải ảnh bill
     */
    public function test_staff_manual_confirm_requires_reason_and_proof_image()
    {
        Storage::fake('public');
        $order = $this->createDummyOrder();
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'BANK_TRANSFER',
            'status' => 'PENDING',
            'amount' => 230000,
        ]);

        // Thử không nhập lý do & không có ảnh -> Phải lỗi validate
        $response = $this->actingAs($this->staff)->post(route('staff.payments.manualConfirm', $payment->id), []);
        $response->assertSessionHasErrors(['reason', 'proof_image']);

        // Gửi đầy đủ lý do & ảnh bill
        $file = UploadedFile::fake()->image('bill_ck.jpg');
        $validResponse = $this->actingAs($this->staff)->post(route('staff.payments.manualConfirm', $payment->id), [
            'reason' => 'Khách đã chuyển khoản có bill xác nhận từ Techcombank',
            'proof_image' => $file,
        ]);

        $validResponse->assertRedirect();
        $validResponse->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals('PAID', $payment->status);
        $this->assertEquals($this->staff->id, $payment->confirmed_by);
        $this->assertNotNull($payment->proof_image);
        $this->assertEquals('Khách đã chuyển khoản có bill xác nhận từ Techcombank', $payment->note);
    }

    /**
     * Quy định 3: Staff tạo yêu cầu hoàn tiền (Refund request)
     */
    public function test_staff_can_request_refund()
    {
        $order = $this->createDummyOrder();
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'BANK_TRANSFER',
            'status' => 'PAID',
            'amount' => 230000,
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->staff)->post(route('staff.payments.requestRefund', $payment->id), [
            'amount' => 230000,
            'reason' => 'Khách hàng hoàn hàng do nhận sai mẫu gấu',
            'bank_name' => 'MB',
            'bank_account' => '0987654321',
            'account_holder' => 'NGUYEN VAN A',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_refund_requests', [
            'payment_id' => $payment->id,
            'requested_by' => $this->staff->id,
            'amount' => 230000,
            'status' => 'PENDING',
        ]);
    }

    /**
     * Quy định 4: Staff đối soát COD và tải bảng kê
     */
    public function test_staff_can_reconcile_cod_and_export_cod_sheet()
    {
        $order = $this->createDummyOrder();
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'COD',
            'status' => 'PENDING',
            'amount' => 230000,
        ]);

        $response = $this->actingAs($this->staff)->post(route('staff.payments.reconcileCod', $payment->id));
        $response->assertRedirect();

        $payment->refresh();
        $this->assertNotNull($payment->cod_reconciled_at);
        $this->assertEquals($this->staff->id, $payment->cod_reconciled_by);

        // Tải bảng kê COD
        $exportResponse = $this->actingAs($this->staff)->get(route('staff.payments.codExport'));
        $exportResponse->assertStatus(200);
    }

    /**
     * Quy định 1 & 6 (Admin): Admin xem toàn bộ dòng tiền và xuất CSV
     */
    public function test_admin_can_view_full_kpi_and_export()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.payments.index'));
        $response->assertStatus(200);
        $response->assertSee('Thực Thu Thành Công');
        $response->assertSee('Xuất Báo Cáo');

        $exportResponse = $this->actingAs($this->admin)->get(route('admin.payments.export'));
        $exportResponse->assertStatus(200);
    }

    /**
     * Quy định 3 (Admin): Admin duyệt yêu cầu hoàn tiền
     */
    public function test_admin_can_approve_refund_request()
    {
        $order = $this->createDummyOrder();
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'BANK_TRANSFER',
            'status' => 'PAID',
            'amount' => 230000,
            'paid_at' => now(),
        ]);

        $refundReq = PaymentRefundRequest::create([
            'payment_id' => $payment->id,
            'order_id' => $order->id,
            'requested_by' => $this->staff->id,
            'amount' => 230000,
            'reason' => 'Khách hàng đổi mẫu',
            'bank_name' => 'MB',
            'bank_account' => '0377466205',
            'account_holder' => 'NGUYEN VAN B',
            'status' => 'PENDING',
        ]);

        $this->assertNotNull($refundReq->viet_qr_url);

        $response = $this->actingAs($this->admin)->post(route('admin.payments.approveRefund', $refundReq->id), [
            'admin_note' => 'Admin đã quét mã VietQR và chuyển tiền xong',
        ]);

        $response->assertRedirect();
        $refundReq->refresh();
        $payment->refresh();
        $order->refresh();

        $this->assertEquals('APPROVED', $refundReq->status);
        $this->assertEquals($this->admin->id, $refundReq->approved_by);
        $this->assertEquals('REFUNDED', $payment->status);
        $this->assertEquals('REFUNDED', $order->payment_status);
    }

    /**
     * Quy định 4 (Admin): Admin chốt nhận tiền COD về tài khoản
     */
    public function test_admin_can_mark_cod_settled()
    {
        $order = $this->createDummyOrder();
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'COD',
            'status' => 'PAID',
            'amount' => 230000,
            'cod_reconciled_at' => now(),
            'cod_reconciled_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.payments.markCodSettled', $payment->id));
        $response->assertRedirect();

        $payment->refresh();
        $this->assertNotNull($payment->cod_settled_at);
        $this->assertEquals($this->admin->id, $payment->cod_settled_by);
    }

    /**
     * Quy định 5 (Admin): Admin cấu hình STK ngân hàng và SePAY
     */
    public function test_admin_can_save_payment_settings()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.payments.settings'));
        $response->assertStatus(200);
        $response->assertSee('Cấu Hình Cổng & API Thanh Toán');

        $saveResponse = $this->actingAs($this->admin)->post(route('admin.payments.saveSettings'), [
            'vietqr_bank_code' => 'VCB',
            'vietqr_bank_name' => 'Vietcombank',
            'vietqr_account_number' => '9998887776',
            'vietqr_account_name' => 'NGUYEN NGOC ANH',
            'sepay_api_key' => 'NEW_SEPAY_KEY_123',
            'sepay_webhook_token' => 'SECRET_TOKEN_456',
            'sepay_active' => 1,
        ]);

        $saveResponse->assertRedirect();
        $saveResponse->assertSessionHas('success');

        $this->assertEquals('VCB', PaymentSetting::get('vietqr_bank_code'));
        $this->assertEquals('9998887776', PaymentSetting::get('vietqr_account_number'));
    }

    /**
     * Kiểm tra đặt đơn hàng KHÔNG làm thay đổi địa chỉ, sđt, họ tên của user trong bảng users
     */
    public function test_order_placement_does_not_modify_user_personal_profile()
    {
        $category = Category::create(['name' => 'Gấu Bông', 'slug' => 'gau-bong']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu Dâu Losto',
            'slug' => 'gau-dau-losto',
            'price' => 150000,
            'stock_quantity' => 10,
            'status' => 'ACTIVE',
        ]);

        $this->customer->update([
            'full_name' => 'Nguyễn Văn Chính Chủ',
            'phone' => '0901111111',
            'address' => 'Số 10 Nhà Riêng, Phường Cầu Giấy, Hà Nội',
        ]);

        $cartItem = CartItem::create([
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Đặt hàng với thông tin giao hàng tới bạn bè khác hoàn toàn
        $response = $this->actingAs($this->customer)->post(route('customer.checkout.process'), [
            'selected_items' => [$cartItem->id],
            'recipient_name' => 'Trần Bạn Bè Tặng Quà',
            'recipient_phone' => '0988888888',
            'province' => 'Hải Phòng',
            'ward' => 'Phường Lê Lợi',
            'address_detail' => 'Số 99 Lê Lợi',
            'recipient_address' => 'Số 99 Lê Lợi, Phường Lê Lợi, Hải Phòng',
            'shipping_method' => 'standard',
            'payment_method' => 'COD',
        ]);

        $response->assertRedirect();

        // Kiểm tra profile của user trong bảng users VẪN NGUYÊN VẸN, KHÔNG BỊ GHI ĐÈ
        $this->customer->refresh();
        $this->assertEquals('Nguyễn Văn Chính Chủ', $this->customer->full_name);
        $this->assertEquals('0901111111', $this->customer->phone);
        $this->assertEquals('Số 10 Nhà Riêng, Phường Cầu Giấy, Hà Nội', $this->customer->address);

        // Nhưng đơn hàng vẫn lưu đúng thông tin người nhận khác
        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
            'recipient_name' => 'Trần Bạn Bè Tặng Quà',
            'recipient_phone' => '0988888888',
            'recipient_address' => 'Số 99 Lê Lợi, Phường Lê Lợi, Hải Phòng',
        ]);
    }
}
