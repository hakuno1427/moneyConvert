<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyController;

Route::get('/', [CurrencyController::class, 'index']);
Route::get('/api/getLatest', [CurrencyController::class, 'getLatest']);
Route::get('/api/history/{currency}', [CurrencyController::class, 'history']);
