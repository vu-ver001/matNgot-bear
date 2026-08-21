<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (Phân quyền Admin)
|--------------------------------------------------------------------------
| Khánh Vân: Sản phẩm & Danh mục | Ngọc Anh: Voucher
| Anh Vũ: Đơn hàng, Người dùng, Đánh giá, Báo cáo | Kim Tuyến: Khác.
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // 1. Dashboard & Thống kê (Anh Vũ)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // 2. Báo cáo doanh thu (Anh Vũ)
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/revenue/export', [ReportController::class, 'revenueExport'])->name('reports.revenue.export');

    // 3. Quản lý đơn hàng (Anh Vũ)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    Route::patch('/payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');

    // 4. Quản lý người dùng (Anh Vũ)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.updateStatus');

    // 5. Quản lý đánh giá (Anh Vũ)
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/toggle', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // 6. Voucher management (Ngọc Anh)
    Route::patch('/vouchers/{voucher}/toggle', [VoucherController::class, 'toggle'])->name('vouchers.toggle');
    Route::resource('vouchers', VoucherController::class)->except(['show']);

    // 7. PHẦN CỦA KHÁNH VÂN: Quản lý Sản phẩm (Trang riêng)
    Route::get('/products', function () {
        return view('admin.products.index', ['currentPage' => 'products']);
    })->name('products.index');

    // 8. PHẦN CỦA KHÁNH VÂN: Quản lý Danh mục (Trang riêng)
    Route::get('/categories', function () {
        return view('admin.categories.index', ['currentPage' => 'categories']);
    })->name('categories.index');

    // 9. Placeholder / Hỗ trợ
    Route::get('/payments', fn() => view('admin.placeholder', ['currentPage' => 'payments']))->name('payments.index');
    Route::get('/customers', fn() => view('admin.placeholder', ['currentPage' => 'customers']))->name('customers.index');
    Route::get('/staff', fn() => view('admin.placeholder', ['currentPage' => 'staff']))->name('staff.index');
    Route::get('/support', fn() => view('admin.placeholder', ['currentPage' => 'support']))->name('support.index');
    Route::get('/page/{page}', function (string $page) {
        return view('admin.placeholder', ['currentPage' => $page]);
    })->name('page');
});
