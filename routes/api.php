<?php

use App\Http\Controllers\Api\BlackListController;
use App\Http\Controllers\Api\DutyController;
use App\Http\Controllers\Api\GuildController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\CheckApiKeyMiddleware;
use App\Http\Middleware\HandleModelNotFoundMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([CheckApiKeyMiddleware::class, HandleModelNotFoundMiddleware::class])->group(function () {

    Route::post('guild/create', [GuildController::class, 'store']);

    Route::get('guilds/{guild}', [GuildController::class, 'get']);

    Route::patch('guilds/{guild}', [GuildController::class, 'install']);

    Route::get('guilds/', [GuildController::class, 'getGuildList'])->name('guilds.all');

    Route::prefix('guilds/{guild}')->group(function () {

        Route::prefix('/users')->group(function () {
            Route::post('/', [UserController::class, 'addUser']);
            Route::get('/{user}', [UserController::class, 'getUser']);
            Route::delete('/{user}', [UserController::class, 'removeUser']);
            Route::put('/{user}', [UserController::class, 'updateUserPivot']);

            Route::prefix('/{user}/duty')->group(function () {
                Route::get('/', [DutyController::class, 'getUserDuty']);
                Route::post('/on', [DutyController::class, 'onDuty']);
                Route::patch('/off', [DutyController::class, 'offDuty']);
                Route::delete('/cancel', [DutyController::class, 'cancelDuty']);
                Route::post('/', [DutyController::class, 'addDuty']);
            });

            Route::delete('/{user}/duties', [DutyController::class, 'deleteUserDuty']);

        });
        Route::get('/duties/checking', [DutyController::class, 'checkingDuty']);
        Route::post('/duties/checking', [DutyController::class, 'checkingDuty']);
        Route::delete('/duties', [DutyController::class, 'deleteAllDuty']);
        Route::delete('/duties/trash', [DutyController::class, 'clearDutyTrash']);
        Route::get('/duties', [DutyController::class, 'getAllDuty']);
        Route::get('/duties/active', [DutyController::class, 'getActiveDuty']);

        Route::prefix('users/{user}/blacklist')->group(function () {
            Route::get('/', [BlackListController::class, 'getBlackList']);
            Route::post('/', [BlackListController::class, 'addBlackList']);
            Route::delete('/', [BlackListController::class, 'removeBlackList']);
        });
        Route::get('/blacklist', [BlackListController::class, 'getAllBlackList']);
    });

});
