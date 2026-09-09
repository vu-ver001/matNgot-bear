<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
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

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:ADMIN'])->group(function () {

    // 1. Dashboard & Thống kê (Anh Vũ)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // 2. Báo cáo doanh thu (Anh Vũ)
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/revenue/export', [ReportController::class, 'revenueExport'])->name('reports.revenue.export');

    // 3. Quản lý đơn hàng (Anh Vũ)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/bulk-update-status', [OrderController::class, 'bulkUpdateStatus'])->name('orders.bulkUpdateStatus');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/orders/{order}/approve-cancel', [OrderController::class, 'approveCancel'])->name('orders.approve_cancel');
    Route::post('/orders/{order}/reject-cancel', [OrderController::class, 'rejectCancel'])->name('orders.reject_cancel');
    Route::post('/orders/{order}/confirm-refund', [OrderController::class, 'confirmRefund'])->name('orders.confirm_refund');

    // 3.1 Quản lý thanh toán & Đối soát dòng tiền
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/export', [PaymentController::class, 'export'])->name('payments.export');
    Route::get('/payments/settings', [PaymentController::class, 'settings'])->name('payments.settings');
    Route::post('/payments/settings', [PaymentController::class, 'saveSettings'])->name('payments.saveSettings');
    Route::post('/payments/{payment}/verify-sepay', [PaymentController::class, 'verifySepay'])->name('payments.verifySepay');
    Route::patch('/payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');
    Route::post('/payments/refund-requests/{refundRequest}/approve', [PaymentController::class, 'approveRefund'])->name('payments.approveRefund');
    Route::post('/payments/refund-requests/{refundRequest}/reject', [PaymentController::class, 'rejectRefund'])->name('payments.rejectRefund');
    Route::post('/payments/{payment}/cod-settled', [PaymentController::class, 'markCodSettled'])->name('payments.markCodSettled');
    Route::post('/payments/bulk-cod-settled', [PaymentController::class, 'bulkMarkCodSettled'])->name('payments.bulkMarkCodSettled');

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
    Route::post('/vouchers/{id}/restore', [VoucherController::class, 'restore'])->name('vouchers.restore');
    Route::delete('/vouchers/{id}/force-delete', [VoucherController::class, 'forceDelete'])->name('vouchers.force-delete');
    Route::match(['POST', 'PATCH'], '/vouchers/{voucher}/toggle', [VoucherController::class, 'toggle'])->name('vouchers.toggle');
    Route::resource('vouchers', VoucherController::class)->except(['show']);

    // 7. PHẦN CỦA KHÁNH VÂN: Quản lý Sản phẩm (Trang riêng)
    Route::resource('products', ProductController::class);

    // 8. PHẦN CỦA KHÁNH VÂN: Quản lý Danh mục (Trang riêng)
    Route::get('/categories', function () {
        return view('admin.categories.index', ['currentPage' => 'categories']);
    })->name('categories.index');


    // 9. Placeholder / Hỗ trợ
    Route::get('/customers', fn() => view('admin.placeholder', ['currentPage' => 'customers']))->name('customers.index');
    Route::get('/staff', fn() => view('admin.placeholder', ['currentPage' => 'staff']))->name('staff.index');
    
    // Hỗ trợ khách hàng (Kim Tuyến - Admin xem toàn bộ và nhắn tin như nhân viên)
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ChatKT\StaffChatController::class, 'index'])->name('index');
        Route::get('/{case}', [\App\Http\Controllers\ChatKT\StaffChatController::class, 'show'])->name('show');
        Route::post('/{case}/accept', [\App\Http\Controllers\ChatKT\StaffChatController::class, 'accept'])->name('accept');
        Route::post('/{case}/handover', [\App\Http\Controllers\ChatKT\StaffChatController::class, 'handover'])->name('handover');
        Route::post('/{case}/close', [\App\Http\Controllers\ChatKT\StaffChatController::class, 'close'])->name('close');
        Route::post('/{case}/reopen', [\App\Http\Controllers\ChatKT\StaffChatController::class, 'reopen'])->name('reopen');
        Route::post('/{case}/takeover', [\App\Http\Controllers\ChatKT\StaffChatController::class, 'takeover'])->name('takeover');
        Route::post('/{case}/revoke', [\App\Http\Controllers\ChatKT\StaffChatController::class, 'revoke'])->name('revoke');
        Route::post('/{case}/assign', [\App\Http\Controllers\ChatKT\StaffChatController::class, 'assign'])->name('assign');
        Route::post('/{case}/messages', [\App\Http\Controllers\ChatKT\StaffChatController::class, 'sendMessage'])->name('messages.send');
        Route::get('/{case}/poll', [\App\Http\Controllers\ChatKT\StaffChatController::class, 'poll'])->name('poll');
    });

    Route::get('/page/{page}', function (string $page) {
        if ($page === 'vouchers') {
            return redirect()->route('admin.vouchers.index');
        }
        return view('admin.placeholder', ['currentPage' => $page]);
    })->name('page');
});
