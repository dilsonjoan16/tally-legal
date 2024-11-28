<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'api'], function () {
    // Auth routes.
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');

        Route::group(['middleware' => 'auth.jwt'], function () {
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::post('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });
});
