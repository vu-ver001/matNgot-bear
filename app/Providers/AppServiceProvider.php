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
            $userId = auth()->id() ?? \App\Models\User::where('role', 'CUSTOMER')->first()?->id ?? 1;
            $realCartCount = \App\Models\CartItem::where('user_id', $userId)->count();
            $view->with('realCartCount', $realCartCount);
        });
    }
}
