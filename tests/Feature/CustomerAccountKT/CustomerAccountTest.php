<?php

namespace Tests\Feature\CustomerAccountKT;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_open_completed_account_pages(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSeeText('Hồ sơ cá nhân')
            ->assertSeeText('Khu vực khách hàng');

        $this->actingAs($customer)
            ->get(route('customer.wishlist.index'))
            ->assertOk()
            ->assertSeeText('Danh sách yêu thích')
            ->assertSee('site-header', false)
            ->assertDontSee('<nav class="nav-bar">', false);

        $this->actingAs($customer)
            ->get(route('customer.wishlist.index', ['view' => 'account']))
            ->assertOk()
            ->assertSeeText('Danh sách yêu thích')
            ->assertSeeText('Khu vực khách hàng');
    }

    public function test_customer_dashboard_redirects_to_profile(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertRedirectToRoute('profile.edit');
    }

    public function test_guest_cannot_open_customer_wishlist(): void
    {
        $this->get(route('customer.wishlist.index'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_profile_uses_customer_account_layout(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertViewIs('ProfileKT.index')
            ->assertSeeText('Khu vực khách hàng')
            ->assertSee('aria-label="Về trang chủ Mật Ngọt Bear"', false)
            ->assertSee('aria-controls="customer-account-user-menu"', false)
            ->assertSee('id="customer-account-user-menu"', false)
            ->assertSeeText('Hồ sơ')
            ->assertSeeText('Đổi mật khẩu')
            ->assertSeeText('Đăng xuất');
    }

    public function test_every_role_uses_the_shared_profile_module(): void
    {
        foreach ([User::ROLE_CUSTOMER, User::ROLE_STAFF, User::ROLE_ADMIN] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $response = $this->actingAs($user)
                ->get(route('profile.edit'))
                ->assertOk()
                ->assertViewIs('ProfileKT.index')
                ->assertSeeText('Thông tin cá nhân');

            if ($role !== User::ROLE_CUSTOMER) {
                $response->assertDontSeeText('Khu vực khách hàng');
            }
        }
    }
}
