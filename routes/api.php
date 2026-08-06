<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\VehicleTrackingController;
use App\Http\Controllers\API\MapDataController;

Route::prefix('v1')->group(function () {

    // Public map data (for embedded maps)
    Route::get('/map/summary',    [MapDataController::class, 'summary']);
    Route::get('/map/containers', [MapDataController::class, 'containers']);
    Route::get('/map/vehicles',   [MapDataController::class, 'vehicles']);
    Route::get('/map/dumpsites',  [MapDataController::class, 'dumpsites']);
    Route::get('/map/heatmap',    [MapDataController::class, 'heatmap']);
    Route::get('/map/complaints', [MapDataController::class, 'complaints']);

    // Authenticated API
    Route::middleware('auth:sanctum')->group(function () {

        // Vehicle tracking
        Route::get('/vehicles/tracking',                          [VehicleTrackingController::class, 'index']);
        Route::post('/vehicles/{vehicle}/location',               [VehicleTrackingController::class, 'updateLocation']);
        Route::get('/vehicles/{vehicle}/history',                 [VehicleTrackingController::class, 'getHistory']);

        // Containers API
        Route::get('/containers',                                 [\App\Http\Controllers\ContainerController::class, 'geojson']);
        Route::patch('/containers/{container}/fill-level',        [\App\Http\Controllers\ContainerController::class, 'updateFillLevel']);
        Route::patch('/containers/{container}/emptied',           [\App\Http\Controllers\ContainerController::class, 'markEmptied']);

        // Route optimization
        Route::post('/routes/optimize',                           [\App\Http\Controllers\RouteController::class, 'optimize']);
    });
});
