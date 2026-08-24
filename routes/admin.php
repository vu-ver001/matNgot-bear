<?php

use App\Http\Controllers\Admin\VoucherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (Phân quyền Admin)
|--------------------------------------------------------------------------
| Phần của bạn Khánh Vân: Quản lý Sản phẩm & Danh mục.
| Các mục khác được tạo khung sẵn (Placeholder) để bạn trong nhóm cắm link vào.
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // 1. Dashboard & Thống kê
    Route::get('/', function () {
        return view('admin.placeholder', ['currentPage' => 'dashboard']);
    })->name('dashboard');

    Route::get('/dashboard', function () {
        return view('admin.placeholder', ['currentPage' => 'dashboard']);
    });

    // Voucher management
    Route::post('/vouchers/{id}/restore', [VoucherController::class, 'restore'])->name('vouchers.restore');
    Route::delete('/vouchers/{id}/force-delete', [VoucherController::class, 'forceDelete'])->name('vouchers.force-delete');
    Route::match(['POST', 'PATCH'], '/vouchers/{voucher}/toggle', [VoucherController::class, 'toggle'])->name('vouchers.toggle');
    Route::resource('vouchers', VoucherController::class)->except(['show']);
    // 2. PHẦN CỦA KHÁNH VÂN: Quản lý Sản phẩm (Trang riêng)
    Route::get('/products', function () {
        return view('admin.products.index', ['currentPage' => 'products']);
    })->name('products.index');

    // 3. PHẦN CỦA KHÁNH VÂN: Quản lý Danh mục (Trang riêng)
    Route::get('/categories', function () {
        return view('admin.categories.index', ['currentPage' => 'categories']);
    })->name('categories.index');

    // 4. Các mục của bạn trong nhóm (Khung hiển thị placeholder)
    Route::get('/page/vouchers', fn () => redirect()->route('admin.vouchers.index'));
    Route::get('/page/{page}', function (string $page) {
        if ($page === 'vouchers') {
            return redirect()->route('admin.vouchers.index');
        }
        return view('admin.placeholder', ['currentPage' => $page]);
    })->name('page');

    // Shortcut routes cho từng mục để tiện link
    Route::get('/orders', fn () => view('admin.placeholder', ['currentPage' => 'orders']))->name('orders.index');
    Route::get('/payments', fn () => view('admin.placeholder', ['currentPage' => 'payments']))->name('payments.index');
    Route::get('/customers', fn () => view('admin.placeholder', ['currentPage' => 'customers']))->name('customers.index');
    Route::get('/staff', fn () => view('admin.placeholder', ['currentPage' => 'staff']))->name('staff.index');
    Route::get('/reviews', fn () => view('admin.placeholder', ['currentPage' => 'reviews']))->name('reviews.index');
    Route::get('/support', fn () => view('admin.placeholder', ['currentPage' => 'support']))->name('support.index');
});
