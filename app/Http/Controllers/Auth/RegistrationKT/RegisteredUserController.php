<?php

namespace App\Http\Controllers\Auth\RegistrationKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrationKT\RegisterCustomerRequest;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Support\RoleRedirect;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Hiển thị trang đăng ký.
     */
    public function create(): View
    {
        return view('auth.registrationKT.index');
    }

    /**
     * Xử lý yêu cầu đăng ký tài khoản mới.
     *
     * @throws ValidationException
     */
    public function store(RegisterCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->session()->get('registration.verified_email') !== $data['email']) {
            throw ValidationException::withMessages([
                'email' => 'Vui lòng xác minh email trước khi đăng ký.',
            ]);
        }

        $user = DB::transaction(function () use ($data): User {
            $verification = EmailVerificationCode::query()
                ->where('email', $data['email'])
                ->whereNotNull('verified_at')
                ->lockForUpdate()
                ->first();

            if (! $verification) {
                throw ValidationException::withMessages([
                    'email' => 'Vui lòng xác minh email trước khi đăng ký.',
                ]);
            }

            $user = User::query()->create([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
            ]);

            $verification->delete();

            return $user;
        });

        event(new Registered($user));

        $user->recordLogin();
        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->forget('registration.verified_email');

        return redirect()->route(RoleRedirect::routeName($user));
    }
}
