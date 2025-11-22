<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function(){
    Route::controller(UserController::class)->group(function(){
        Route::post('/login','login')->name('user.login');
        Route::post('/register','register')->name('user.register');
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::controller(UserController::class)->group(function(){
            Route::get('/me','user')->name('user.user');
            Route::post('/logout', 'logout')->name('user.logout');
            Route::post('/refresh', 'refresh_token')->name('user.refresh');
        });
    });
});

