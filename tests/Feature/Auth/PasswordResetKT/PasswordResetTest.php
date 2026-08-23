<?php

namespace Tests\Feature\Auth\PasswordResetKT;

use App\Mail\Auth\PasswordResetKT\PasswordResetVerificationCodeMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_screen_can_be_rendered(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Đặt lại mật khẩu')
            ->assertSee('Gửi mã OTP');
    }

    public function test_send_code_rejects_an_email_that_does_not_exist(): void
    {
        $this->postJson(route('password.otp.send'), ['email' => 'unknown@example.com'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Không tìm thấy tài khoản nào sử dụng email này.');
    }

    public function test_send_code_stores_only_hash_and_sends_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'bear@example.com']);
        $plainCode = null;

        $this->postJson(route('password.otp.send'), ['email' => 'BEAR@example.com'])
            ->assertOk()
            ->assertJsonPath('message', 'Mã xác nhận đã được gửi đến email của bạn.')
            ->assertJsonPath('expires_in', 60)
            ->assertJsonMissingPath('code');

        Mail::assertSent(PasswordResetVerificationCodeMail::class, function ($mail) use ($user, &$plainCode): bool {
            $plainCode = $mail->code;

            return $mail->hasTo($user->email);
        });

        $passwordReset = PasswordResetCode::query()->where('email', $user->email)->firstOrFail();

        $this->assertMatchesRegularExpression('/^\d{6}$/', $plainCode);
        $this->assertNotSame($plainCode, $passwordReset->code_hash);
        $this->assertTrue(Hash::check($plainCode, $passwordReset->code_hash));
        $this->assertTrue($passwordReset->expires_at->between(now()->addSeconds(59), now()->addSeconds(61)));
    }

    public function test_resend_requires_a_sixty_second_cooldown(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->postJson(route('password.otp.send'), ['email' => $user->email])->assertOk();

        $this->postJson(route('password.otp.send'), ['email' => $user->email])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Vui lòng chờ 60 giây trước khi yêu cầu mã mới.');

        $this->travel(61)->seconds();

        $this->postJson(route('password.otp.send'), ['email' => $user->email])->assertOk();

        Mail::assertSentCount(2);
    }

    public function test_wrong_code_is_locked_after_five_attempts(): void
    {
        $user = User::factory()->create(['email' => 'bear@example.com']);
        $this->createPasswordResetCode($user->email, '123456');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson(route('password.otp.verify'), [
                'email' => $user->email,
                'code' => '654321',
            ])->assertUnprocessable()->assertJsonValidationErrors('code');
        }

        $this->assertSame(5, PasswordResetCode::query()->value('attempts'));

        $this->postJson(route('password.otp.verify'), [
            'email' => $user->email,
            'code' => '123456',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.code.0', 'Bạn đã nhập sai quá 5 lần. Vui lòng yêu cầu mã mới.');
    }

    public function test_expired_code_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'bear@example.com']);
        $this->createPasswordResetCode($user->email, '123456', now()->subSecond());

        $this->postJson(route('password.otp.verify'), [
            'email' => $user->email,
            'code' => '123456',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.code.0', 'Mã xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.');
    }

    public function test_password_cannot_be_reset_before_otp_verification(): void
    {
        $user = User::factory()->create();

        $this->from(route('password.request'))->post(route('password.store'), [
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');
    }

    public function test_password_can_be_reset_after_otp_verification(): void
    {
        $user = User::factory()->create(['email' => 'bear@example.com']);
        $this->createPasswordResetCode($user->email, '123456');

        $this->postJson(route('password.otp.verify'), [
            'email' => $user->email,
            'code' => '123456',
        ])->assertOk()
            ->assertSessionHas('password_reset.verified_email', $user->email);

        $this->travel(61)->seconds();

        $this->post(route('password.store'), [
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Mật khẩu của bạn đã được đặt lại thành công.')
            ->assertSessionMissing('password_reset.verified_email')
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_codes', ['email' => $user->email]);
    }

    public function test_password_reset_authorization_expires_after_ten_minutes(): void
    {
        $user = User::factory()->create(['email' => 'bear@example.com']);
        $this->createPasswordResetCode($user->email, '123456');

        $this->postJson(route('password.otp.verify'), [
            'email' => $user->email,
            'code' => '123456',
        ])->assertOk();

        $this->travel(601)->seconds();

        $this->from(route('password.request'))->post(route('password.store'), [
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('password.request'))
            ->assertSessionHasErrors([
                'email' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng gửi mã OTP mới.',
            ]);

        $this->assertFalse(Hash::check('NewPassword123', $user->fresh()->password));
        $this->assertDatabaseHas('password_reset_codes', ['email' => $user->email]);
    }

    private function createPasswordResetCode(string $email, string $code, mixed $expiresAt = null): PasswordResetCode
    {
        return PasswordResetCode::query()->create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => $expiresAt ?? now()->addMinute(),
            'verified_at' => null,
            'last_sent_at' => now(),
            'attempts' => 0,
        ]);
    }
}
