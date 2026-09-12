<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffUserMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_popup_menu_has_connected_profile_and_password_links(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        // 1. On Admin Dashboard
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('href="'.route('profile.edit').'"', false);
        $response->assertSee('href="'.route('account.password.edit').'"', false);
        $response->assertDontSee('Chưa kết nối');
        $response->assertDontSee('badge-not-connected');

        // 2. On Profile Edit Page
        $profileResponse = $this->actingAs($admin)->get(route('profile.edit'));
        $profileResponse->assertOk();
        $profileResponse->assertSee('admin-sidebar', false);
        $profileResponse->assertSee('Hồ sơ cá nhân');
        // Profile link should have active class, password link should not
        $profileResponse->assertSee('href="'.route('profile.edit').'" class="user-popup-item active"', false);
        $profileResponse->assertSee('href="'.route('account.password.edit').'" class="user-popup-item "', false);

        // 3. On Password Edit Page
        $passwordResponse = $this->actingAs($admin)->get(route('account.password.edit'));
        $passwordResponse->assertOk();
        $passwordResponse->assertSee('admin-sidebar', false);
        $passwordResponse->assertSee('Đổi mật khẩu');
        // Password link should have active class, profile link should not
        $passwordResponse->assertSee('href="'.route('profile.edit').'" class="user-popup-item "', false);
        $passwordResponse->assertSee('href="'.route('account.password.edit').'" class="user-popup-item active"', false);
    }

    public function test_staff_popup_menu_has_connected_profile_and_password_links(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
        ]);

        // 1. On Staff Dashboard
        $response = $this->actingAs($staff)->get(route('staff.dashboard'));
        $response->assertOk();
        $response->assertSee('href="'.route('profile.edit').'"', false);
        $response->assertSee('href="'.route('account.password.edit').'"', false);
        $response->assertDontSee('Chưa kết nối');
        $response->assertDontSee('badge-not-connected');

        // 2. On Profile Edit Page
        $profileResponse = $this->actingAs($staff)->get(route('profile.edit'));
        $profileResponse->assertOk();
        $profileResponse->assertSee('staff-sidebar', false);
        $profileResponse->assertSee('Hồ sơ cá nhân');
        $profileResponse->assertSee('href="'.route('profile.edit').'" class="user-popup-item active"', false);
        $profileResponse->assertSee('href="'.route('account.password.edit').'" class="user-popup-item "', false);

        // 3. On Password Edit Page
        $passwordResponse = $this->actingAs($staff)->get(route('account.password.edit'));
        $passwordResponse->assertOk();
        $passwordResponse->assertSee('staff-sidebar', false);
        $passwordResponse->assertSee('Đổi mật khẩu');
        $passwordResponse->assertSee('href="'.route('profile.edit').'" class="user-popup-item "', false);
        $passwordResponse->assertSee('href="'.route('account.password.edit').'" class="user-popup-item active"', false);
    }

    public function test_auth_page_has_connected_home_link(): void
    {
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertOk();
        $loginResponse->assertSee('href="'.route('home').'"', false);
        $loginResponse->assertSee('class="auth-home-link"', false);
        $loginResponse->assertDontSee('data-placeholder-link');

        $registerResponse = $this->get(route('register'));
        $registerResponse->assertOk();
        $registerResponse->assertSee('href="'.route('home').'"', false);
        $registerResponse->assertSee('class="auth-home-link"', false);
    }
}

