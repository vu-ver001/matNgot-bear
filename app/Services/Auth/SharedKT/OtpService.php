<?php

namespace App\Services\Auth\SharedKT;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpService
{
    private const RESEND_COOLDOWN_SECONDS = 60;

    private const MAX_ATTEMPTS = 5;

    /**
     * Tạo, lưu dạng băm và gửi mã OTP đến người dùng.
     *
     * @param  class-string<Model>  $modelClass
     */
    public function issueCode(
        string $modelClass,
        string $email,
        int $expiresInSeconds,
        Closure $sendCode,
    ): void {
        if (config('mail.default') === 'log') {
            throw ValidationException::withMessages([
                'email' => 'Hệ thống gửi email chưa được cấu hình. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        $code = $this->generateCode();

        DB::transaction(function () use ($modelClass, $email, $expiresInSeconds, $sendCode, $code): void {
            $otpRecord = $modelClass::query()
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if ($otpRecord?->last_sent_at?->gt(now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))) {
                throw ValidationException::withMessages([
                    'email' => 'Vui lòng chờ 60 giây trước khi yêu cầu mã mới.',
                ]);
            }

            $modelClass::query()->updateOrCreate(
                ['email' => $email],
                [
                    'code_hash' => Hash::make($code),
                    'expires_at' => now()->addSeconds($expiresInSeconds),
                    'verified_at' => null,
                    'last_sent_at' => now(),
                    'attempts' => 0,
                ],
            );

            $sendCode($code);
        });
    }

    /**
     * Kiểm tra mã OTP và đánh dấu mã đã được sử dụng khi hợp lệ.
     *
     * @param  class-string<Model>  $modelClass
     */
    public function verifyCode(string $modelClass, string $email, string $code): void
    {
        $error = DB::transaction(function () use ($modelClass, $email, $code): ?string {
            $otpRecord = $modelClass::query()
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if (! $otpRecord) {
                return 'Không tìm thấy yêu cầu xác nhận. Vui lòng gửi mã mới.';
            }

            if ($otpRecord->verified_at !== null) {
                return 'Mã xác nhận này đã được sử dụng.';
            }

            if ($otpRecord->expires_at->isPast()) {
                return 'Mã xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.';
            }

            if ($otpRecord->attempts >= self::MAX_ATTEMPTS) {
                return 'Bạn đã nhập sai quá 5 lần. Vui lòng yêu cầu mã mới.';
            }

            if (! Hash::check($code, $otpRecord->code_hash)) {
                $otpRecord->increment('attempts');

                return $otpRecord->attempts >= self::MAX_ATTEMPTS
                    ? 'Bạn đã nhập sai quá 5 lần. Vui lòng yêu cầu mã mới.'
                    : 'Mã xác nhận không chính xác.';
            }

            $otpRecord->forceFill(['verified_at' => now()])->save();

            return null;
        });

        if ($error !== null) {
            throw ValidationException::withMessages(['code' => $error]);
        }
    }

    /**
     * Sinh ngẫu nhiên mã OTP gồm đúng 6 chữ số.
     */
    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
