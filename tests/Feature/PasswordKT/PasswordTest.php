<?php

namespace Tests\Feature\PasswordKT;

use App\Mail\Auth\PasswordResetKT\PasswordResetVerificationCodeMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_change_password_page(): void
    {
        $this->get(route('account.password.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_every_role_can_open_the_shared_change_password_page(): void
    {
        foreach ([User::ROLE_CUSTOMER, User::ROLE_STAFF, User::ROLE_ADMIN] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $response = $this->actingAs($user)
                ->get(route('account.password.edit'))
                ->assertOk()
                ->assertViewIs('PasswordKT.index')
                ->assertSeeText('Đổi mật khẩu')
                ->assertSeeText('Bảo mật tài khoản');

            if ($role === User::ROLE_CUSTOMER) {
                $response
                    ->assertSeeText('Khu vực khách hàng')
                    ->assertSee('href="'.route('account.password.edit').'"', false)
                    ->assertSee('aria-current="page"', false);
            }
        }
    }

    public function test_password_can_be_updated_and_user_stays_logged_in(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1!'),
        ]);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'OldPassword1!',
                'password' => 'NewPassword2!',
                'password_confirmation' => 'NewPassword2!',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'password-updated')
            ->assertRedirect(route('account.password.edit'));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('NewPassword2!', $user->fresh()->password));
    }

    public function test_current_password_must_be_correct(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1!'),
        ]);

        $this->actingAs($user)
            ->from(route('account.password.edit'))
            ->put(route('account.password.update'), [
                'current_password' => 'WrongPassword1!',
                'password' => 'NewPassword2!',
                'password_confirmation' => 'NewPassword2!',
            ])
            ->assertSessionHasErrorsIn('updatePassword', [
                'current_password' => 'Mật khẩu hiện tại không chính xác.',
            ]);

        $this->assertTrue(Hash::check('OldPassword1!', $user->fresh()->password));
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1!'),
        ]);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'OldPassword1!',
                'password' => 'NewPassword2!',
                'password_confirmation' => 'DifferentPassword3!',
            ])
            ->assertSessionHasErrorsIn('updatePassword', [
                'password' => 'Xác nhận mật khẩu không khớp.',
            ]);
    }

    public function test_new_password_must_follow_the_existing_authentication_rules(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1!'),
        ]);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'OldPassword1!',
                'password' => 'onlyletters',
                'password_confirmation' => 'onlyletters',
            ])
            ->assertSessionHasErrorsIn('updatePassword', 'password');

        $this->assertTrue(Hash::check('OldPassword1!', $user->fresh()->password));
    }

    public function test_new_password_cannot_match_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1!'),
        ]);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'OldPassword1!',
                'password' => 'OldPassword1!',
                'password_confirmation' => 'OldPassword1!',
            ])
            ->assertSessionHasErrorsIn('updatePassword', [
                'password' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
            ]);

        $this->assertTrue(Hash::check('OldPassword1!', $user->fresh()->password));
    }

    public function test_change_password_page_reuses_the_existing_password_reset_form_in_a_modal(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('account.password.edit'))
            ->assertOk()
            ->assertSee('data-password-reset-modal', false)
            ->assertSee('data-password-reset-flow', false)
            ->assertSee('data-send-code-url="'.route('password.otp.send').'"', false)
            ->assertSee('data-verify-code-url="'.route('password.otp.verify').'"', false)
            ->assertSee('value="'.$user->email.'"', false)
            ->assertSeeTextInOrder(['Quên mật khẩu?', 'Quên mật khẩu']);
    }

    public function test_logged_in_user_can_use_the_existing_reset_otp_endpoint(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('password.otp.send'), ['email' => $user->email])
            ->assertOk()
            ->assertJsonPath('message', 'Mã xác nhận đã được gửi đến email của bạn.');

        Mail::assertSent(PasswordResetVerificationCodeMail::class);
        $this->assertDatabaseHas('password_reset_codes', ['email' => $user->email]);
    }

    public function test_logged_in_user_returns_to_change_password_page_after_otp_reset(): void
    {
        $user = User::factory()->create(['email' => 'bear@example.com']);

        PasswordResetCode::query()->create([
            'email' => $user->email,
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinute(),
            'verified_at' => null,
            'last_sent_at' => now(),
            'attempts' => 0,
        ]);

        $this->actingAs($user)
            ->postJson(route('password.otp.verify'), [
                'email' => $user->email,
                'code' => '123456',
            ])
            ->assertOk();

        $this->post(route('password.store'), [
                'email' => $user->email,
                'password' => 'ResetPassword2!',
                'password_confirmation' => 'ResetPassword2!',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'password-reset')
            ->assertRedirect(route('account.password.edit'));

        $this->assertTrue(Hash::check('ResetPassword2!', $user->fresh()->password));
    }
}
