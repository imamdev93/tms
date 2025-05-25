<?php

use App\Http\Controllers\PaymentController;
use App\Livewire\Register;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    if (auth()->user()) {
        return redirect()->to('/tms');
    }

    return redirect()->route('register');
});

Route::get(
    '/register',
    Register::class
)->middleware('guest')->name('register');

// Payment Routes
// Route::get('/payment/{orderId}', [PaymentController::class, 'showPayment'])->name('payment.show');
// Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
// Route::get('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');
