<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerVoucherAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    // =========================================================================
    // 1. KHO VOUCHER & QUẢN LÝ VOUCHER (Kho voucher: Customer, Quản lý: Admin)
    // =========================================================================

    public function test_customer_can_access_voucher_page(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.vouchers.index'));
        $response->assertStatus(200);
        $response->assertViewIs('customer.vouchers.index');
    }

    public function test_staff_and_customer_can_access_customer_vouchers_but_admin_is_blocked(): void
    {
        // Customer can access customer voucher page
        $customerRes = $this->actingAs($this->customer)->get(route('customer.vouchers.index'));
        $customerRes->assertStatus(200);

        // Staff can also access customer voucher page to advise customers
        $staffRes = $this->actingAs($this->staff)->get(route('customer.vouchers.index'));
        $staffRes->assertStatus(200);
        $staffRes->assertSee('Chế độ Nhân viên tư vấn khách hàng');
        $staffRes->assertSee('Chi Tiết Điều Kiện Voucher');

        // Admin is blocked from customer voucher page (Admin manages vouchers at /admin/vouchers)
        $adminRes = $this->actingAs($this->admin)->get(route('customer.vouchers.index'));
        $adminRes->assertStatus(403);
    }

    public function test_customer_and_staff_are_blocked_from_admin_vouchers(): void
    {
        // Customer accessing admin vouchers -> 403
        $customerRes = $this->actingAs($this->customer)->get(route('admin.vouchers.index'));
        $customerRes->assertStatus(403);

        // Staff accessing admin vouchers -> 403
        $staffRes = $this->actingAs($this->staff)->get(route('admin.vouchers.index'));
        $staffRes->assertStatus(403);

        // Admin accessing admin vouchers -> 200
        $adminRes = $this->actingAs($this->admin)->get(route('admin.vouchers.index'));
        $adminRes->assertStatus(200);
    }

    // =========================================================================
    // 2. GIỎ HÀNG & TRANG THANH TOÁN (Cart & Checkout: Chỉ dành cho Customer)
    // =========================================================================

    public function test_customer_can_access_cart_and_checkout(): void
    {
        $cartRes = $this->actingAs($this->customer)->get(route('customer.cart'));
        $cartRes->assertStatus(200);

        $category = \App\Models\Category::create(['name' => 'Gấu Bông', 'slug' => 'gau-bong-' . uniqid()]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu Bông Test',
            'price' => 150000,
            'stock_quantity' => 10,
            'status' => 'ACTIVE',
        ]);

        CartItem::create([
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'selected' => true,
        ]);

        $checkoutRes = $this->actingAs($this->customer)->get(route('customer.checkout'));
        $checkoutRes->assertStatus(200);
    }

    public function test_admin_and_staff_are_blocked_from_cart_and_checkout(): void
    {
        // Admin
        $this->actingAs($this->admin)->get(route('customer.cart'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('customer.checkout'))->assertStatus(403);

        // Staff
        $this->actingAs($this->staff)->get(route('customer.cart'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('customer.checkout'))->assertStatus(403);
    }

    // =========================================================================
    // 3. QUẢN LÝ THANH TOÁN & XỬ LÝ THANH TOÁN
    // =========================================================================

    public function test_payment_management_role_permissions(): void
    {
        // Customer is blocked from Admin Payments and Staff Payments
        $this->actingAs($this->customer)->get(route('admin.payments.index'))->assertStatus(403);
        $this->actingAs($this->customer)->get(route('staff.payments.index'))->assertStatus(403);

        // Staff is blocked from Admin Payments, can access Staff Payments
        $this->actingAs($this->staff)->get(route('admin.payments.index'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('staff.payments.index'))->assertStatus(200);

        // Admin can access Admin Payments, is blocked from Staff Payments
        $this->actingAs($this->admin)->get(route('admin.payments.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('staff.payments.index'))->assertStatus(403);
    }

    public function test_customer_payment_routes_blocked_for_admin_and_staff(): void
    {
        $order = Order::create([
            'order_code' => 'ORD-TEST-PERM',
            'customer_id' => $this->customer->id,
            'recipient_name' => 'Khách hàng Test',
            'recipient_phone' => '0987654321',
            'recipient_address' => 'Hà Nội',
            'subtotal' => 200000,
            'shipping_fee' => 30000,
            'total_amount' => 230000,
            'order_status' => 'PENDING',
            'payment_method' => 'CARD',
            'payment_status' => 'UNPAID',
        ]);

        // Admin and Staff blocked from customer payment QR route
        $this->actingAs($this->admin)->get(route('customer.payment.qr', $order->id))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('customer.payment.qr', $order->id))->assertStatus(403);

        // Customer can access their own payment QR
        $this->actingAs($this->customer)->get(route('customer.payment.qr', $order->id))->assertStatus(200);
    }

    // =========================================================================
    // 4. GUEST CHƯA ĐĂNG NHẬP BỊ YÊU CẦU ĐĂNG NHẬP
    // =========================================================================

    public function test_guest_is_redirected_to_login_for_protected_routes(): void
    {
        $this->get(route('customer.vouchers.index'))->assertRedirect(route('login'));
        $this->get(route('customer.cart'))->assertRedirect(route('login'));
        $this->get(route('customer.checkout'))->assertRedirect(route('login'));
        $this->get(route('admin.vouchers.index'))->assertRedirect(route('login'));
        $this->get(route('admin.payments.index'))->assertRedirect(route('login'));
        $this->get(route('staff.payments.index'))->assertRedirect(route('login'));
    }

    // =========================================================================
    // 5. HEADER CHỈ HIỆN ICON CHO CUSTOMER & GUEST
    // =========================================================================

    public function test_header_shows_shopping_icons_for_customer_and_guest_but_hides_for_staff_and_admin(): void
    {
        // For guest on home page: sees all 3 shopping utility buttons
        $guestResponse = $this->get(route('home'));
        $guestResponse->assertStatus(200);
        $guestResponse->assertSee('title="Danh sách yêu thích"', false);
        $guestResponse->assertSee('title="Giỏ hàng"', false);
        $guestResponse->assertSee('title="Kho voucher & khuyến mãi"', false);

        // For customer on home page: sees all 3 shopping utility buttons
        $customerResponse = $this->actingAs($this->customer)->get(route('home'));
        $customerResponse->assertStatus(200);
        $customerResponse->assertSee('title="Danh sách yêu thích"', false);
        $customerResponse->assertSee('title="Giỏ hàng"', false);
        $customerResponse->assertSee('title="Kho voucher & khuyến mãi"', false);

        // For admin on home page: customer shopping buttons are completely hidden
        $adminResponse = $this->actingAs($this->admin)->get(route('home'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertDontSee('title="Danh sách yêu thích"', false);
        $adminResponse->assertDontSee('title="Giỏ hàng"', false);
        $adminResponse->assertDontSee('title="Kho voucher & khuyến mãi"', false);

        // For staff on home page: wishlist & cart are hidden, but voucher icon is visible to advise customers
        $staffResponse = $this->actingAs($this->staff)->get(route('home'));
        $staffResponse->assertStatus(200);
        $staffResponse->assertDontSee('title="Danh sách yêu thích"', false);
        $staffResponse->assertDontSee('title="Giỏ hàng"', false);
        $staffResponse->assertSee('title="Kho voucher & khuyến mãi"', false);
    }

    public function test_voucher_badge_count_is_hidden_for_guests_and_shown_for_logged_in_users(): void
    {
        \App\Models\Voucher::create([
            'code' => 'TESTBADGE' . rand(100, 999),
            'voucher_type' => 'ORDER',
            'discount_type' => 'FIXED',
            'discount_value' => 10000,
            'min_order_value' => 50000,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 100,
            'used_count' => 0,
            'status' => 'ACTIVE',
        ]);

        // Guest: does NOT see the orange badge count on voucher icon
        $guestResponse = $this->get(route('home'));
        $guestResponse->assertStatus(200);
        $guestResponse->assertDontSee('background: #E08A1E; color: #ffffff;', false);

        // Customer: sees the orange badge count
        $customerResponse = $this->actingAs($this->customer)->get(route('home'));
        $customerResponse->assertStatus(200);
        $customerResponse->assertSee('background: #E08A1E; color: #ffffff;', false);
    }

    public function test_voucher_can_be_scoped_to_specific_product_variants(): void
    {
        $category = \App\Models\Category::create([
            'name' => 'Gấu Bông',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu Bông Cao Cấp',
            'price' => 200000,
            'stock_quantity' => 20,
            'status' => 'ACTIVE',
        ]);

        $redVariant = \App\Models\ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GAU-RED-M',
            'color' => 'Đỏ',
            'size' => 'M',
            'price' => 200000,
            'stock_quantity' => 10,
        ]);

        $blueVariant = \App\Models\ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GAU-BLUE-L',
            'color' => 'Xanh',
            'size' => 'L',
            'price' => 250000,
            'stock_quantity' => 10,
        ]);

        $voucher = \App\Models\Voucher::create([
            'code' => 'REDONLY50',
            'voucher_type' => 'ORDER',
            'apply_scope' => 'PRODUCT',
            'discount_type' => 'PERCENTAGE',
            'discount_value' => 50,
            'min_order_value' => 100000,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 100,
            'used_count' => 0,
            'status' => 'ACTIVE',
        ]);

        // Attach product and specifically ONLY redVariant
        $voucher->products()->sync([$product->id]);
        $voucher->productVariants()->sync([$redVariant->id]);

        // 1. Validate with Red Variant
        $redCartItem = (object) [
            'product_id' => $product->id,
            'product_variant_id' => $redVariant->id,
            'quantity' => 1,
            'variant' => $redVariant,
            'product' => $product,
        ];
        $resultRed = $voucher->validateForCustomer($this->customer->id, 200000, 0, [$redCartItem]);
        $this->assertTrue($resultRed['valid']);
        $this->assertEquals(100000, $resultRed['discount_amount']);

        // 2. Validate with Blue Variant (should NOT be eligible)
        $blueCartItem = (object) [
            'product_id' => $product->id,
            'product_variant_id' => $blueVariant->id,
            'quantity' => 1,
            'variant' => $blueVariant,
            'product' => $product,
        ];
        $resultBlue = $voucher->validateForCustomer($this->customer->id, 250000, 0, [$blueCartItem]);
        $this->assertFalse($resultBlue['valid']);
        $this->assertStringContainsString('chỉ áp dụng cho một số sản phẩm hoặc phân loại', $resultBlue['message']);
    }

    public function test_customer_can_use_voucher_unlimited_times_when_usage_limit_per_user_is_null(): void
    {
        $voucher = \App\Models\Voucher::create([
            'code' => 'UNLIMITEDPERUSER',
            'voucher_type' => 'ORDER',
            'apply_scope' => 'ALL',
            'discount_type' => 'PERCENTAGE',
            'discount_value' => 10,
            'min_order_value' => 0,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 100,
            'usage_limit_per_user' => null,
            'used_count' => 0,
            'status' => 'ACTIVE',
        ]);

        // Even after user used voucher multiple times in previous orders:
        for ($i = 1; $i <= 5; $i++) {
            Order::create([
                'order_code' => 'ORD-TEST-UNLIMITED-' . $i,
                'customer_id' => $this->customer->id,
                'recipient_name' => 'Khách hàng Test',
                'recipient_phone' => '0987654321',
                'recipient_address' => 'Hà Nội',
                'subtotal' => 200000,
                'shipping_fee' => 30000,
                'total_amount' => 230000,
                'order_status' => 'COMPLETED',
                'payment_method' => 'COD',
                'payment_status' => 'PAID',
                'voucher_id' => $voucher->id,
            ]);
        }

        $this->assertEquals(5, $voucher->countUsedByCustomer($this->customer->id));
        $this->assertFalse($voucher->isUsedByCustomer($this->customer->id));

        $validation = $voucher->validateForCustomer($this->customer->id, 200000);
        $this->assertTrue($validation['valid']);
    }

    public function test_admin_can_create_and_update_voucher_without_per_user_limit(): void
    {
        // 1. Create with empty usage_limit_per_user
        $payload = [
            'code' => 'NOLIMITUSER',
            'voucher_type' => 'ORDER',
            'apply_scope' => 'ALL',
            'discount_type' => 'FIXED',
            'discount_value' => 20000,
            'min_order_value' => 50000,
            'start_date' => now()->format('Y-m-d H:i'),
            'end_date' => now()->addDays(10)->format('Y-m-d H:i'),
            'usage_limit' => 50,
            'usage_limit_per_user' => '', // Leave blank
            'status' => 'ACTIVE',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.vouchers.store'), $payload);
        $response->assertRedirect(route('admin.vouchers.index'));

        $voucher = \App\Models\Voucher::where('code', 'NOLIMITUSER')->first();
        $this->assertNotNull($voucher);
        $this->assertNull($voucher->usage_limit_per_user);

        // 2. Update with null / empty usage_limit_per_user
        $updatePayload = array_merge($payload, [
            'discount_value' => 30000,
            'usage_limit_per_user' => '',
        ]);

        $updateRes = $this->actingAs($this->admin)->put(route('admin.vouchers.update', $voucher), $updatePayload);
        $updateRes->assertRedirect(route('admin.vouchers.index'));

        $voucher->refresh();
        $this->assertEquals(30000, (float) $voucher->discount_value);
        $this->assertNull($voucher->usage_limit_per_user);
    }

    public function test_buy_now_uses_exact_quantity_selected_on_product_detail_page_and_does_not_accumulate_cart(): void
    {
        $category = \App\Models\Category::create([
            'name' => 'Gấu Bông Test',
            'slug' => 'gau-bong-test-' . uniqid(),
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu Bông Teddy Buy Now',
            'slug' => 'gau-bong-teddy-buy-now-' . uniqid(),
            'price' => 200000,
            'stock_quantity' => 50,
            'status' => 'ACTIVE',
        ]);

        // 1. Giả sử trước đó trong giỏ hàng khách đã có sẵn sản phẩm này với số lượng 10
        $oldCartItem = CartItem::create([
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // 2. Khách vào trang chi tiết sản phẩm, chọn mua số lượng là 2 và bấm "Mua Ngay"
        $response = $this->actingAs($this->customer)->get(route('customer.checkout.index', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]));

        $response->assertStatus(200);

        // Số lượng trong CartItem thanh toán phải là đúng 2 (không bị cộng dồn thành 12 hay giữ nguyên 10 của giỏ hàng)
        $oldCartItem->refresh();
        $this->assertEquals(2, $oldCartItem->quantity);

        // View hiển thị đúng số lượng 2 sản phẩm
        $response->assertSee('2 sản phẩm');
    }
}

