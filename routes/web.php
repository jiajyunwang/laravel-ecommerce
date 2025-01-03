<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Auth\LoginController;

Auth::routes(['register' => false]);

Route::get('/ccc', [FrontendController::class, 'testConnection']);
Route::get('/', [FrontendController::class, 'index'])->name('home');

Route::get('/user/login', [FrontendController::class, 'login'])->name('login.form');
Route::post('/user/login', [FrontendController::class, 'loginSubmit'])->name('login.submit');

Route::get('/logout', [FrontendController::class, 'logout'])->name('logout');

Route::get('/user/register', [FrontendController::class, 'register'])->name('register.form');
Route::post('/user/register', [FrontendController::class, 'registerSubmit'])->name('register.submit');

Route::get('product-detail/{slug}', [FrontendController::class, 'productDetail'])->name('product-detail');
Route::get('/reviews/fetch', [FrontendController::class, 'fetchReviews'])->name('reviews.fetch');

Route::post('/request-action/{slug}', [FrontendController::class, 'requestAction'])->name('request.action');

Route::post('/cart-update', [CartController::class, 'update'])->name('cart.update');

Route::group(['prefix' => '/user', 'middleware' => ['user']], function () {
    Route::get('/', [FrontendController::class, 'index'])->name('user');
    Route::get('/account', [FrontendController::class, 'account'])->name('account.form');
    Route::post('/account', [FrontendController::class, 'accountSubmit'])->name('account.submit');
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::get('/cart-destroy/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/destroy-carts', [CartController::class, 'destroyCarts'])->name('destroy.carts');
    Route::post('/review', [OrderController::class, 'review'])->name('review');
    Route::get('/order', [OrderController::class, 'index'])->name('user.order');
    Route::get('/orders/fetch', [OrderController::class, 'fetchOrders'])->name('orders.fetch');
    Route::get('/order/order-detail/{id}', [OrderController::class, 'orderDetail'])->name('order.detail');
    Route::get('/order/to-completed/{id}', [OrderController::class, 'toCompleted'])->name('to-completed');
    Route::get('/order/to-cancel/{id}', [OrderController::class, 'toCancel'])->name('to-cancel');
    Route::get('/order/{id}/repurchase', [OrderController::class, 'repurchase'])->name('order.repurchase');
    Route::get('/aaa/{id}', [OrderController::class, 'aaa'])->name('aaa');
    Route::get('/bbb/{id}', [OrderController::class, 'bbb'])->name('bbb');
    Route::post('/order/create', [OrderController::class, 'create'])->name('order.create');
    Route::post('/order/store', [OrderController::class, 'store'])->name('order.store');
    Route::get('/chat/messages', [FrontendController::class, 'fetchChatMessages']);
    Route::post('/chat/send', [FrontendController::class, 'sendMessage']);
    Route::get('/chat/chat-list', [FrontendController::class, 'fetchChatList']);
});

Route::group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function () {
    Route::get('/', [AdminController::class, 'purchaseType'])->name('admin');
    Route::get('/product', [AdminController::class, 'purchaseType']);
    Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
    Route::post('/product/store', [ProductController::class, 'store'])->name('product.store');
    Route::post('/product/{id}/edit', [ProductController::class, 'edit'])->name('product.edit');
    Route::post('/product/update', [ProductController::class, 'update'])->name('product.update');
    Route::post('/product/to-inactive/{id}', [ProductController::class, 'toInactive'])->name('to-inactive');
    Route::post('/product/to-active/{id}', [ProductController::class, 'toActive'])->name('to-active');
    Route::post('/product/destroy/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
    Route::get('/order', [AdminController::class, 'purchaseType'])->name('admin.order');
    Route::get('/order/order-detail/{id}', [AdminController::class, 'orderDetail'])->name('admin.order.detail');
    Route::get('/order/to-shipping/{id}', [AdminController::class, 'toShipping'])->name('to-shipping');
    Route::get('/order/to-cancel/{id}', [AdminController::class, 'toCancel'])->name('admin.to-cancel');
    Route::post('/order/search', [AdminController::class, 'searchByOrderNumber'])->name('order.search');
    Route::post('/purchase/destroy-products', [ProductController::class, 'destroyProducts'])->name('destroy.products');
    Route::get('/chat/messages', [AdminController::class, 'fetchChatMessages']);
});
