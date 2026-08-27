<?php

use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\WishlistKT\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->name('customer.')->middleware(['auth', 'role:CUSTOMER'])->group(function () {
    Route::get('/', fn () => redirect()->route('profile.edit'))->name('dashboard');

    // Xem và tìm sản phẩm dành cho khách hàng
    // Route::get('/', [ProductController::class, 'index'])->name('products.index');
    // Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // Danh sách sản phẩm yêu thích
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::delete('/', [WishlistController::class, 'clear'])->name('clear');
        Route::delete('/{product}', [WishlistController::class, 'destroy'])->name('destroy');
    });

    // Thêm một sản phẩm vào giỏ hoặc tăng số lượng nếu sản phẩm đã có.
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');

    // Đánh giá sản phẩm
    // Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Trò chuyện với nhân viên hỗ trợ
    // Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    // Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');
});
