<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\DumpsiteController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\GISController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/locale/{lang}', function ($lang) {
    if (in_array($lang, ['en', 'ar'])) {
        session()->put('locale', $lang);
        app()->setLocale($lang);
        if (auth()->check()) {
            auth()->user()->update(['locale' => $lang]);
        }
    }
    return redirect()->back();
})->name('locale.switch');

Route::middleware(['auth'])->group(function () {

    // Dashboard — visible to any logged-in user, no specific permission required
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');

    // Notifications
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');

    // Profile — minimal read-only page for the logged-in user
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');

    // ===== Containers =====
    Route::middleware('permission:view containers')->group(function () {
        Route::get('containers', [ContainerController::class, 'index'])->name('containers.index');
        Route::get('containers/{container}', [ContainerController::class, 'show'])->name('containers.show');
        Route::get('containers/export/excel', [ContainerController::class, 'export'])->name('containers.export');
        Route::get('containers/data/geojson', [ContainerController::class, 'geojson'])->name('containers.geojson');
        Route::get('containers/data/heatmap', [ContainerController::class, 'heatmap'])->name('containers.heatmap');
    });
    Route::post('containers', [ContainerController::class, 'store'])->name('containers.store')
        ->middleware('permission:create containers');
    Route::post('containers/import/excel', [ContainerController::class, 'import'])->name('containers.import')
        ->middleware('permission:create containers');
    Route::middleware('permission:edit containers')->group(function () {
        Route::put('containers/{container}', [ContainerController::class, 'update'])->name('containers.update');
        Route::patch('containers/{container}', [ContainerController::class, 'update']);
        Route::patch('containers/{container}/fill-level', [ContainerController::class, 'updateFillLevel'])->name('containers.fill-level');
        Route::patch('containers/{container}/emptied', [ContainerController::class, 'markEmptied'])->name('containers.emptied');
    });
    Route::delete('containers/{container}', [ContainerController::class, 'destroy'])->name('containers.destroy')
        ->middleware('permission:delete containers');

    // ===== Vehicles =====
    Route::middleware('permission:view vehicles')->group(function () {
        Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
        Route::get('vehicles/export/excel', [VehicleController::class, 'export'])->name('vehicles.export');
    });
    Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store')
        ->middleware('permission:create vehicles');
    Route::post('vehicles/import/excel', [VehicleController::class, 'import'])->name('vehicles.import')
        ->middleware('permission:create vehicles');
    Route::middleware('permission:edit vehicles')->group(function () {
        Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::patch('vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::patch('vehicles/{vehicle}/location', [VehicleController::class, 'updateLocation'])->name('vehicles.location');
    });
    Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy')
        ->middleware('permission:delete vehicles');

    // ===== Drivers =====
    Route::middleware('permission:view drivers')->group(function () {
        Route::get('drivers', [DriverController::class, 'index'])->name('drivers.index');
        Route::get('drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');
        Route::get('drivers/export/excel', [DriverController::class, 'export'])->name('drivers.export');
    });
    Route::post('drivers', [DriverController::class, 'store'])->name('drivers.store')
        ->middleware('permission:create drivers');
    Route::post('drivers/import/excel', [DriverController::class, 'import'])->name('drivers.import')
        ->middleware('permission:create drivers');
    Route::middleware('permission:edit drivers')->group(function () {
        Route::put('drivers/{driver}', [DriverController::class, 'update'])->name('drivers.update');
        Route::patch('drivers/{driver}', [DriverController::class, 'update']);
    });
    Route::delete('drivers/{driver}', [DriverController::class, 'destroy'])->name('drivers.destroy')
        ->middleware('permission:delete drivers');

    // ===== Routes (collection routes) =====
    Route::middleware('permission:view routes')->group(function () {
        Route::get('routes', [RouteController::class, 'index'])->name('routes.index');
        Route::get('routes/export/excel', [RouteController::class, 'export'])->name('routes.export');
        Route::get('routes/{route}/geojson', [RouteController::class, 'getGeojson'])->name('routes.geojson');
    });
    Route::post('routes', [RouteController::class, 'store'])->name('routes.store')
        ->middleware('permission:create routes');
    Route::middleware('permission:edit routes')->group(function () {
        Route::put('routes/{route}', [RouteController::class, 'update'])->name('routes.update');
        Route::patch('routes/{route}', [RouteController::class, 'update']);
        Route::patch('routes/{route}/activate', [RouteController::class, 'activate'])->name('routes.activate');
        Route::patch('routes/{route}/complete', [RouteController::class, 'complete'])->name('routes.complete');
    });
    Route::delete('routes/{route}', [RouteController::class, 'destroy'])->name('routes.destroy')
        ->middleware('permission:delete routes');
    Route::post('routes/optimize', [RouteController::class, 'optimize'])->name('routes.optimize')
        ->middleware('permission:optimize routes');

    // ===== Dumpsites =====
    Route::get('dumpsites', [DumpsiteController::class, 'index'])->name('dumpsites.index')
        ->middleware('permission:view dumpsites');
    Route::post('dumpsites', [DumpsiteController::class, 'store'])->name('dumpsites.store')
        ->middleware('permission:create dumpsites');
    Route::middleware('permission:edit dumpsites')->group(function () {
        Route::put('dumpsites/{dumpsite}', [DumpsiteController::class, 'update'])->name('dumpsites.update');
        Route::patch('dumpsites/{dumpsite}', [DumpsiteController::class, 'update']);
    });
    Route::delete('dumpsites/{dumpsite}', [DumpsiteController::class, 'destroy'])->name('dumpsites.destroy')
        ->middleware('permission:delete dumpsites');

    // ===== Complaints =====
    Route::middleware('permission:view complaints')->group(function () {
        Route::get('complaints', [ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('complaints/export/excel', [ComplaintController::class, 'export'])->name('complaints.export');
    });
    Route::post('complaints', [ComplaintController::class, 'store'])->name('complaints.store')
        ->middleware('permission:create complaints');
    Route::middleware('permission:edit complaints')->group(function () {
        Route::put('complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
        Route::patch('complaints/{complaint}', [ComplaintController::class, 'update']);
    });
    Route::delete('complaints/{complaint}', [ComplaintController::class, 'destroy'])->name('complaints.destroy')
        ->middleware('permission:delete complaints');

    // ===== Reports =====
    Route::middleware('permission:view reports')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });
    Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate')
        ->middleware('permission:generate reports');

    // ===== GIS / Map =====
    Route::middleware('permission:view map')->group(function () {
        Route::get('map', [GISController::class, 'map'])->name('map');
        Route::get('map/layers/all', [GISController::class, 'getAllLayers'])->name('map.layers.all');
        Route::get('map/layers/containers', [GISController::class, 'getContainerLayer'])->name('map.layers.containers');
        Route::get('map/layers/vehicles', [GISController::class, 'getVehicleLayer'])->name('map.layers.vehicles');
        Route::get('map/geojson/export', [GISController::class, 'exportGeojson'])->name('map.geojson.export');
    });
    Route::middleware('permission:spatial analysis')->group(function () {
        Route::post('map/analysis', [GISController::class, 'spatialAnalysis'])->name('map.analysis');
        Route::post('map/geojson/upload', [GISController::class, 'uploadGeojson'])->name('map.geojson.upload');
    });

    // ===== Users / Roles / Settings (admin area) =====
    Route::middleware('role:admin')->group(function () {
        // UserController/RoleController only implement index/store/update/destroy
        // (create/show/edit are handled via modals in the index view), so the
        // resource routes are restricted to match — otherwise /users/create,
        // /users/{user}, and /users/{user}/edit would 500 with "method not found".
        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('roles', RoleController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});

// Public complaint submission — no auth required, so anyone can report an issue
Route::get('/complaint/submit', [ComplaintController::class, 'create'])->name('complaints.public');
Route::post('/complaint/submit', [ComplaintController::class, 'store'])->name('complaints.public.store');
