<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        if (request()->filled('redirect')) {
            session()->put('url.intended', request('redirect'));
        }
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Merge guest session cart into user database cart
        if (session()->has('guest_cart')) {
            $guestCart = session()->pull('guest_cart', []);
            $userId = Auth::id();
            foreach ($guestCart as $productId => $qty) {
                $existing = \App\Models\CartItem::where('user_id', $userId)->where('product_id', $productId)->first();
                if ($existing) {
                    $existing->update(['quantity' => $existing->quantity + (int) $qty]);
                } else {
                    \App\Models\CartItem::create([
                        'user_id' => $userId,
                        'product_id' => (int) $productId,
                        'quantity' => (int) $qty,
                    ]);
                }
            }
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
