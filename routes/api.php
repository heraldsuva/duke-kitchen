<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/signup', [AuthController::class, 'signup'])->name('api.auth.signup');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('api.auth.user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    });
});

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::apiResource('users', UserController::class)->except(['show']);
    Route::apiResource('sites', SiteController::class)->except(['show']);
    Route::apiResource('equipment', EquipmentController::class)->except(['show']);
});
