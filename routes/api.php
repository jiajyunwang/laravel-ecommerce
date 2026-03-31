<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\OrderController;

Route::get('/test', function () {
    return response()->json(['status' => 'API is working']);
});

Route::post('/tokens/create', [FrontendController::class, 'apiTokenCreate']);

Route::get('/user/info', [FrontendController::class, 'apiUserInfo']);

Route::get('/products', [FrontendController::class, 'apiProducts']);
Route::get('/products/search', [FrontendController::class, 'apiProductSearch']);

Route::middleware('auth')->group(function () {
    Route::get('/user/checkout', [OrderController::class, 'apiCheckout']);
    Route::post('/order/store', [OrderController::class, 'apiStore']);
    Route::get('/user/orders', [OrderController::class, 'apiOrders']);
    Route::get('/user/order/{id}', [OrderController::class, 'apiOrderDetail']);
    Route::post('/review', [OrderController::class, 'apiReview']);
});
