<?php

use App\Http\Controllers\OrganismeController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('organisme')->group(function(){
        Route::controller(OrganismeController::class)->group(function(){
            Route::get('/','index')->name('organisme.index');
            Route::post('/store','store')->name('organisme.store');
            Route::get('/show/{organisme}','show')->name('organisme.show');
            Route::put('/update/{organisme}','update')->name('organisme.update');
            Route::delete('/delete/{organisme}', 'destroy')->name('organisme.destroy');
        });
    });
});