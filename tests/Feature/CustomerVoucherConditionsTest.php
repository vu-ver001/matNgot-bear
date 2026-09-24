<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerVoucherConditionsTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $staff;
    private User $admin;
    private Category $category;
    private Product $product;
    private Voucher $orderVoucher;
    private Voucher $shippingVoucher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->category = Category::create([
            'name' => 'Gấu Bông Khổng Lồ',
            'slug' => 'gau-bong-khong-lo-' . uniqid(),
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Gấu Bông Teddy 1m6',
            'slug' => 'gau-bong-teddy-1m6-' . uniqid(),
            'price' => 350000,
            'stock_quantity' => 20,
            'status' => 'ACTIVE',
        ]);

        $this->orderVoucher = Voucher::create([
            'code' => 'MATNGOT50K',
            'voucher_type' => 'ORDER',
            'apply_scope' => 'ALL',
            'discount_type' => 'FIXED',
            'discount_value' => 50000,
            'min_order_value' => 200000,
            'max_discount_value' => 50000,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 100,
            'usage_limit_per_user' => 2,
            'used_count' => 5,
            'status' => 'ACTIVE',
        ]);

        $this->shippingVoucher = Voucher::create([
            'code' => 'SHIPFREE20K',
            'voucher_type' => 'SHIPPING',
            'apply_scope' => 'ALL',
            'discount_type' => 'FIXED',
            'discount_value' => 20000,
            'min_order_value' => 150000,
            'max_discount_value' => 20000,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 200,
            'usage_limit_per_user' => 3,
            'used_count' => 10,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_customer_and_staff_can_fetch_voucher_conditions_json_endpoint(): void
    {
        // 1. Customer can query voucher conditions
        $response = $this->actingAs($this->customer)->getJson(route('customer.vouchers.conditions', $this->orderVoucher->code));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'voucher' => [
                'code' => 'MATNGOT50K',
                'voucher_type' => 'ORDER',
                'discount_value' => 50000,
                'min_order_value' => 200000,
                'min_order_formatted' => '200.000đ',
                'limit_per_user' => 2,
                'user_remaining' => 2,
            ],
        ]);

        // 2. Staff can also query voucher conditions to advise customers
        $staffResponse = $this->actingAs($this->staff)->getJson(route('customer.vouchers.conditions', $this->shippingVoucher->code));
        $staffResponse->assertStatus(200);
        $staffResponse->assertJson([
            'success' => true,
            'voucher' => [
                'code' => 'SHIPFREE20K',
                'voucher_type' => 'SHIPPING',
                'discount_value' => 20000,
            ],
        ]);

        // 3. Admin is blocked from customer voucher conditions route (Admin has admin panel)
        $adminResponse = $this->actingAs($this->admin)->getJson(route('customer.vouchers.conditions', $this->orderVoucher->code));
        $adminResponse->assertStatus(403);

        // 4. Guest is unauthorized / blocked from role route
        $guestResponse = $this->getJson(route('customer.vouchers.conditions', $this->orderVoucher->code));
        $guestResponse->assertStatus(403);
    }

    public function test_voucher_conditions_endpoint_evaluates_order_subtotal(): void
    {
        // Khi đơn hàng đạt giá trị tối thiểu (350k >= 200k)
        $validRes = $this->actingAs($this->customer)->getJson(route('customer.vouchers.conditions', [
            'code' => $this->orderVoucher->code,
            'subtotal' => 350000,
        ]));
        $validRes->assertStatus(200);
        $validRes->assertJsonPath('voucher.is_applicable', true);
        $validRes->assertJsonPath('voucher.expected_discount', 50000);

        // Khi đơn hàng chưa đạt giá trị tối thiểu (100k < 200k)
        $invalidRes = $this->actingAs($this->customer)->getJson(route('customer.vouchers.conditions', [
            'code' => $this->orderVoucher->code,
            'subtotal' => 100000,
        ]));
        $invalidRes->assertStatus(200);
        $invalidRes->assertJsonPath('voucher.is_applicable', false);
        $this->assertStringContainsString('Tổng giá trị sản phẩm hợp lệ phải từ 200.000đ', $invalidRes->json('voucher.inapplicable_reason'));
    }

    public function test_checkout_page_renders_condition_buttons_and_condition_modal(): void
    {
        // Thêm sản phẩm vào giỏ hàng
        CartItem::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'selected' => true,
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.checkout'));
        $response->assertStatus(200);

        // Kiểm tra có nút "Điều kiện" trên giao diện checkout
        $response->assertSee('openConditionModal(v)', false);
        $response->assertSee('<span>Điều kiện</span>', false);

        // Kiểm tra có modal xem chi tiết điều kiện voucher
        $response->assertSee('id="checkout-voucher-modal-title"', false);
        $response->assertSee('Chi Tiết Điều Kiện Voucher', false);
        $response->assertSee('openConditionModal', false);
        $response->assertSee('closeConditionModal', false);
    }

    public function test_product_detail_page_loads_successfully(): void
    {
        $response = $this->get(route('products.show', $this->product->id));
        $response->assertStatus(200);
        $response->assertSee($this->product->name);
    }
}
