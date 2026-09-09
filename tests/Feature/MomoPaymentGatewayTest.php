<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\MomoService;
use App\Services\PaymentSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MomoPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'status' => 'ACTIVE',
            'full_name' => 'Khách MoMo Test',
            'phone' => '0987654321',
        ]);

        $category = Category::create([
            'name' => 'Gấu Bông',
            'slug' => 'gau-bong',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu Teddy Nâu 1m',
            'slug' => 'gau-teddy-nau-1m',
            'price' => 200000,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    private function createOrder(int $amount = 250000): Order
    {
        $order = Order::create([
            'order_code' => 'MNB' . strtoupper(uniqid()),
            'customer_id' => $this->customer->id,
            'subtotal' => $amount - 30000,
            'shipping_fee' => 30000,
            'total_amount' => $amount,
            'order_status' => 'PENDING',
            'payment_method' => 'E_WALLET',
            'payment_status' => 'UNPAID',
            'recipient_name' => $this->customer->full_name,
            'recipient_phone' => $this->customer->phone,
            'recipient_address' => '123 Đường Cầu Giấy, Hà Nội',
            'shipping_method' => 'standard',
        ]);

        $order->details()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'product_price' => $amount - 30000,
            'product_name' => $this->product->name,
            'line_total' => $amount - 30000,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'method' => 'E_WALLET',
            'status' => 'PENDING',
            'amount' => $amount,
            'transaction_ref' => 'TXN' . strtoupper(uniqid()),
        ]);

        return $order;
    }

    /**
     * Test MomoService live call creates gateway payment with valid payUrl in Sandbox
     */
    public function test_momo_service_creates_gateway_payment(): void
    {
        $order = $this->createOrder(50000);
        $momoService = app(MomoService::class);

        $res = $momoService->createGatewayPayment($order);

        $this->assertTrue($res['success']);
        $this->assertNotEmpty($res['payUrl']);
        $this->assertStringContainsString('test-payment.momo.vn', $res['payUrl']);
    }

    /**
     * Test redirect customer to MoMo gateway
     */
    public function test_customer_redirect_to_momo_gateway(): void
    {
        $order = $this->createOrder(50000);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.payment.momo.redirect', $order->id));

        $response->assertRedirect();
        $this->assertStringContainsString('test-payment.momo.vn', $response->headers->get('Location'));
    }

    /**
     * Test MoMo Return URL with valid signature updates order and payment to PAID
     */
    public function test_momo_return_url_updates_order_to_paid(): void
    {
        $order = $this->createOrder(100000);
        $secretKey = config('services.momo.secret_key');
        $accessKey = config('services.momo.access_key');
        $partnerCode = config('services.momo.partner_code');

        $orderId = $order->order_code . '_' . time();
        $requestId = 'REQ_' . time();
        $amount = (string) (int) $order->total_amount;
        $orderInfo = "Thanh toan don hang {$order->order_code}";
        $extraData = base64_encode(json_encode(['order_id' => $order->id]));
        $transId = 'MOMO_' . time();
        $resultCode = '0';
        $message = 'Thành công.';
        $payType = 'qr';
        $orderType = 'momo_wallet';
        $responseTime = (string) time();

        $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&message={$message}&orderId={$orderId}&orderInfo={$orderInfo}&orderType={$orderType}&partnerCode={$partnerCode}&payType={$payType}&requestId={$requestId}&responseTime={$responseTime}&resultCode={$resultCode}&transId={$transId}";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $params = [
            'partnerCode' => $partnerCode,
            'orderId' => $orderId,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderInfo' => $orderInfo,
            'orderType' => $orderType,
            'transId' => $transId,
            'resultCode' => $resultCode,
            'message' => $message,
            'payType' => $payType,
            'responseTime' => $responseTime,
            'extraData' => $extraData,
            'signature' => $signature,
        ];

        $response = $this->get(route('payment.momo.return', $params));

        $response->assertRedirect(route('payment.result', $order->id));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'PAID',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'PAID',
            'transaction_ref' => $transId,
        ]);
    }

    /**
     * Test MoMo IPN Server-to-Server Webhook updates order to PAID
     */
    public function test_momo_ipn_webhook_confirms_payment_successfully(): void
    {
        $order = $this->createOrder(150000);
        $secretKey = config('services.momo.secret_key');
        $accessKey = config('services.momo.access_key');
        $partnerCode = config('services.momo.partner_code');

        $orderId = $order->order_code . '_' . time();
        $requestId = 'REQ_' . time();
        $amount = (string) (int) $order->total_amount;
        $orderInfo = "Thanh toan don hang {$order->order_code}";
        $extraData = base64_encode(json_encode(['order_id' => $order->id]));
        $transId = 'MOMO_IPN_' . time();
        $resultCode = '0';
        $message = 'Thành công.';
        $payType = 'qr';
        $orderType = 'momo_wallet';
        $responseTime = (string) time();

        $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&message={$message}&orderId={$orderId}&orderInfo={$orderInfo}&orderType={$orderType}&partnerCode={$partnerCode}&payType={$payType}&requestId={$requestId}&responseTime={$responseTime}&resultCode={$resultCode}&transId={$transId}";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $payload = [
            'partnerCode' => $partnerCode,
            'orderId' => $orderId,
            'requestId' => $requestId,
            'amount' => (int) $amount,
            'orderInfo' => $orderInfo,
            'orderType' => $orderType,
            'transId' => $transId,
            'resultCode' => (int) $resultCode,
            'message' => $message,
            'payType' => $payType,
            'responseTime' => (int) $responseTime,
            'extraData' => $extraData,
            'signature' => $signature,
        ];

        $response = $this->postJson(route('payment.momo.ipn'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['resultCode' => 0, 'message' => 'Success']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'PAID',
        ]);
    }

    /**
     * Test MoMo IPN rejects invalid signature
     */
    public function test_momo_ipn_rejects_invalid_signature(): void
    {
        $order = $this->createOrder(150000);

        $payload = [
            'partnerCode' => 'MOMO',
            'orderId' => $order->order_code . '_123',
            'requestId' => 'REQ_123',
            'amount' => 150000,
            'orderInfo' => 'Test',
            'orderType' => 'momo_wallet',
            'transId' => '123',
            'resultCode' => 0,
            'message' => 'Thành công',
            'payType' => 'qr',
            'responseTime' => time(),
            'extraData' => base64_encode(json_encode(['order_id' => $order->id])),
            'signature' => 'FAKE_INVALID_SIGNATURE',
        ];

        $response = $this->postJson(route('payment.momo.ipn'), $payload);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Invalid signature']);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'UNPAID',
        ]);
    }

    /**
     * Test Admin can configure MoMo credentials via Settings UI
     */
    public function test_admin_can_save_momo_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.payments.saveSettings'), [
            'vietqr_bank_code' => 'MB',
            'vietqr_bank_name' => 'MB Bank',
            'vietqr_account_number' => '0377466205',
            'vietqr_account_name' => 'NGUYEN NGOC ANH',
            'momo_partner_code' => 'MOMO_PARTNER_NEW',
            'momo_access_key' => 'ACC_KEY_TEST',
            'momo_secret_key' => 'SEC_KEY_TEST',
            'momo_phone' => '0912345678',
            'momo_name' => 'CHU SHOP GAU',
            'momo_active' => '1',
        ]);

        $response->assertRedirect(route('admin.payments.settings'));
        $response->assertSessionHas('success');

        $settingService = app(PaymentSettingService::class);
        $this->assertSame('MOMO_PARTNER_NEW', $settingService->get('momo_partner_code'));
        $this->assertSame('ACC_KEY_TEST', $settingService->get('momo_access_key'));
        $this->assertSame('SEC_KEY_TEST', $settingService->get('momo_secret_key'));
        $this->assertSame('0912345678', $settingService->get('momo_phone'));
        $this->assertSame('CHU SHOP GAU', $settingService->get('momo_name'));
    }
}
