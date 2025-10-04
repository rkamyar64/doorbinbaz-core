<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/user-orders/{id}', [OrderController::class, 'orders']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::get('/user/show', [AuthController::class, 'show']);

        Route::prefix('businesses')->group(function () {
            Route::get('/show', [BusinessController::class, 'index']);
            Route::post('/store', [BusinessController::class, 'store']);
            Route::post('/{business}', [BusinessController::class, 'update']);
        });

        Route::prefix('services')->group(function () {
            Route::get('/show', [ServiceController::class, 'index']);
            Route::post('/store', [ServiceController::class, 'store']);
            Route::post('/{service}', [ServiceController::class, 'update']);
        });

        Route::prefix('orders')->group(function () {
            Route::get('/show', [OrderController::class, 'index']);
            Route::post('/store', [OrderController::class, 'store']);
            Route::post('/{orders}', [OrderController::class, 'update']);
        });

    });

//    //دسترسی ویزیتور
//    Route::middleware(['role:ROLE_VISITOR'])->group(function () {
//        Route::middleware('auth:sanctum')->group(function () {
//
//        });
//    });

    Route::middleware(['role:ROLE_ADMIN'])->group(function () {
        Route::get('/users', [AuthController::class, 'getAllUsers']);
    });


    Route::get('/import-products', [\App\Http\Controllers\ArioController::class, 'import']);
    Route::get('/products', [\App\Http\Controllers\ArioController::class, 'index']);


});

