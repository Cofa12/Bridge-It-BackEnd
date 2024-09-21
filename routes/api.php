<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\AuthController;
use Laravel\Socialite\Facades\Socialite;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['middleware'=>'sanitizedCredentials'],function (){
    Route::post('/register',[AuthController::class,'Register']);
    Route::post('/login',[AuthController::class,'login']);
});

    Route::get('/register/twitter',[AuthController::class,'providerRegisterTwitter'])->middleware('web');
    Route::get('register/twitter/redirection',[AuthController::class,'providerRegisterRedirectionTwitter'])->middleware('web');

    Route::get('/register/{provider}',[AuthController::class,'providerRegister']);
    Route::get('register/{provider}/redirection',[AuthController::class,'providerRegisterRedirection']);
    Route::post('/email/forget',[AuthController::class,'requireOTP']);
    Route::post('/email/forget/check',[AuthController::class,'validateOTP']);
    Route::post('/password/change',[AuthController::class,'changePassword']);

    Route::get('Email/Confirm/',[AuthController::class,'confirmEmail'])->name('confirmEmail');
