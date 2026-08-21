<?php

use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->name('customer.')->group(function () {
    // Product browsing (Customer)
    // Route::get('/', [ProductController::class, 'index'])->name('products.index');
    // Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // Cart routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart-clear', [CartController::class, 'clear'])->name('cart.clear');

    // Checkout routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

    // Wishlist
    // Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    // Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Review
    // Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Chat
    // Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    // Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');
});

