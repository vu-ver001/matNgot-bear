<?php

use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->name('customer.')->group(function () {
    // 1. Cart routes (hỗ trợ cả customer.cart và customer.cart.index)
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::get('/cart-index', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/log-uncheck', [CartController::class, 'logUncheck'])->name('cart.log_uncheck');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart-clear', [CartController::class, 'clear'])->name('cart.clear');

    // 2. Checkout routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

    // 3. Wishlist
    Route::get('/wishlist', function () {
        return view('customer.placeholder', [
            'pageTitle' => 'Danh Sách Yêu Thích (Wishlist)',
            'pageIcon'  => 'fa-solid fa-heart',
            'pageDesc'  => 'Trang lưu trữ các sản phẩm gấu bông bạn yêu thích để dễ dàng mua sau.',
            'routeCode' => "Route::get('/customer/wishlist', [WishlistController::class, 'index'])->name('customer.wishlist');",
        ]);
    })->name('wishlist');

    // 4. Orders (Anh Vũ)
    Route::middleware(['auth'])->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });

    // 5. Profile
    Route::get('/profile', function () {
        return redirect()->route('profile.edit');
    })->name('profile');
});

// Shortcut alias routes tiện lợi ngoài root
Route::get('/wishlist', fn() => redirect()->route('customer.wishlist'));
Route::get('/cart', fn() => redirect()->route('customer.cart'));
Route::get('/my-orders', fn() => redirect()->route('customer.orders.index'));
