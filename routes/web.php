<?php

use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\PasswordKT\PasswordController;
use App\Http\Controllers\ProfileKT\ProfileController;
use App\Http\Controllers\ProfileKT\ProfileEmailController;
use App\Support\RoleRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. CUSTOMER PUBLIC PAGES
// ==========================================
Route::get('/', [CustomerProductController::class, 'home'])->name('home');
Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [CustomerProductController::class, 'show'])->name('products.show');

// ==========================================
// 2. AUTH & DASHBOARD
// ==========================================
Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    // Redirect by role if user is logged in
    if ($user) {
        return match ($user->role) {
            'ADMIN' => redirect()->route('admin.dashboard'),
            'STAFF' => redirect()->route('staff.dashboard'),
            default => redirect()->route('home'),
        };
    }

    return redirect()->route(RoleRedirect::routeName($user));
})->middleware(['auth', 'verified'])->name('dashboard');

// Tiện ích chuyển nhanh vai trò (Admin / Staff / Khách hàng / Guest) để test giao diện
Route::get('/switch-role/{role}', function (string $role) {
    if ($role === 'guest') {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return back()->with('status', 'Đã chuyển sang trạng thái Khách vãng lai (Guest)!');
    }

    $email = match ($role) {
        'admin'    => 'admin@matngotbear.com',
        'staff'    => 'staff1@matngotbear.com',
        'customer' => 'nguyenvana@example.com',
        default    => 'nguyenvana@example.com',
    };

    $user = \App\Models\User::where('email', $email)->first();
    if ($user) {
        auth()->login($user);
    }
    return back()->with('status', "Đã chuyển sang tài khoản: {$user?->full_name} ({$user?->role})");
})->name('switch-role');

Route::middleware(['auth'])->group(function () {
    Route::get('/account/password', [PasswordController::class, 'edit'])->name('account.password.edit');
    Route::put('/account/password', [PasswordController::class, 'update'])->name('account.password.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/email/code', [ProfileEmailController::class, 'sendCode'])->name('profile.email.code');
    Route::patch('/profile/email', [ProfileEmailController::class, 'verifyCode'])->name('profile.email.verify');
    Route::delete('/profile/email', [ProfileEmailController::class, 'cancel'])->name('profile.email.cancel');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/customer.php';
require __DIR__.'/staff.php';
require __DIR__.'/admin.php';
