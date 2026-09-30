<?php

namespace App\Http\Controllers\Auth\RegistrationKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrationKT\SendRegistrationCodeRequest;
use App\Http\Requests\Auth\SharedKT\VerifyOtpCodeRequest;
use App\Mail\Auth\RegistrationKT\RegistrationVerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Services\Auth\SharedKT\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

// Chức năng: Gửi và xác minh mã OTP email khi đăng ký (RegistrationKT)
class RegistrationEmailVerificationController extends Controller
{
    // Thời gian hết hạn của mã OTP là 60 giây
    private const CODE_EXPIRES_SECONDS = 60;

    public function __construct(private readonly OtpService $otpService) {}

    /**
     * API gửi mã OTP 6 số qua email người dùng.
     */
    public function sendCode(SendRegistrationCodeRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        // Sinh mã OTP 6 số ngẫu nhiên, lưu DB và gửi email qua Mail::send
        $this->otpService->issueCode(
            EmailVerificationCode::class,
            $email,
            self::CODE_EXPIRES_SECONDS,
            function (string $code) use ($email): void {
                Mail::to($email)->send(new RegistrationVerificationCodeMail($code));
            },
        );

        // Xóa email đã xác minh cũ trong session nếu có
        $request->session()->forget('registration.verified_email');

        return response()->json([
            'message' => 'Mã xác nhận đã được gửi đến email của bạn.',
            'expires_in' => self::CODE_EXPIRES_SECONDS,
        ]);
    }

    /**
     * API xác minh mã OTP người dùng nhập vào.
     */
    public function verifyCode(VerifyOtpCodeRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $code = $request->validated('code');

        // Kiểm tra mã OTP: đúng mã, chưa hết hạn và chưa bị quá số lần thử
        $this->otpService->verifyCode(EmailVerificationCode::class, $email, $code);

        // Đánh dấu email này đã xác minh thành công vào session để cho phép bước tiếp theo
        $request->session()->put('registration.verified_email', $email);

        return response()->json([
            'message' => 'Email đã được xác minh thành công.',
        ]);
    }
}
