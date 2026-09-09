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

    // Trang chủ Staff (Dashboard vận hành)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý đơn hàng
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/bulk-update-status', [OrderController::class, 'bulkUpdateStatus'])->name('orders.bulkUpdateStatus');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/orders/{order}/approve-cancel', [OrderController::class, 'approveCancel'])->name('orders.approve_cancel');
    Route::post('/orders/{order}/reject-cancel', [OrderController::class, 'rejectCancel'])->name('orders.reject_cancel');
    Route::post('/orders/{order}/confirm-refund', [OrderController::class, 'confirmRefund'])->name('orders.confirm_refund');

    // Quản lý thanh toán & Đối soát theo phân quyền Nhân viên vận hành
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::patch('/payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');
    Route::post('/payments/{payment}/manual-confirm', [PaymentController::class, 'manualConfirm'])->name('payments.manualConfirm');
    Route::post('/payments/{payment}/refund-request', [PaymentController::class, 'requestRefund'])->name('payments.requestRefund');
    Route::post('/payments/{payment}/reconcile-cod', [PaymentController::class, 'reconcileCod'])->name('payments.reconcileCod');
    Route::post('/payments/bulk-reconcile-cod', [PaymentController::class, 'bulkReconcileCod'])->name('payments.bulkReconcileCod');
    Route::get('/payments/cod-export', [PaymentController::class, 'codExport'])->name('payments.codExport');

    // Chức năng 5: Cấu hình cổng & API thanh toán -> Khóa hoàn toàn cho nhân viên
    Route::get('/payments/settings', function () {
        abort(403, 'Bạn không có quyền truy cập cấu hình cổng & API thanh toán. Chỉ Admin mới có quyền.');
    })->name('payments.settings');

    // Các trang mục phụ & placeholder
    Route::get('/order-status', fn() => view('staff.placeholder', ['currentPage' => 'order-status']))->name('order-status.index');
    Route::get('/support', fn() => view('staff.placeholder', ['currentPage' => 'support']))->name('support.index');
    Route::get('/page/{page}', function (string $page) {
        return view('staff.placeholder', ['currentPage' => $page]);
    })->name('page');
});
