<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FriendController;

Route::group(['middleware' => ['verification']], function() {
    //frind
    Route::post('/sendRequest/{id}', [FriendController::class, 'sendRequestById']);
    Route::get('/showRequests', [FriendController::class, 'showRequests']);
    Route::get('/acceptRequest/{id}', [FriendController::class, 'acceptRequestById']);
    Route::get('/deleteRequest/{id}', [FriendController::class, 'deleteRequestById']);
    Route::get('/removeFriend/{id}', [FriendController::class, 'removeFriendById']);
    });
    