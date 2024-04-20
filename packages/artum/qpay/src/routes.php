<?php

use Artum\Qpay\Controllers\QpayController;
use Illuminate\Support\Facades\Route;

// QPay
Route::controller(QpayController::class)->group(
    function () {
        Route::get('/qpay/callback', 'calback')->name('qpay.callback');
        Route::get('/qpay/payment', 'payment')->name('qpay.payment');
        Route::get('/seller/seller-packages/qpay/payment', 'paymentSpp')->name('qpay.paymentSpp');
        Route::get('/qpay/check', 'responseQpay')->name('qpay.check');
        Route::get('/qpay/success', 'success')->name('qpay.success');
    }
);
