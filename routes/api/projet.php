<?php

use App\Http\Controllers\ProjetController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function(){
    Route::prefix('projet')->group(function(){
        Route::controller(ProjetController::class)->group(function(){
            Route::get('/','index')->name('projet.index');
            Route::get('/promuleguer','promulegue')->name('projet.promulegue');
            Route::get('/nonPromulegue','nonPromulegue')->name('projet.nonPromulegue');
            Route::get('/avoter','Avoter')->name('projet.Avoter');
            Route::post('/voter','voter')->name('projet.voter');
            Route::post('/store','store')->name('projet.store');
            Route::get('/show/{projet}','show')->name('projet.show');
            Route::put('/update/{projet}','update')->name('projet.update');
            Route::delete('/delete/{projet}', 'destroy')->name('projet.destroy');
        });
    });
});