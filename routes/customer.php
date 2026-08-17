<?php

use App\Http\Controllers\Customer\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->name('customer.')->middleware(['auth'])->group(function () {
    // Product browsing (Person 2)
    // Route::get('/', [ProductController::class, 'index'])->name('products.index');
    // Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // Cart (Person 3)
    // Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    // Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
    // Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    // Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');

    // Wishlist (Person 1)
    // Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    // Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Checkout (Person 3)
    // Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    // Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Orders (Person 4)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Review (Person 1)
    // Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Chat (Person 1)
    // Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    // Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');
});
