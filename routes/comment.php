<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

//comment
Route::group(['middleware' => ['verification']], function() {

         //comment
         Route::get('/showcomment', [CommentController::class, 'showComments']);
         Route::post('/post/{id}/createComment', [CommentController::class, 'create']);
         Route::put('/updateComment/{id}',  [CommentController::class, 'update']);
         Route::delete('/deleteComment/{id}',  [CommentController::class, 'delete']);
        
    });
    