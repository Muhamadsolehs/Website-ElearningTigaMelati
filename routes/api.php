<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SppController;
use Illuminate\Support\Facades\Route;

Route::post('/payment/create-transaction', [PaymentController::class, 'createTransaction']);
Route::post('/payment/webhook', [PaymentController::class, 'handleWebhook']);
// routes/api.php



Route::post('/payment/spp', [SppController::class, 'createSnap']);
Route::post('/payment/spp/update-status', [SppController::class, 'updateStatus']);
