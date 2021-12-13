<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Controllers\PhotoController;

//user
Route::get("/login",[UserController::class,'authenticate']);
Route::post("/sign_up",[UserController::class,'register']);
Route::get('/EmailConfirmation/{email}', [UserController::class, 'confirmEmail']);
Route::post('/SendResetLinkResponse', [UserController::class, 'sendResetLinkResponse']);
Route::post('/sendResetResponse/{email}/{token}', [UserController::class, 'sendResetResponse']);

    // Route::get('/send_photo_link_privacy', [PhotoController::class, 'sendPhotoLinkPrivacy']);
    // Route::get('/PhotoLinkAccessPrivacy/{email}/{token}', [PhotoController::class, 'photoLinkAccessPrivacy']);

Route::group(['middleware' => ['verification']], function() {

        Route::get('/logout', [UserController::class, 'logout']);
        Route::get('/get_user', [UserController::class, 'get_user']);
        Route::post('/profile', [UserController::class, 'profile']);

    //     Route::post('/photo', [PhotoController::class, 'uploadPhoto']);
    //     Route::delete('/remove_photo/{id}', [PhotoController::class, 'removePhoto']);
    //     Route::get('/list_all_Photos', [PhotoController::class, 'listAllPhotos']);

    //     Route::get('/photos_find_by_name/{name}', [PhotoController::class, 'photosFindByName']);
    //     Route::get('/photos_find_by_extensions/{extension}', [PhotoController::class, 'photosFindByExtensions']);
    //     Route::get('/photos_find_by_privacy/{privacy}', [PhotoController::class, 'photosFindByPrivacy']);
    //     Route::get('/photos_find_by_time/{created_at}', [PhotoController::class, 'photosFindByTime']);
        
    // Route::get('/send_photo_link_privacy', [PhotoController::class, 'sendPhotoLinkPrivacy']);
    // Route::get('/photoLink/{email}/imageId/{image_id}', [PhotoController::class, 'photoLink']);
    // Route::get('/photoLink/{email}/imageId/{image_id}/{privacy}', [PhotoController::class, 'photoLinkAccessPrivacy']);
        


    });
    