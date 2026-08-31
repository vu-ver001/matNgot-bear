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
})->middleware(['auth', 'verified'])->name('dashboard');

// Tiện ích chuyển nhanh vai trò (Admin / Staff / Khách hàng / Guest) để test giao diện
Route::get('/switch-role/{role}', function (string $role) {
    if (strtolower($role) === 'guest') {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('home')->with('status', 'Đã chuyển sang trạng thái Khách vãng lai (Guest)!');
    }

    $roleEnum = match (strtolower($role)) {
        'admin'    => 'ADMIN',
        'staff'    => 'STAFF',
        'customer' => 'CUSTOMER',
        default    => 'CUSTOMER',
    };

    $user = \App\Models\User::where('role', $roleEnum)->first();
    if (!$user) {
        // Fallback: Tạo user demo nếu chưa có
        $defaults = [
            'ADMIN'    => ['email' => 'admin@matngotbear.com', 'name' => 'Quản Trị Viên (Admin)'],
            'STAFF'    => ['email' => 'staff1@matngotbear.com', 'name' => 'Nhân Viên CSKH (Staff)'],
            'CUSTOMER' => ['email' => 'customer@matngot.com', 'name' => 'Nguyễn Văn Khách'],
        ];
        $def = $defaults[$roleEnum] ?? $defaults['CUSTOMER'];
        $user = \App\Models\User::firstOrCreate(
            ['email' => $def['email']],
            ['full_name' => $def['name'], 'role' => $roleEnum, 'password' => bcrypt('password')]
        );
    }

    if ($user) {
        auth()->login($user);
    }

    if (session()->has('url.intended')) {
        return redirect()->intended();
    }

    // Nếu đang ở trang login, chuyển hướng thẳng đến dashboard tương ứng
    $previousUrl = url()->previous();
    if (str_contains($previousUrl, '/login') || str_contains($previousUrl, '/register')) {
        return match ($roleEnum) {
            'ADMIN' => redirect()->route('admin.dashboard')->with('status', "Đã đăng nhập: {$user?->full_name} (Admin)"),
            'STAFF' => redirect()->route('staff.dashboard')->with('status', "Đã đăng nhập: {$user?->full_name} (Staff)"),
            default => redirect()->route('home')->with('status', "Đã đăng nhập: {$user?->full_name}"),
        };
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
