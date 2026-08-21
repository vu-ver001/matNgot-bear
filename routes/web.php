<?php

use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\ProfileController;
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
Route::get('/dashboard', function () {
    $user = auth()->user();
    return match ($user->role) {
        'ADMIN' => redirect()->route('admin.dashboard'),
        'STAFF' => redirect()->route('staff.dashboard'),
        default => redirect()->route('home'),
    };
})->middleware(['auth', 'verified']);

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
        'staff'    => 'staff@matngotbear.com',
        'customer' => 'customer@matngotbear.com',
        default    => 'customer@matngotbear.com',
    };

    $user = \App\Models\User::where('email', $email)->first();
    if ($user) {
        auth()->login($user);
    }
    return back()->with('status', "Đã chuyển sang tài khoản: {$user?->full_name} ({$user?->role})");
})->name('switch-role');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/customer.php';
require __DIR__.'/staff.php';
require __DIR__.'/admin.php';
