<?php

namespace App\Http\Controllers\Auth\PasswordResetKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetKT\SendPasswordResetCodeRequest;
use App\Http\Requests\Auth\SharedKT\VerifyOtpCodeRequest;
use App\Mail\Auth\PasswordResetKT\PasswordResetVerificationCodeMail;
use App\Models\PasswordResetCode;
use App\Services\Auth\SharedKT\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

// Chức năng: Gửi và xác nhận mã OTP khôi phục mật khẩu (PasswordResetKT)
class PasswordResetOtpController extends Controller
{
    // Thời hạn hiệu lực của mã OTP là 60 giây
    private const CODE_EXPIRES_SECONDS = 60;

    public function __construct(private readonly OtpService $otpService) {}

    /**
     * [Giao diện] Hiển thị trang Quên mật khẩu 3 bước (auth/passwordResetKT/index.blade.php).
     */
    public function create(): View
    {
        return view('auth.passwordResetKT.index');
    }

    /**
     * API tạo và gửi mã OTP 6 số vào email tài khoản yêu cầu.
     */
    public function sendCode(SendPasswordResetCodeRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        // Tạo mã OTP ngẫu nhiên, lưu DB kèm hạn dùng và gửi email
        $this->otpService->issueCode(
            PasswordResetCode::class,
            $email,
            self::CODE_EXPIRES_SECONDS,
            function (string $code) use ($email): void {
                Mail::to($email)->send(new PasswordResetVerificationCodeMail($code));
            },
        );

        // Xóa email đã xác minh cũ trong session
        $request->session()->forget('password_reset.verified_email');

        return response()->json([
            'message' => 'Mã xác nhận đã được gửi đến email của bạn.',
            'expires_in' => self::CODE_EXPIRES_SECONDS,
        ]);
    }

    /**
     * API kiểm tra mã OTP do người dùng nhập.
     */
    public function verifyCode(VerifyOtpCodeRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $code = $request->validated('code');

        // Kiểm tra mã OTP: trùng khớp, chưa hết hạn, số lần thử hợp lệ
        $this->otpService->verifyCode(PasswordResetCode::class, $email, $code);

        // Tạo mới ID session chống tấn công session fixation và lưu email đã xác minh
        $request->session()->regenerate();
        $request->session()->put('password_reset.verified_email', $email);

        return response()->json([
            'message' => 'Mã xác nhận hợp lệ. Bạn có thể tạo mật khẩu mới.',
        ]);
    }
}
