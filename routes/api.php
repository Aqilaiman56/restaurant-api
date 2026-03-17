<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;

Route::post('register', [AuthController::class,'register']);
Route::post('login', [AuthController::class,'login']);

Route::middleware('auth:api')->group(function () {
    // Available to all authenticated users
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('menu-items', [MenuItemController::class, 'index']);
    Route::get('menu-items/{menuItem}', [MenuItemController::class, 'show']);
    Route::get('orders', [OrderController::class, 'index']);

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::post('menu-items', [MenuItemController::class, 'store']);
        Route::put('menu-items/{menuItem}', [MenuItemController::class, 'update']);
        Route::delete('menu-items/{menuItem}', [MenuItemController::class, 'destroy']);
    });

    // Staff only
    Route::middleware('role:staff')->group(function () {
        Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']);
    });

    // Customer only
    Route::middleware('role:customer')->group(function () {
        Route::post('orders', [OrderController::class, 'placeOrder']);
    });
});