<?php

namespace App\Http\Controllers\Auth\LoginKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginKT\LoginRequest;
use App\Support\RoleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Hiển thị trang đăng nhập.
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
     * Xử lý yêu cầu đăng nhập.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->user()->recordLogin();
        $request->session()->regenerate();

        return redirect()->intended(route(RoleRedirect::routeName($request->user()), absolute: false));
    }

    /**
     * Đăng xuất và hủy phiên đăng nhập hiện tại.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Chỉ cho phép quay lại đường dẫn bên trong website hiện tại.
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
