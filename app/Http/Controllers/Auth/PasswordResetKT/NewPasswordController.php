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

// Chức năng: Lưu mật khẩu mới sau khi xác thực OTP thành công (PasswordResetKT)
class NewPasswordController extends Controller
{
    // Thời hạn cho phép đặt lại mật khẩu sau khi xác minh OTP là 10 phút
    private const RESET_AUTHORIZATION_MINUTES = 10;

    /**
     * Cập nhật mật khẩu sau khi người dùng xác nhận đúng mã OTP.
     *
     * @throws ValidationException
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Kiểm tra xem email trong session có khớp với email gửi lên hay không
        if ($request->session()->get('password_reset.verified_email') !== $data['email']) {
            throw ValidationException::withMessages([
                'email' => 'Vui lòng xác nhận mã OTP trước khi tạo mật khẩu mới.',
            ]);
        }

        // Bắt đầu transaction để cập nhật mật khẩu và hủy OTP đã dùng
        $user = DB::transaction(function () use ($data): User {
            // Kiểm tra trạng thái OTP đã xác minh trong vòng 10 phút gần nhất
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

            // Tìm user và tiến hành băm mật khẩu mới (Hash::make)
            $user = User::query()->where('email', $data['email'])->lockForUpdate()->firstOrFail();

            $user->forceFill([
                'password' => Hash::make($data['password']),
                'remember_token' => Str::random(60),
            ])->save();

            // Xóa bản ghi OTP để không thể tái sử dụng
            $passwordReset->delete();

            return $user;
        });

        event(new PasswordReset($user));

        $request->session()->forget('password_reset.verified_email');

        // Nếu người dùng đã đăng nhập sẵn thì quay về trang đổi mật khẩu tài khoản
        if ($request->user()) {
            return redirect()->route('account.password.edit')
                ->with('status', 'password-reset');
        }

        // Chuyển hướng về trang Đăng nhập kèm thông báo thành công
        return redirect()->route('login')
            ->with('status', 'Mật khẩu của bạn đã được đặt lại thành công.');
    }
}
