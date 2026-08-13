<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\SignupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteEquipmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/signup', [SignupController::class, 'create'])->name('signup');
    Route::post('/signup', [SignupController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);
    Route::resource('sites', SiteController::class)->except(['show', 'create', 'edit']);
    Route::get('/sites/{site}/equipment', SiteEquipmentController::class)->name('sites.equipment');
    Route::resource('equipment', EquipmentController::class)->except(['show', 'create', 'edit']);
});
