<?php

namespace App\Http\Controllers\PasswordKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordKT\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordController extends Controller
{
    /**
     * Hiển thị trang đổi mật khẩu dùng chung cho tài khoản đã đăng nhập.
     */
    public function edit(): View
    {
        return view('PasswordKT.index');
    }

    /**
     * Đổi mật khẩu của chính người dùng đang đăng nhập.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()
            ->route('account.password.edit')
            ->with('status', 'password-updated');
    }
}
