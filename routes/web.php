<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SignupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteEquipmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/api/docs', function () {
    return response(<<<'HTML'
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Duke Kitchen API Docs</title>
        <link
            rel="stylesheet"
            type="text/css"
            href="https://unpkg.com/swagger-ui-dist/swagger-ui.css"
        />
    </head>
    <body>
        <div id="swagger-ui" />

        <script src="https://unpkg.com/swagger-ui-dist/swagger-ui-bundle.js"></script>
        <script src="https://unpkg.com/swagger-ui-dist/swagger-ui-standalone-preset.js"></script>
        <script>
            window.onload = function () {
                window.ui = SwaggerUIBundle({
                    url: '/api/docs/openapi.yaml',
                    dom_id: '#swagger-ui',
                    presets: [
                        SwaggerUIBundle.presets.apis,
                        SwaggerUIStandalonePreset
                    ]
                });
            };
        </script>
    </body>
</html>
HTML)
        ->header('Content-Type', 'text/html; charset=UTF-8');
});

Route::get('/api/docs/openapi.yaml', function () {
    return response()->file(base_path('openapi.yaml'), [
        'Content-Type' => 'text/yaml; charset=UTF-8',
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::get('/signup', [SignupController::class, 'create'])->name('signup');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/roles', [RolesController::class, 'index'])->name('roles.index');
    Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
    Route::get('/sites/{site}/equipment', SiteEquipmentController::class)->name('sites.equipment');
    Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');
});
