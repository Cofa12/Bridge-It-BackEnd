<?php

use App\Http\Controllers\Task\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\landing\LandingPageController;
use App\Http\Controllers\group\GroupController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



    Route::post('/register',[AuthController::class,'Register']);
    Route::post('/login',[AuthController::class,'login']);


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
    // @todo : add the route to change the permissions
    // @todo : add the routes of adding members and doctor and flow


    Route::group(['middleware'=>'auth:sanctum'],function (){
       Route::get('/groups',[GroupController::class,'index']);
        Route::post('/groups/store',[GroupController::class,'store']);
        Route::post('/groups/searchedGroups',[GroupController::class,'searchUsingName']);
        Route::get('/groups/{id}',[GroupController::class,'getGroupWithID']);
        Route::get('/groups/{groupId}/members',[GroupController::class,'getGroupMembers']);
        Route::group(['middleware'=>['isFoundGroup','isAdmin']],function(){
            Route::delete('/groups/destroy',[GroupController::class,'destroy']);
            Route::put('/groups/update',[GroupController::class,'update']);
            Route::post('/invite/members',[GroupController::class,'sendJoinInvitation']);
        });
        Route::get('confirm/Invitation',[GroupController::class,'acceptInvitation'])->name('acceptGroupInvitation');
        Route::post('/groups/join/fromLink',[GroupController::class,'joinFromLink'])->name('');

        Route::apiResource('{groupId}/tasks', TaskController::class)->middleware('isFoundGroup');
        Route::get('/{groupId}/tasksBy/{Urgency}',[TaskController::class,'getTasksByUrgency']);
        Route::post('/tasks/updateStatus/{TaskId}',[TaskController::class,'updateTaskStatus']);
    });
    Route::get('confirm/Invitation/link/{groupId}/{adminId}',[GroupController::class,'joinView'])->name('joinViewLink');

Route::get('',function (){
    return "please login to access the API";
})->name('login');



