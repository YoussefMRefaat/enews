<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth:sanctum'],
    'prefix'    => '/dashboard',
], function (){
    Route::group([
        'middleware' => ['roles:'.\App\Enums\Roles::Admin->name],
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
        'middleware' => ['roles:' . \App\Enums\Roles::Admin->name . ',' . \App\Enums\Roles::Moderator->name],
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
            Route::get('/' , [\App\Http\Controllers\Categories\ShowController::class , 'index']);
            Route::get('/{category}' , [\App\Http\Controllers\Categories\ShowController::class , 'show']);
            Route::post('/' , [\App\Http\Controllers\Categories\CreateController::class , 'store']);
            Route::patch('/{category}' , [\App\Http\Controllers\Categories\UpdateController::class , 'update']);
            Route::patch('/{category}/status' , [\App\Http\Controllers\Categories\UpdateController::class , 'updateStatus']);
            Route::delete('/{category}' , [\App\Http\Controllers\Categories\DeleteController::class , 'destroy']);
        });

        Route::get('/topics' , [\App\Http\Controllers\Topics\ShowController::class , 'index']);
    });

    Route::group([
        'prefix' => '/topics',
    ], function (){
        Route::post('/news' , [\App\Http\Controllers\Topics\CreateController::class , 'storeNews'])
            ->middleware('roles:' . \App\Enums\Roles::Journalist->name);
        Route::post('/articles' , [\App\Http\Controllers\Topics\CreateController::class , 'storeArticle'])
            ->middleware('roles:' . \App\Enums\Roles::Writer->name);
        Route::group([
            'middleware' => ['canManage']
        ],function (){
            Route::get('/{topic}' , [\App\Http\Controllers\Topics\ShowController::class , 'show']);
            Route::patch('/{topic}' , [\App\Http\Controllers\Topics\UpdateController::class , 'update']);
            Route::patch('/{topic}/publish' , [\App\Http\Controllers\Topics\UpdateController::class , 'publish']);
            Route::delete('/{topic}' , [\App\Http\Controllers\Topics\DeleteController::class , 'destroy']);
        });

    });

});
