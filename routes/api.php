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

Route::get('/topics' , [\App\Http\Controllers\Topics\ShowController::class , 'publicIndex']);
Route::get('/clerks' , [\App\Http\Controllers\Clerks\ShowController::class , 'publicIndex']);
Route::get('/categories' , [\App\Http\Controllers\Categories\ShowController::class , 'publicIndex']);
Route::get('/tags' , [\App\Http\Controllers\Tags\ShowController::class , 'publicIndex']);

//Route::get('/{topic}');
//Route::get('/{clerk}');
//Route::get('/{category}');
//Route::get('/{tag}');
//
//Route::post('subscribe');

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
});

require 'dashboard.php';
