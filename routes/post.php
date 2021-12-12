<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;


Route::group(['middleware' => ['verification']], function() {

        //post
        Route::post('/create', [PostController::class, 'create']);
        Route::put('/update/{title}',  [PostController::class, 'updateByTitle']);
        Route::delete('/delete/{id}',  [PostController::class, 'destroyById']);
        Route::get('/posts/{id}', [PostController::class, 'showById']);
    });
    