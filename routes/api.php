<?php

use App\Http\Controllers\ShopifyProductController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'shopify'], function () {
        Route::post('/products', [ShopifyProductController::class, 'store']);
    });
});
