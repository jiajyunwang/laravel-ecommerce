<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductController;

Route::get('/test', function () {
    return response()->json(['status' => 'API is working']);
});

Route::post('/tokens/create', [FrontendController::class, 'apiTokenCreate']);

Route::get('/user/info', [FrontendController::class, 'apiUserInfo']);

Route::get('/products', [FrontendController::class, 'apiProducts']);
Route::get('/products/search', [FrontendController::class, 'apiProductSearch']);

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/stats', [AdminController::class, 'apiStats']);
    Route::get('/products', [ProductController::class, 'apiIndex']);
    Route::post('/product/store', [ProductController::class, 'apiStore']);
    Route::post('/product/destroy-products', [ProductController::class, 'apiDestroyProducts']);
    Route::post('/product/to-inactive/{id}', [ProductController::class, 'apiToInactive']);
    Route::post('/product/to-active/{id}', [ProductController::class, 'apiToActive']);
    Route::post('/product/destroy/{id}', [ProductController::class, 'apiDestroy']);
});

Route::middleware('auth')->group(function () {
    Route::get('/user/checkout', [OrderController::class, 'apiCheckout']);
    Route::post('/order/store', [OrderController::class, 'apiStore']);
    Route::get('/user/orders', [OrderController::class, 'apiOrders']);
    Route::get('/user/order/{id}', [OrderController::class, 'apiOrderDetail']);
    Route::post('/review', [OrderController::class, 'apiReview']);
});
