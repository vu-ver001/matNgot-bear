<?php

namespace App\Http\Controllers\Auth\LoginKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginKT\LoginRequest;
use App\Support\RoleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Chức năng: Đăng nhập & Đăng xuất tài khoản (LoginKT)
class AuthenticatedSessionController extends Controller
{
    /**
     * [Giao diện] Hiển thị trang đăng nhập (auth/loginKT/index.blade.php).
     */
    public function create(Request $request): View
    {
        $redirectPath = $this->safeRedirectPath($request->query('redirect'));

        if ($redirectPath !== null) {
            $request->session()->put('url.intended', url($redirectPath));
        }

        return view('auth.loginKT.index');
    }

    /**
     * Xử lý đăng nhập: xác thực thông tin, ghi nhận thời gian và chuyển hướng theo vai trò.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->user()->recordLogin();

        $request->session()->regenerate();

        return redirect()->intended(route(RoleRedirect::routeName($request->user()), absolute: false));
    }

    /**
     * Xử lý đăng xuất: đăng xuất tài khoản, hủy session và làm mới token CSRF.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Kiểm tra đường dẫn chuyển hướng chỉ nằm trong website (chống lỗi Open Redirect).
     */
    private function safeRedirectPath(mixed $redirect): ?string
    {
        if (! is_string($redirect)
            || ! str_starts_with($redirect, '/')
            || str_starts_with($redirect, '//')) {
            return null;
        }

        return $redirect;
    }
}
