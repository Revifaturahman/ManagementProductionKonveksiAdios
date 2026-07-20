<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProcessDelivery1Controller;
use App\Http\Controllers\Api\ProcessDelivery2Controller;
use App\Http\Controllers\Api\RawMaterialProcessController;
use App\Http\Controllers\Api\WorkerTask2Controller;
use App\Http\Controllers\Api\WorkerTaskController;
use Illuminate\Support\Facades\Route;

// 
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::patch(
        '/auth/availability',
        [AuthController::class, 'updateAvailability']
    );


    // PRODUKSI TAHAP 1 KURIR
    Route::get(
        '/courier/tasks',
        [ProcessDelivery1Controller::class, 'getCourierTasks']
    );

    Route::post(
        '/courier/start-delivery',
        [ProcessDelivery1Controller::class, 'startDelivery']
    );

    Route::post(
        '/courier/arrive',
        [ProcessDelivery1Controller::class, 'arrive']
    );

    Route::post(
        'courier/update-result',
        [ProcessDelivery1Controller::class, 'updateResult']
    );

    Route::post(
        'courier/next-process',
        [ProcessDelivery1Controller::class, 'nextProcess']
    );

    Route::get(
        'courier/arrived-tasks',
        [ProcessDelivery1Controller::class, 'getArrivedTasks']
    );


    // PRODUKSI TAHAP 2 KURIR
    Route::get(
        '/courier/tasks2',
        [ProcessDelivery2Controller::class, 'getCourierTasks']
    );

    Route::post(
        '/courier/start-delivery2',
        [ProcessDelivery2Controller::class, 'startDelivery']
    );

    Route::post(
        '/courier/arrive2',
        [ProcessDelivery2Controller::class, 'arrive']
    );

    Route::post(
        'courier/update-result2',
        [ProcessDelivery2Controller::class, 'updateResult']
    );

    Route::post(
        'courier/next-process2',
        [ProcessDelivery2Controller::class, 'nextProcess']
    );

    Route::get(
        'courier/arrived-tasks2',
        [ProcessDelivery2Controller::class, 'getArrivedTasks']
    );

    Route::post(
        '/auth/update-password',
        [AuthController::class, 'updatePassword']
    );

// MAKLUN PRODUKSI 1
    Route::get(
        '/worker/tasks',
        [WorkerTaskController::class, 'getTasks']
    );

    Route::get(
        '/worker/task-detail/{rawMaterialId}/{stage}',
        [WorkerTaskController::class, 'getTaskDetail']
    );

    Route::post(
        '/worker/update-progress',
        [WorkerTaskController::class, 'updateProgress']
    );

    // MAKLUN PRODUKSI 2
    Route::get(
        '/worker/tasks2',
        [WorkerTask2Controller::class, 'getTasks']
    );

    Route::get(
        '/worker/task-detail2/{productionBatchId}/{stage}',
        [WorkerTask2Controller::class, 'getTaskDetail']
    );

    Route::post(
        '/worker/update-progress2',
        [WorkerTask2Controller::class, 'updateProgress']
    );
});
