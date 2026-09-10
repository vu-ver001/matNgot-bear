<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (auth()->check()) {
                $realCartCount = \App\Models\CartItem::where('user_id', auth()->id())->count();
                $realWishlistCount = \App\Models\WishlistItem::where('user_id', auth()->id())->count();
            } else {
                $guestCart = session()->get('guest_cart', []);
                $realCartCount = count($guestCart);
                $realWishlistCount = 0;
            }
            $view->with('realCartCount', (int) $realCartCount);
            $view->with('realWishlistCount', (int) $realWishlistCount);
        });
    }
}
