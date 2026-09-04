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

class RegistrationEmailVerificationController extends Controller
{
    private const CODE_EXPIRES_SECONDS = 60;

    public function __construct(private readonly OtpService $otpService) {}

    public function sendCode(SendRegistrationCodeRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        $this->otpService->issueCode(
            EmailVerificationCode::class,
            $email,
            self::CODE_EXPIRES_SECONDS,
            function (string $code) use ($email): void {
                Mail::to($email)->send(new RegistrationVerificationCodeMail($code));
            },
        );

        $request->session()->forget('registration.verified_email');

        return response()->json([
            'message' => 'Mã xác nhận đã được gửi đến email của bạn.',
            'expires_in' => self::CODE_EXPIRES_SECONDS,
        ]);
    }

    public function verifyCode(VerifyOtpCodeRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $code = $request->validated('code');

        $this->otpService->verifyCode(EmailVerificationCode::class, $email, $code);

        $request->session()->put('registration.verified_email', $email);

        return response()->json([
            'message' => 'Email đã được xác minh thành công.',
        ]);
    }
}
