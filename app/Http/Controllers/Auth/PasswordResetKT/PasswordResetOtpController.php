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

class PasswordResetOtpController extends Controller
{
    private const CODE_EXPIRES_SECONDS = 60;

    public function __construct(private readonly OtpService $otpService) {}

    /**
     * Hiển thị trang đặt lại mật khẩu bằng mã OTP.
     */
    public function create(): View
    {
        return view('auth.passwordResetKT.index');
    }

    /**
     * Tạo và gửi mã OTP đến email đã đăng ký.
     */
    public function sendCode(SendPasswordResetCodeRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        $this->otpService->issueCode(
            PasswordResetCode::class,
            $email,
            self::CODE_EXPIRES_SECONDS,
            function (string $code) use ($email): void {
                Mail::to($email)->send(new PasswordResetVerificationCodeMail($code));
            },
        );

        $request->session()->forget('password_reset.verified_email');

        return response()->json([
            'message' => 'Mã xác nhận đã được gửi đến email của bạn.',
            'expires_in' => self::CODE_EXPIRES_SECONDS,
        ]);
    }

    /**
     * Kiểm tra mã OTP và cho phép người dùng tạo mật khẩu mới.
     */
    public function verifyCode(VerifyOtpCodeRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $code = $request->validated('code');

        $this->otpService->verifyCode(PasswordResetCode::class, $email, $code);

        $request->session()->regenerate();
        $request->session()->put('password_reset.verified_email', $email);

        return response()->json([
            'message' => 'Mã xác nhận hợp lệ. Bạn có thể tạo mật khẩu mới.',
        ]);
    }
}
