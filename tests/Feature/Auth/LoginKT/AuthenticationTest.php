<?php

namespace Tests\Feature\Auth\LoginKT;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertStatus(200)
            ->assertSee('name="remember" value="1" checked', false);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertNotNull($user->fresh()->last_login_at);
        $response->assertRedirect(route('customer.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ]);
    }

    public function test_users_can_choose_to_be_remembered(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
        $response->assertCookie(Auth::guard('web')->getRecallerName());
    }

    public function test_blocked_users_cannot_authenticate(): void
    {
        $user = User::factory()->create(['status' => User::STATUS_BLOCKED]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.',
        ]);
    }

    public function test_active_users_are_redirected_by_role(): void
    {
        $roles = [
            User::ROLE_CUSTOMER => 'customer.dashboard',
            User::ROLE_STAFF => 'staff.dashboard',
            User::ROLE_ADMIN => 'admin.dashboard',
        ];

        foreach ($roles as $role => $routeName) {
            $user = User::factory()->create(['role' => $role]);

            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

            $response->assertRedirect(route($routeName, absolute: false));
            $this->assertAuthenticatedAs($user);

            $this->post('/logout');
        }
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
