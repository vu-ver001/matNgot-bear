<?php

namespace Tests\Feature;

use App\Mail\ProfileKT\EmailChangeCodeMail;
use App\Models\EmailChangeCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_profile(): void
    {
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response
            ->assertOk()
            ->assertSeeText($user->full_name)
            ->assertSeeText('Thông tin cá nhân')
            ->assertSeeText('Thông tin tài khoản')
            ->assertSeeText('Tham gia từ')
            ->assertSeeText('Cần hỗ trợ?')
            ->assertSeeText('Đăng ký qua')
            ->assertSeeText('Lần đăng nhập cuối')
            ->assertSeeText('Chưa ghi nhận')
            ->assertSeeText('Chỉnh sửa thông tin')
            ->assertSeeText('Email chỉ thay đổi sau khi bạn nhập đúng mã xác nhận.')
            ->assertSee('form="profile-email-code-form"', false)
            ->assertSee('data-profile-editable', false)
            ->assertSee('data-profile-email-input', false)
            ->assertSee('data-profile-email-action', false)
            ->assertSee('readonly aria-readonly="true"', false)
            ->assertDontSeeText('Đổi ảnh')
            ->assertSee('data-profile-avatar-input', false);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $originalEmail = $user->email;

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'full_name' => 'Test User',
                'email' => $originalEmail,
                'phone' => '0123456789',
                'address' => 'Hà Nội',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->full_name);
        $this->assertSame($originalEmail, $user->email);
        $this->assertSame('0123456789', $user->phone);
        $this->assertSame('Hà Nội', $user->address);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_profile_uses_the_same_name_and_phone_validation_as_registration(): void
    {
        $user = User::factory()->create(['full_name' => 'Nguyễn Văn An']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => 'An123',
                'phone' => '12345',
                'address' => $user->address,
            ])
            ->assertSessionHasErrors([
                'full_name' => 'Họ và tên chỉ được chứa chữ cái, khoảng trắng, dấu nháy đơn hoặc dấu gạch nối.',
                'phone' => 'Số điện thoại phải bắt đầu bằng 0 hoặc +84 và có độ dài hợp lệ.',
            ]);

        $this->assertSame('Nguyễn Văn An', $user->fresh()->full_name);
    }

    public function test_profile_values_are_trimmed_before_saving(): void
    {
        $user = User::factory()->create(['full_name' => 'Nguyễn Văn An']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => '  Trần Thị Mai  ',
                'phone' => '  0912345678  ',
                'address' => '  Hà Nội  ',
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('Trần Thị Mai', $user->full_name);
        $this->assertSame('0912345678', $user->phone);
        $this->assertSame('Hà Nội', $user->address);
    }

    public function test_saving_unchanged_profile_reports_that_nothing_changed(): void
    {
        $user = User::factory()->create();
        $originalUpdatedAt = $user->updated_at->copy();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => $user->full_name,
                'phone' => $user->phone,
                'address' => $user->address,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'profile-no-changes')
            ->assertRedirect(route('profile.edit'));

        $this->assertTrue($user->fresh()->updated_at->equalTo($originalUpdatedAt));

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('data-profile-editing-initially="true"', false);
    }

    public function test_customer_cannot_change_email_without_confirmation(): void
    {
        $user = User::factory()->create();
        $originalEmail = $user->email;

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => $user->full_name,
                'email' => 'new-email@example.com',
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame($originalEmail, $user->fresh()->email);
    }

    public function test_customer_can_request_a_code_for_a_new_email(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)
            ->post(route('profile.email.code'), ['email' => 'NEW@example.com'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('old@example.com', $user->fresh()->email);
        $this->assertDatabaseHas('email_change_codes', [
            'user_id' => $user->id,
            'email' => 'new@example.com',
        ]);
        Mail::assertSent(EmailChangeCodeMail::class, fn (EmailChangeCodeMail $mail): bool => $mail->hasTo('new@example.com'));
    }

    public function test_email_change_code_request_returns_json_for_ajax(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)
            ->postJson(route('profile.email.code'), ['email' => 'new@example.com'])
            ->assertOk()
            ->assertJson([
                'message' => 'Mã xác nhận đã được gửi đến email mới.',
                'email' => 'new@example.com',
            ]);

        $this->assertSame('old@example.com', $user->fresh()->email);
    }

    public function test_email_is_changed_only_after_the_correct_code_is_verified(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        EmailChangeCode::query()->create([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('profile.email.verify'), ['code' => '123456'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'email-updated')
            ->assertRedirect();

        $user->refresh();

        $this->assertSame('new@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseMissing('email_change_codes', ['user_id' => $user->id]);
    }

    public function test_email_confirmation_returns_updated_email_for_ajax(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        EmailChangeCode::query()->create([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->patchJson(route('profile.email.verify'), ['code' => '123456'])
            ->assertOk()
            ->assertJson([
                'email' => 'new@example.com',
            ]);

        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_wrong_email_confirmation_code_does_not_change_email(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        EmailChangeCode::query()->create([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('profile.email.verify'), ['code' => '999999'])
            ->assertSessionHasErrors('code');

        $this->assertSame('old@example.com', $user->fresh()->email);
    }

    public function test_customer_cannot_request_an_email_used_by_another_account(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'old@example.com']);
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($user)
            ->post(route('profile.email.code'), ['email' => 'taken@example.com'])
            ->assertSessionHasErrors('email');

        $this->assertSame('old@example.com', $user->fresh()->email);
        Mail::assertNothingSent();
    }

    public function test_customer_can_cancel_a_pending_email_change(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        EmailChangeCode::query()->create([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->delete(route('profile.email.cancel'))
            ->assertSessionHas('status', 'email-change-cancelled')
            ->assertRedirect();

        $this->assertSame('old@example.com', $user->fresh()->email);
        $this->assertDatabaseMissing('email_change_codes', ['user_id' => $user->id]);
    }

    public function test_profile_cannot_be_saved_while_new_email_is_waiting_for_confirmation(): void
    {
        $user = User::factory()->create([
            'full_name' => 'Nguyễn Văn An',
            'email' => 'old@example.com',
        ]);
        EmailChangeCode::query()->create([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => 'Trần Thị Mai',
                'phone' => '0912345678',
                'address' => 'Hà Nội',
            ])
            ->assertSessionHasErrors([
                'email' => 'Email mới chưa được xác nhận. Vui lòng nhập mã hoặc hủy đổi email trước khi lưu.',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertSame('Nguyễn Văn An', $user->fresh()->full_name);
    }

    public function test_profile_cannot_be_saved_with_an_unconfirmed_email_that_has_not_requested_a_code(): void
    {
        $user = User::factory()->create([
            'full_name' => 'Nguyễn Văn An',
            'email' => 'old@example.com',
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => 'Trần Thị Mai',
                'email' => 'new@example.com',
                'phone' => '0912345678',
                'address' => 'Hà Nội',
            ])
            ->assertSessionHasErrors('email')
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Nguyễn Văn An', $user->full_name);
        $this->assertSame('old@example.com', $user->email);

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('value="old@example.com"', false)
            ->assertSee('data-profile-email-input', false)
            ->assertSee('readonly aria-readonly="true"', false)
            ->assertSeeText('Đổi email');
    }

    public function test_successful_profile_save_returns_to_read_only_mode(): void
    {
        $user = User::factory()->create(['full_name' => 'Nguyễn Văn An']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => 'Trần Thị Mai',
                'phone' => '0912345678',
                'address' => 'Hà Nội',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'profile-updated');

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('data-profile-editing-initially="false"', false)
            ->assertSee('data-profile-edit-button', false)
            ->assertSee('readonly aria-readonly="true"', false);
    }

    public function test_user_can_upload_jpg_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $avatar = $user->fresh()->avatar;

        $this->assertStringStartsWith('avatars/', $avatar);
        Storage::disk('public')->assertExists($avatar);
    }

    public function test_user_can_upload_png_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('avatar.png'),
            ])
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    public function test_avatar_larger_than_five_megabytes_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('large-avatar.jpg')->size(5121),
            ])
            ->assertSessionHasErrors('avatar');

        $this->assertNull($user->fresh()->avatar);
    }

    public function test_non_image_avatar_files_are_rejected(): void
    {
        $user = User::factory()->create();

        foreach (['document.pdf' => 'application/pdf', 'script.php' => 'text/x-php', 'vector.svg' => 'image/svg+xml'] as $name => $mime) {
            $this->actingAs($user)
                ->patch(route('profile.update'), [
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'avatar' => UploadedFile::fake()->create($name, 10, $mime),
                ])
                ->assertSessionHasErrors('avatar');
        }

        $this->assertNull($user->fresh()->avatar);
    }

    public function test_old_managed_avatar_is_deleted_after_uploading_a_new_avatar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old-avatar.jpg', 'old avatar');
        $user = User::factory()->create(['avatar' => 'avatars/old-avatar.jpg']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('new-avatar.jpg'),
            ])
            ->assertSessionHasNoErrors();

        $newAvatar = $user->fresh()->avatar;

        Storage::disk('public')->assertMissing('avatars/old-avatar.jpg');
        Storage::disk('public')->assertExists($newAvatar);
    }

    public function test_profile_update_cannot_change_role_or_status(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_BLOCKED,
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertSame(User::STATUS_ACTIVE, $user->status);
    }

    public function test_email_verification_status_is_unchanged_on_customer_profile_update(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'full_name' => 'Test User',
                'email' => 'ignored@example.com',
            ]);

        $response
            ->assertSessionHasErrors('email')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
