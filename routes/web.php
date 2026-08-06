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
            auth()->user()->update([
                'locale' => $lang
            ]);
        }
    }

    return redirect()->back();

})->name('locale.switch');
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');

    // Containers
    Route::get('containers/export/excel', [ContainerController::class, 'export'])->name('containers.export');
    Route::post('containers/import/excel', [ContainerController::class, 'import'])->name('containers.import');
    Route::get('containers/data/geojson', [ContainerController::class, 'geojson'])->name('containers.geojson');
    Route::get('containers/data/heatmap', [ContainerController::class, 'heatmap'])->name('containers.heatmap');
    Route::resource('containers', ContainerController::class);
    Route::patch('containers/{container}/fill-level', [ContainerController::class, 'updateFillLevel'])->name('containers.fill-level');
    Route::patch('containers/{container}/emptied', [ContainerController::class, 'markEmptied'])->name('containers.emptied');

    // Vehicles
    Route::get('vehicles/export/excel', [VehicleController::class, 'export'])->name('vehicles.export');
    Route::post('vehicles/import/excel', [VehicleController::class, 'import'])->name('vehicles.import');
    Route::resource('vehicles', VehicleController::class);
    Route::patch('vehicles/{vehicle}/location', [VehicleController::class, 'updateLocation'])->name('vehicles.location');

    // Drivers
    Route::get('drivers/export/excel', [DriverController::class, 'export'])->name('drivers.export');
    Route::post('drivers/import/excel', [DriverController::class, 'import'])->name('drivers.import');
    Route::resource('drivers', DriverController::class);

    // Routes
    Route::get('routes/export/excel', [RouteController::class, 'export'])->name('routes.export');
    Route::post('routes/optimize', [RouteController::class, 'optimize'])->name('routes.optimize');
    Route::resource('routes', RouteController::class);
    Route::get('routes/{route}/geojson', [RouteController::class, 'getGeojson'])->name('routes.geojson');
    Route::patch('routes/{route}/activate', [RouteController::class, 'activate'])->name('routes.activate');
    Route::patch('routes/{route}/complete', [RouteController::class, 'complete'])->name('routes.complete');

    // Dumpsites
    Route::resource('dumpsites', DumpsiteController::class);

    // Complaints
    Route::get('complaints/export/excel', [ComplaintController::class, 'export'])->name('complaints.export');
    Route::resource('complaints', ComplaintController::class);

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

    // GIS / Map
    Route::get('map', [GISController::class, 'map'])->name('map');
    Route::get('map/layers/all', [GISController::class, 'getAllLayers'])->name('map.layers.all');
    Route::get('map/layers/containers', [GISController::class, 'getContainerLayer'])->name('map.layers.containers');
    Route::get('map/layers/vehicles', [GISController::class, 'getVehicleLayer'])->name('map.layers.vehicles');
    Route::post('map/analysis', [GISController::class, 'spatialAnalysis'])->name('map.analysis');
    Route::post('map/geojson/upload', [GISController::class, 'uploadGeojson'])->name('map.geojson.upload');
    Route::get('map/geojson/export', [GISController::class, 'exportGeojson'])->name('map.geojson.export');

    // Users (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});

// Public complaint submission
Route::get('/complaint/submit', [ComplaintController::class, 'create'])->name('complaints.public');
Route::post('/complaint/submit', [ComplaintController::class, 'store'])->name('complaints.public.store');