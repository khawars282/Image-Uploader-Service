<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;


Route::group(['middleware' => ['verification']], function() {

    //profile
    Route::get('/showProfile/{id}', [UserController::class, 'showProfileById']);
    Route::put('/update/{id}', [UserController::class, 'updateById']);
    Route::delete('/delete/{id}', [UserController::class, 'deleteById']);
    Route::post('/search/{name}', [UserController::class, 'searchByName']);
    });
