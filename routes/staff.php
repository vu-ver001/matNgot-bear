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

    // Trang chủ Staff (mặc định vào Quản lý đơn hàng)
    Route::get('/', fn() => redirect()->route('staff.orders.index'))->name('dashboard');

    // Quản lý đơn hàng
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/bulk-update-status', [OrderController::class, 'bulkUpdateStatus'])->name('orders.bulkUpdateStatus');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    Route::patch('/payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');

    // Các trang mục phụ & placeholder
    Route::get('/order-status', fn() => view('staff.placeholder', ['currentPage' => 'order-status']))->name('order-status.index');
    Route::get('/payments', fn() => view('staff.placeholder', ['currentPage' => 'payments']))->name('payments.index');
    Route::get('/support', fn() => view('staff.placeholder', ['currentPage' => 'support']))->name('support.index');
    Route::get('/page/{page}', function (string $page) {
        return view('staff.placeholder', ['currentPage' => $page]);
    })->name('page');
});
