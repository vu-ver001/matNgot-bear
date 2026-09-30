<?php

namespace App\Http\Controllers\PasswordKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordKT\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

// Chức năng: Đổi mật khẩu tài khoản khi đã đăng nhập (PasswordKT)
class PasswordController extends Controller
{
    /**
     * [Giao diện] Hiển thị trang đổi mật khẩu (PasswordKT/index.blade.php).
     */
    public function edit(): View
    {
        return view('PasswordKT.index');
    }

    /**
     * Cập nhật mật khẩu mới của người dùng sau khi đã xác thực mật khẩu cũ.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        // Băm mật khẩu mới bằng Hash::make và lưu vào CSDL
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()
            ->route('account.password.edit')
            ->with('status', 'password-updated');
    }
}
