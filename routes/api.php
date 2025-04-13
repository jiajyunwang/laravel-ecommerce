<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;

Route::get('/test', function () {
    return response()->json(['status' => 'API is working']);
});

Route::post('/tokens/create', [FrontendController::class, 'apiTokenCreate']);