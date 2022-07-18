<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => ['guest'],
] , function(){
    Route::post('/login' , [\App\Http\Controllers\Auth\GuestController::class , 'login']);
    Route::post('/password' , [\App\Http\Controllers\Auth\GuestController::class , 'forgetPassword']);
    Route::patch('/password' , [\App\Http\Controllers\Auth\GuestController::class , 'resetPassword']);
});

Route::group([
    'middleware' => ['auth:sanctum'],
] , function (){
    Route::post('/logout' , [\App\Http\Controllers\Auth\UserController::class , 'logout']);
    Route::patch('/me' , [\App\Http\Controllers\Auth\UserController::class , 'update']);
    Route::patch('/me/password' , [\App\Http\Controllers\Auth\UserController::class , 'updatePassword']);

    Route::group([
        'middleware' => ['roles:admin'],
        'prefix' => 'clerks',
    ], function (){
        Route::post('/' , [\App\Http\Controllers\Clerks\CreateController::class , 'store']);
        Route::get('/' , [\App\Http\Controllers\Clerks\ShowController::class , 'index']);
        Route::get('/{user}' , [\App\Http\Controllers\Clerks\ShowController::class , 'show']);
        Route::group([
            'middleware' => ['protectAdmin'],
        ], function (){
            Route::patch('/{user}' , [\App\Http\Controllers\Clerks\UpdateController::class , 'update']);
            Route::patch('/{user}/publish' , [\App\Http\Controllers\Clerks\UpdateController::class , 'publisher']);
            Route::patch('/{user}/ban' , [\App\Http\Controllers\Clerks\UpdateController::class , 'ban']);
            Route::patch('/{user}/hide' , [\App\Http\Controllers\Clerks\UpdateController::class , 'hide']);
        });
    });

    Route::group([
        'middleware' => ['roles:admin,moderator'],
    ] , function (){
        Route::group([
            'prefix' => 'tags'
        ] , function (){
            Route::get('/' , [\App\Http\Controllers\Tags\ShowController::class , 'index']);
            Route::get('/{tag}' , [\App\Http\Controllers\Tags\ShowController::class , 'show']);
            Route::post('/' , [\App\Http\Controllers\Tags\CreateController::class , 'store']);
            Route::patch('/{tag}' , [\App\Http\Controllers\Tags\UpdateController::class , 'update']);
            Route::patch('/{tag}/status' , [\App\Http\Controllers\Tags\UpdateController::class , 'updateStatus']);
            Route::delete('/{tag}' , [\App\Http\Controllers\Tags\DeleteController::class , 'destroy']);
        });

        Route::group([
            'prefix' => 'categories'
        ] , function (){
            Route::get('/');
            Route::get('/{category}');
            Route::post('/');
            Route::patch('/{category}');
            Route::patch('/{category}/status');
            Route::delete('/{category}');
        });
    });

});
