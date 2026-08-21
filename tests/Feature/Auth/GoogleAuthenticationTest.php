<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost:8000/auth/google/callback',
        ]);
    }

    public function test_guest_can_be_redirected_to_google(): void
    {
        Socialite::fake('google');

        $this->get(route('auth.google.redirect'))
            ->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_verified_google_user_can_create_a_customer_account(): void
    {
        Socialite::fake('google', $this->googleUser());

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('customer.dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'full_name' => 'Gấu Mật Ngọt',
            'email' => 'bear@example.com',
            'google_id' => 'google-123',
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->assertNotNull(User::query()->where('email', 'bear@example.com')->value('email_verified_at'));
    }

    public function test_google_login_links_an_existing_account_by_verified_email(): void
    {
        $user = User::factory()->create([
            'email' => 'bear@example.com',
            'role' => User::ROLE_STAFF,
            'google_id' => null,
        ]);
        Socialite::fake('google', $this->googleUser());

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('staff.dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-123', $user->fresh()->google_id);
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        Socialite::fake('google', $this->googleUser(['email_verified' => false]));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Email Google chưa được xác minh nên không thể đăng nhập.']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_blocked_account_cannot_login_with_google(): void
    {
        $user = User::factory()->create([
            'email' => 'bear@example.com',
            'status' => User::STATUS_BLOCKED,
        ]);
        Socialite::fake('google', $this->googleUser());

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.',
            ]);

        $this->assertGuest();
        $this->assertNull($user->fresh()->google_id);
    }

    private function googleUser(array $overrides = []): GoogleUser
    {
        return GoogleUser::fake(array_merge([
            'id' => 'google-123',
            'name' => 'Gấu Mật Ngọt',
            'email' => 'bear@example.com',
            'email_verified' => true,
        ], $overrides));
    }
}
