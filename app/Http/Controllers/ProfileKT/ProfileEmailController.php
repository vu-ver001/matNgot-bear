<?php

namespace App\Http\Controllers\ProfileKT;

use App\Http\Controllers\Controller;
use App\Mail\ProfileKT\EmailChangeCodeMail;
use App\Models\EmailChangeCode;
use App\Models\User;
use App\Services\Auth\SharedKT\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileEmailController extends Controller
{
    private const CODE_EXPIRES_SECONDS = 300;

    public function __construct(private readonly OtpService $otpService) {}

    public function sendCode(Request $request): RedirectResponse|JsonResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::notIn([$request->user()->email]),
                Rule::unique(User::class, 'email'),
            ],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'email.not_in' => 'Email mới phải khác email hiện tại.',
            'email.unique' => 'Email này đã được sử dụng.',
        ]);

        EmailChangeCode::query()
            ->where('user_id', $request->user()->id)
            ->where('email', '!=', $data['email'])
            ->delete();

        $this->otpService->issueCode(
            EmailChangeCode::class,
            $data['email'],
            self::CODE_EXPIRES_SECONDS,
            function (string $code) use ($data): void {
                Mail::to($data['email'])->send(new EmailChangeCodeMail($code));
            },
            ['user_id' => $request->user()->id],
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Mã xác nhận đã được gửi đến email mới.',
                'email' => $data['email'],
            ]);
        }

        return back()->with('status', 'email-change-code-sent');
    }

    public function verifyCode(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Vui lòng nhập mã xác nhận.',
            'code.digits' => 'Mã xác nhận phải gồm đúng 6 chữ số.',
        ]);

        $changeRequest = EmailChangeCode::query()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $changeRequest) {
            throw ValidationException::withMessages([
                'code' => 'Không tìm thấy yêu cầu đổi email. Vui lòng gửi mã mới.',
            ]);
        }

        $this->otpService->verifyCode(EmailChangeCode::class, $changeRequest->email, $data['code']);

        DB::transaction(function () use ($request, $changeRequest): void {
            $emailWasTaken = User::query()
                ->where('email', $changeRequest->email)
                ->where('id', '!=', $request->user()->id)
                ->exists();

            if ($emailWasTaken) {
                throw ValidationException::withMessages([
                    'email' => 'Email này vừa được sử dụng bởi tài khoản khác.',
                ]);
            }

            $request->user()->forceFill([
                'email' => $changeRequest->email,
                'email_verified_at' => now(),
            ])->save();

            $changeRequest->delete();
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đổi email thành công. Lần đăng nhập sau hãy dùng email mới.',
                'email' => $request->user()->email,
            ]);
        }

        return back()->with('status', 'email-updated');
    }

    public function cancel(Request $request): RedirectResponse|JsonResponse
    {
        EmailChangeCode::query()
            ->where('user_id', $request->user()->id)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã hủy yêu cầu đổi email.',
            ]);
        }

        return back()->with('status', 'email-change-cancelled');
    }
}
