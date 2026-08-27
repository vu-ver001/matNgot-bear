<?php

namespace Tests\Feature\Auth\RegistrationKT;

use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_users_cannot_register_before_email_verification(): void
    {
        $response = $this->post('/register', [
            'full_name' => 'Nguyễn Văn An',
            'email' => 'test@example.com',
            'phone' => '0912345678',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Vui lòng xác minh email trước khi đăng ký.',
        ]);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_verified_customer_can_register(): void
    {
        EmailVerificationCode::query()->create([
            'email' => 'test@example.com',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinute(),
            'verified_at' => now(),
            'last_sent_at' => now(),
            'attempts' => 0,
        ]);

        $response = $this
            ->withSession(['registration.verified_email' => 'test@example.com'])
            ->post('/register', [
                'full_name' => 'Nguyễn Văn An',
                'email' => 'test@example.com',
                'phone' => '0912345678',
                'password' => 'password1',
                'password_confirmation' => 'password1',
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_BLOCKED,
            ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('customer.dashboard', absolute: false));

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->assertSame('Nguyễn Văn An', $user->full_name);
        $this->assertSame('0912345678', $user->phone);
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertSame(User::STATUS_ACTIVE, $user->status);
        $this->assertTrue(Hash::check('password1', $user->password));
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseMissing('email_verification_codes', ['email' => 'test@example.com']);
    }
}
