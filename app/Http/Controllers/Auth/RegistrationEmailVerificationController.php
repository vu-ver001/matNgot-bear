<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendRegistrationCodeRequest;
use App\Http\Requests\Auth\VerifyRegistrationCodeRequest;
use App\Mail\RegistrationVerificationCodeMail;
use App\Models\EmailVerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class RegistrationEmailVerificationController extends Controller
{
    private const CODE_EXPIRES_SECONDS = 60;

    private const RESEND_COOLDOWN_SECONDS = 60;

    private const MAX_ATTEMPTS = 5;

    public function sendCode(SendRegistrationCodeRequest $request): JsonResponse
    {
        if (config('mail.default') === 'log') {
            throw ValidationException::withMessages([
                'email' => 'Hệ thống gửi email chưa được cấu hình. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        $email = $request->validated('email');
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($email, $code): void {
            $verification = EmailVerificationCode::query()
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if ($verification?->last_sent_at?->gt(now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))) {
                throw ValidationException::withMessages([
                    'email' => 'Vui lòng chờ 60 giây trước khi yêu cầu mã mới.',
                ]);
            }

            EmailVerificationCode::query()->updateOrCreate(
                ['email' => $email],
                [
                    'code_hash' => Hash::make($code),
                    'expires_at' => now()->addSeconds(self::CODE_EXPIRES_SECONDS),
                    'verified_at' => null,
                    'last_sent_at' => now(),
                    'attempts' => 0,
                ],
            );

            Mail::to($email)->send(new RegistrationVerificationCodeMail($code));
        });

        $request->session()->forget('registration.verified_email');

        return response()->json([
            'message' => 'Mã xác nhận đã được gửi đến email của bạn.',
            'expires_in' => self::CODE_EXPIRES_SECONDS,
        ]);
    }

    public function verifyCode(VerifyRegistrationCodeRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $code = $request->validated('code');

        $error = DB::transaction(function () use ($email, $code): ?string {
            $verification = EmailVerificationCode::query()
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if (! $verification) {
                return 'Không tìm thấy yêu cầu xác nhận. Vui lòng gửi mã mới.';
            }

            if ($verification->verified_at !== null) {
                return 'Mã xác nhận này đã được sử dụng.';
            }

            if ($verification->expires_at->isPast()) {
                return 'Mã xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.';
            }

            if ($verification->attempts >= self::MAX_ATTEMPTS) {
                return 'Bạn đã nhập sai quá 5 lần. Vui lòng yêu cầu mã mới.';
            }

            if (! Hash::check($code, $verification->code_hash)) {
                $verification->increment('attempts');

                return $verification->attempts >= self::MAX_ATTEMPTS
                    ? 'Bạn đã nhập sai quá 5 lần. Vui lòng yêu cầu mã mới.'
                    : 'Mã xác nhận không chính xác.';
            }

            $verification->forceFill(['verified_at' => now()])->save();

            return null;
        });

        if ($error !== null) {
            throw ValidationException::withMessages(['code' => $error]);
        }

        $request->session()->put('registration.verified_email', $email);

        return response()->json([
            'message' => 'Email đã được xác minh thành công.',
        ]);
    }
}
