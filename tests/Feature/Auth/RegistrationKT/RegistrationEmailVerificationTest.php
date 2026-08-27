<?php

namespace Tests\Feature\Auth\RegistrationKT;

use App\Mail\Auth\RegistrationKT\RegistrationVerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_mailer_is_rejected_to_avoid_logging_plaintext_code(): void
    {
        config(['mail.default' => 'log']);

        $this->postJson(route('register.email.send'), ['email' => 'bear@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseMissing('email_verification_codes', ['email' => 'bear@example.com']);
    }

    public function test_send_code_rejects_invalid_or_used_email(): void
    {
        User::factory()->create(['email' => 'used@example.com']);

        $this->postJson(route('register.email.send'), ['email' => 'invalid-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->postJson(route('register.email.send'), ['email' => 'used@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email')
            ->assertJsonPath('errors.email.0', 'Email này đã được sử dụng.');
    }

    public function test_send_code_stores_only_hash_and_sends_mail(): void
    {
        Mail::fake();
        $plainCode = null;

        $this->postJson(route('register.email.send'), ['email' => 'BEAR@example.com'])
            ->assertOk()
            ->assertJsonPath('message', 'Mã xác nhận đã được gửi đến email của bạn.')
            ->assertJsonPath('expires_in', 60)
            ->assertJsonMissingPath('code');

        Mail::assertSent(RegistrationVerificationCodeMail::class, function ($mail) use (&$plainCode): bool {
            $plainCode = $mail->code;

            return $mail->hasTo('bear@example.com');
        });

        $verification = EmailVerificationCode::query()->where('email', 'bear@example.com')->firstOrFail();

        $this->assertMatchesRegularExpression('/^\d{6}$/', $plainCode);
        $this->assertNotSame($plainCode, $verification->code_hash);
        $this->assertTrue(Hash::check($plainCode, $verification->code_hash));
        $this->assertTrue($verification->expires_at->between(now()->addSeconds(59), now()->addSeconds(61)));
        $this->assertSame(0, $verification->attempts);
    }

    public function test_resend_requires_cooldown_and_invalidates_old_code(): void
    {
        Mail::fake();

        $this->postJson(route('register.email.send'), ['email' => 'bear@example.com'])->assertOk();
        $oldHash = EmailVerificationCode::query()->value('code_hash');

        $this->postJson(route('register.email.send'), ['email' => 'bear@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->travel(61)->seconds();

        $this->postJson(route('register.email.send'), ['email' => 'bear@example.com'])->assertOk();

        $verification = EmailVerificationCode::query()->where('email', 'bear@example.com')->firstOrFail();

        $this->assertNotSame($oldHash, $verification->code_hash);
        $this->assertSame(0, $verification->attempts);
        $this->assertNull($verification->verified_at);
        Mail::assertSentCount(2);
    }

    public function test_wrong_code_increases_attempts_and_fifth_failure_locks_code(): void
    {
        $this->createVerification('bear@example.com', '123456');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson(route('register.email.verify'), [
                'email' => 'bear@example.com',
                'code' => '654321',
            ])->assertUnprocessable()->assertJsonValidationErrors('code');
        }

        $this->assertSame(5, EmailVerificationCode::query()->value('attempts'));

        $this->postJson(route('register.email.verify'), [
            'email' => 'bear@example.com',
            'code' => '123456',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.code.0', 'Bạn đã nhập sai quá 5 lần. Vui lòng yêu cầu mã mới.');
    }

    public function test_expired_code_is_rejected(): void
    {
        $this->createVerification('bear@example.com', '123456', now()->subSecond());

        $this->postJson(route('register.email.verify'), [
            'email' => 'bear@example.com',
            'code' => '123456',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.code.0', 'Mã xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.');
    }

    public function test_correct_code_verifies_email_and_cannot_be_reused(): void
    {
        $this->createVerification('bear@example.com', '123456');

        $this->postJson(route('register.email.verify'), [
            'email' => 'bear@example.com',
            'code' => '123456',
        ])->assertOk()
            ->assertJsonPath('message', 'Email đã được xác minh thành công.')
            ->assertSessionHas('registration.verified_email', 'bear@example.com');

        $this->assertNotNull(EmailVerificationCode::query()->value('verified_at'));

        $this->postJson(route('register.email.verify'), [
            'email' => 'bear@example.com',
            'code' => '123456',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.code.0', 'Mã xác nhận này đã được sử dụng.');
    }

    private function createVerification(string $email, string $code, mixed $expiresAt = null): EmailVerificationCode
    {
        return EmailVerificationCode::query()->create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => $expiresAt ?? now()->addMinute(),
            'verified_at' => null,
            'last_sent_at' => now(),
            'attempts' => 0,
        ]);
    }
}
