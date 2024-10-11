<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\AuthController;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\landing\LandingPageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['middleware'=>'sanitizedCredentials'],function (){
    Route::post('/register',[AuthController::class,'Register']);
    Route::post('/login',[AuthController::class,'login']);
});


    Route::get('/register/{provider}',[AuthController::class,'providerRegister']);
    Route::get('register/{provider}/redirection',[AuthController::class,'providerRegisterRedirection']);

    Route::post('/email/forget/requireOTP',[AuthController::class,'requireOTP']);
    Route::post('/email/forget/checkOTP',[AuthController::class,'validateOTP']);
    Route::post('/email/forget/resendOTP',[AuthController::class,'resendOtp']);
    Route::post('/password/change',[AuthController::class,'changePassword']);
    Route::post('/password/change',[AuthController::class,'changePassword']);

    Route::get('Email/Confirm/',[AuthController::class,'confirmEmail'])->name('confirmEmail');


    //landing pages routes
    Route::get('/questions',[LandingPageController::class,'getQuestions']);
    Route::put('/questions/up',[LandingPageController::class,'addPoint']);
