<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;

Route::get('/test', function () {
    return response()->json(['status' => 'API is working']);
});

Route::post('/tokens/create', [FrontendController::class, 'apiTokenCreate']);

Route::get('/products', [FrontendController::class, 'apiProducts']);
Route::get('/products/search', [FrontendController::class, 'apiProductSearch']);