<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\AuthController;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\landing\LandingPageController;
use App\Http\Controllers\group\GroupController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['middleware'=>'sanitizedCredentials'],function (){
    Route::post('/register',[AuthController::class,'Register']);
    Route::post('/login',[AuthController::class,'login'])->name('login');
});


    Route::get('/register/{provider}',[AuthController::class,'providerRegister']);
    Route::get('/register/{provider}/redirection',[AuthController::class,'providerRegisterRedirection']);
    Route::get('/credentials/fetch/',[AuthController::class,'getCredentialsUser'])->name('credentials');

    Route::post('/email/forget/requireOTP',[AuthController::class,'requireOTP']);
    Route::post('/email/forget/checkOTP',[AuthController::class,'validateOTP']);
    Route::post('/email/forget/resendOTP',[AuthController::class,'resendOtp']);
    Route::post('/password/change',[AuthController::class,'changePassword']);
    Route::post('/password/change',[AuthController::class,'changePassword']);

    Route::get('Email/Confirm/',[AuthController::class,'confirmEmail'])->name('confirmEmail');


    //landing pages routes
    Route::get('/questions',[LandingPageController::class,'getQuestions']);
    Route::post('/question/add',[LandingPageController::class,'addQuestion']);
    Route::put('/questions/up',[LandingPageController::class,'addPoint']);
    Route::post('/subscription',[LandingPageController::class,'getSubscription']);
    Route::get('/subscription/send',[LandingPageController::class,'sendSubscription']);


    // group routes
//    Route::apiResource('/group',GroupController::class);

    Route::group(['middleware'=>'auth:sanctum'],function (){
       Route::get('/groups',[GroupController::class,'index']);
        Route::post('/groups/store',[GroupController::class,'store']);

        Route::delete('/groups/destroy',[GroupController::class,'destroy'])->middleware(['isFoundGroup','isAdmin']);
        Route::put('/groups/update',[GroupController::class,'update'])->middleware(['isFoundGroup','isAdmin']);
        Route::post('/groups/searchedGroups',[GroupController::class,'searchUsingName']);
        Route::get('/groups/{id}',[GroupController::class,'getGroupWithID']);
    });
