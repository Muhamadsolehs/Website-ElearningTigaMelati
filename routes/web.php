<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SppController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/midtrans-finish', function () {
    return view('midtrans-finish');
})->name('midtrans.finish');

Route::get('/midtrans/finish-spp', [SppController::class, 'finishSpp'])->name('midtrans.finishSpp');


