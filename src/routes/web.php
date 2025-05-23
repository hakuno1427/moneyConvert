<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyController;

Route::get('/', [CurrencyController::class, 'index']);
Route::get('getRates', [CurrencyController::class, 'getRates']);
