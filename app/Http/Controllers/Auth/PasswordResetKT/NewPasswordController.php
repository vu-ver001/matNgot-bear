<?php

namespace App\Http\Controllers\Auth\PasswordResetKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetKT\ResetPasswordRequest;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    private const RESET_AUTHORIZATION_MINUTES = 10;

    /**
     * Cập nhật mật khẩu sau khi người dùng xác nhận đúng mã OTP.
     *
     * @throws ValidationException
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->session()->get('password_reset.verified_email') !== $data['email']) {
            throw ValidationException::withMessages([
                'email' => 'Vui lòng xác nhận mã OTP trước khi tạo mật khẩu mới.',
            ]);
        }

        $user = DB::transaction(function () use ($data): User {
            $passwordReset = PasswordResetCode::query()
                ->where('email', $data['email'])
                ->whereNotNull('verified_at')
                ->where('verified_at', '>=', now()->subMinutes(self::RESET_AUTHORIZATION_MINUTES))
                ->lockForUpdate()
                ->first();

            if (! $passwordReset) {
                throw ValidationException::withMessages([
                    'email' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng gửi mã OTP mới.',
                ]);
            }

            $user = User::query()->where('email', $data['email'])->lockForUpdate()->firstOrFail();

            $user->forceFill([
                'password' => Hash::make($data['password']),
                'remember_token' => Str::random(60),
            ])->save();

            $passwordReset->delete();

            return $user;
        });

        event(new PasswordReset($user));

        $request->session()->forget('password_reset.verified_email');

        return redirect()->route('login')
            ->with('status', 'Mật khẩu của bạn đã được đặt lại thành công.');
    }
}
