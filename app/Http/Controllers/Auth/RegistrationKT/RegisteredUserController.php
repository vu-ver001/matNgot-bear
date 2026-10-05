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

// Chức năng: Đăng ký tài khoản khách hàng (RegistrationKT)
class RegisteredUserController extends Controller
{
    /**
     * [Giao diện] Hiển thị trang đăng ký 3 bước (auth/registrationKT/index.blade.php).
     */
    public function create(): View
    {
        return view('auth.registrationKT.index');
    }

    /**
     * Xử lý đăng ký: kiểm tra OTP email, tạo user CUSTOMER và tự động đăng nhập.
     *
     * @throws ValidationException
     */
    public function store(RegisterCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Kiểm tra xem email này đã xác minh OTP trong session hay chưa
        if ($request->session()->get('registration.verified_email') !== $data['email']) {
            throw ValidationException::withMessages([
                'email' => 'Vui lòng xác minh email trước khi đăng ký.',
            ]);
        }

        // Bắt đầu transaction để đảm bảo tạo user và xóa OTP diễn ra an toàn
        $user = DB::transaction(function () use ($data): User {
            // Kiểm tra trạng thái mã OTP đã xác minh trong CSDL (khóa dòng chống trùng lặp)
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

            // Tạo tài khoản khách hàng mới (CUSTOMER, kích hoạt sẵn)
            $user = User::query()->create([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
            ]);

            // Xóa bản ghi OTP đã dùng
            $verification->delete();

            return $user;
        });

        event(new Registered($user));

        // Lưu thời gian đăng nhập và tự động đăng nhập người dùng
        $user->recordLogin();
        Auth::login($user);

        // Làm mới session và xóa trạng thái OTP tạm
        $request->session()->regenerate();
        $request->session()->forget('registration.verified_email');

        // Chuyển hướng theo vai trò (khách hàng vào trang chủ/dashboard)
        return redirect()->route(RoleRedirect::routeName($user));
    }
}
