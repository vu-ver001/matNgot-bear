<?php

use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\OrderController;
use App\Http\Controllers\Staff\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Staff Routes (Phân quyền Nhân viên)
|--------------------------------------------------------------------------
| Anh Vũ: Dashboard vận hành, Đơn hàng, Thanh toán.
*/

Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:STAFF'])->group(function () {

    // Trang chủ Staff (mặc định vào Dashboard vận hành)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý đơn hàng
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    Route::patch('/payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');

    // Route động cho các trang mục chưa làm
    Route::get('/page/{page}', function (string $page) {
        return view('staff.placeholder', ['currentPage' => $page]);
    })->name('page');
});
