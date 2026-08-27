<?php

use App\Http\Controllers\ProfileKT\ProfileController;
use App\Http\Controllers\ProfileKT\ProfileEmailController;
use App\Support\RoleRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function (Request $request) {
    return redirect()->route(RoleRedirect::routeName($request->user()));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/email/code', [ProfileEmailController::class, 'sendCode'])->name('profile.email.code');
    Route::patch('/profile/email', [ProfileEmailController::class, 'verifyCode'])->name('profile.email.verify');
    Route::delete('/profile/email', [ProfileEmailController::class, 'cancel'])->name('profile.email.cancel');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/customer.php';
require __DIR__.'/staff.php';
require __DIR__.'/admin.php';
