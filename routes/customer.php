<?php

use Illuminate\Support\Facades\Route;

Route::prefix('customer')->name('customer.')->middleware(['auth', 'role:CUSTOMER'])->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    // Xem và tìm sản phẩm dành cho khách hàng
    // Route::get('/', [ProductController::class, 'index'])->name('products.index');
    // Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // Danh sách sản phẩm yêu thích
    // Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    // Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Đánh giá sản phẩm
    // Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Trò chuyện với nhân viên hỗ trợ
    // Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    // Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');
});
