<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        if (request()->filled('redirect')) {
            session()->put('url.intended', request('redirect'));
        }
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->whereNull('deleted_at')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Merge guest session cart into user database cart
        if (session()->has('guest_cart')) {
            $guestCart = session()->pull('guest_cart', []);
            foreach ($guestCart as $productId => $qty) {
                \App\Models\CartItem::create([
                    'user_id' => $user->id,
                    'product_id' => (int) $productId,
                    'quantity' => (int) $qty,
                ]);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
