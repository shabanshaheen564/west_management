<?php

namespace App\Http\Controllers;

use App\Models\Route as WasteRoute;
use App\Models\Container;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Dumpsite;
use App\Exports\RoutesExport;
use App\Services\GIS\RouteOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RouteController extends Controller
{
    public function __construct(private RouteOptimizationService $optimizationService) {}

    public function index(Request $request)
    {
        $query = WasteRoute::with(['vehicle', 'driver', 'dumpsite', 'containers']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('name', 'like', "%$q%")
                                         ->orWhere('code', 'like', "%$q%"));
        }
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('date'))     $query->whereDate('scheduled_at', $request->date);
        if ($request->filled('vehicle'))  $query->where('vehicle_id', $request->vehicle);
        if ($request->filled('driver'))   $query->where('driver_id', $request->driver);

        $routes     = $query->orderByDesc('scheduled_at')->paginate(15)->withQueryString();
        $vehicles   = Vehicle::available()->get();
        $drivers    = Driver::available()->get();
        $dumpsites  = Dumpsite::active()->get();
        $containers = Container::active()->get();

        $stats = [
            'total'           => WasteRoute::count(),
            'active'          => WasteRoute::where('status', 'active')->count(),
            'today'           => WasteRoute::whereDate('scheduled_at', today())->count(),
            'completed_today' => WasteRoute::whereDate('completed_at', today())->where('status', 'completed')->count(),
        ];

        return view('waste_management.routes', compact(
            'routes', 'vehicles', 'drivers', 'dumpsites', 'containers', 'stats'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'name_ar'      => 'nullable|string',
            'vehicle_id'   => 'nullable|exists:vehicles,id',
            'driver_id'    => 'nullable|exists:drivers,id',
            'dumpsite_id'  => 'nullable|exists:dumpsites,id',
            'frequency'    => 'required|in:daily,alternate,weekly,monthly,on_demand',
            'scheduled_at' => 'required|date',
            'start_lat'    => 'nullable|numeric',
            'start_lng'    => 'nullable|numeric',
            'containers'   => 'nullable|array',
            'containers.*' => 'exists:containers,id',
            'notes'        => 'nullable|string',
        ]);

        $this->validateAssignments($validated['vehicle_id'] ?? null, $validated['driver_id'] ?? null);

        $validated['code'] = 'RTE-' . strtoupper(uniqid());

        $route = DB::transaction(function () use ($request, $validated) {
            $route = WasteRoute::create($validated);
            $this->syncContainers($route, $request->input('containers', []));
            return $route;
        });

        return redirect()->route('routes.index')
            ->with('success', __('Route created successfully'));
    }

    public function update(Request $request, WasteRoute $route)
    {
        if (in_array($route->status, ['completed', 'cancelled'], true)) {
            return redirect()->route('routes.index')->with('error', __('Completed or cancelled routes cannot be edited.'));
        }

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'name_ar'      => 'nullable|string',
            'vehicle_id'   => 'nullable|exists:vehicles,id',
            'driver_id'    => 'nullable|exists:drivers,id',
            'dumpsite_id'  => 'nullable|exists:dumpsites,id',
            'frequency'    => 'required|in:daily,alternate,weekly,monthly,on_demand',
            'scheduled_at' => 'required|date',
            'status'       => 'required|in:planned,active,completed,cancelled',
            'notes'        => 'nullable|string',
        ]);

        if ($validated['status'] === 'active' && $route->status !== 'active') {
            $this->validateAssignments($validated['vehicle_id'] ?? null, $validated['driver_id'] ?? null, $route->id);
        } elseif ($route->status !== 'active') {
            $this->validateAssignments($validated['vehicle_id'] ?? null, $validated['driver_id'] ?? null, $route->id);
        }

        DB::transaction(function () use ($request, $validated, $route) {
            $route->update($validated);
            if ($request->has('containers')) {
                $this->syncContainers($route, $request->input('containers', []));
            }
        });

        return redirect()->route('routes.index')
            ->with('success', __('Route updated successfully'));
    }

    public function destroy(WasteRoute $route)
    {
        if ($route->status === 'active') {
            return redirect()->route('routes.index')->with('error', __('Active routes cannot be deleted. Complete or cancel the route first.'));
        }

        $route->delete();
        return redirect()->route('routes.index')
            ->with('success', __('Route deleted successfully'));
    }

    public function optimize(Request $request)
    {
        $request->validate([
            'container_ids' => 'required|array',
            'start_lat'     => 'required|numeric',
            'start_lng'     => 'required|numeric',
            'end_lat'       => 'nullable|numeric',
            'end_lng'       => 'nullable|numeric',
        ]);

        $containers = Container::whereIn('id', $request->container_ids)->get();

        $containerData = $containers->map(fn($c) => [
            'id'        => $c->id,
            'name'      => $c->name,
            'latitude'  => $c->latitude,
            'longitude' => $c->longitude,
        ])->toArray();

        $startPoint = ['lat' => $request->start_lat, 'lng' => $request->start_lng];
        $endPoint   = ['lat' => $request->end_lat ?? $request->start_lat,
                       'lng' => $request->end_lng ?? $request->start_lng];

        $result = $this->optimizationService->optimizeCollectionOrder(
            $containerData, $startPoint, $endPoint
        );

        if (!empty($result['ordered_containers'])) {
            $coords = array_merge(
                [[$startPoint['lng'], $startPoint['lat']]],
                array_map(fn($c) => [$c['longitude'] ?? $c['lng'], $c['latitude'] ?? $c['lat']], $result['ordered_containers']),
                [[$endPoint['lng'], $endPoint['lat']]]
            );

            $routeDetails = $this->optimizationService->optimizeRoute($coords);
            $result['route_geojson'] = $routeDetails['geojson'] ?? null;
            $result['total_distance'] = $routeDetails['distance'] ?? $result['total_distance'];
            $result['total_duration'] = $routeDetails['duration'] ?? $result['total_duration'];
        }

        return response()->json($result);
    }

    public function getGeojson(WasteRoute $route)
    {
        if ($route->geojson_path) {
            return response()->json($route->geojson_path);
        }

        $containers = $route->containers()->get();
        if ($containers->isEmpty()) {
            return response()->json(['error' => 'No containers'], 422);
        }

        $coords = $containers->map(fn($c) => [$c->longitude, $c->latitude])->toArray();

        if ($route->start_lat && $route->start_lng) {
            array_unshift($coords, [$route->start_lng, $route->start_lat]);
        }

        return response()->json([
            'type' => 'Feature',
            'geometry' => ['type' => 'LineString', 'coordinates' => $coords],
            'properties' => ['route_id' => $route->id, 'name' => $route->name],
        ]);
    }

    public function export()
    {
        return Excel::download(new RoutesExport, 'routes-' . date('Y-m-d') . '.xlsx');
    }

    public function activate(WasteRoute $route)
    {
        if (!in_array($route->status, ['planned'], true)) {
            return response()->json(['success' => false, 'message' => __('Only planned routes can be activated.')], 422);
        }

        try {
            DB::transaction(function () use ($route) {
                $this->validateAssignments($route->vehicle_id, $route->driver_id, $route->id);

                $route->update(['status' => 'active', 'started_at' => now()]);

                if ($route->vehicle_id) {
                    $route->vehicle->update(['status' => 'on_route']);
                }
            });

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function complete(WasteRoute $route)
    {
        if ($route->status !== 'active') {
            return response()->json(['success' => false, 'message' => __('Only active routes can be completed.')], 422);
        }

        DB::transaction(function () use ($route) {
            $now = now();
            $route->load('vehicle', 'driver', 'containers');

            foreach ($route->containers as $container) {
                $container->update([
                    'fill_level'      => 0,
                    'last_emptied_at' => $now,
                    'last_checked_at' => $now,
                ]);

                if (!in_array($container->status, ['maintenance', 'inactive'], true)) {
                    $container->update(['status' => 'active']);
                }

                $route->containers()->updateExistingPivot($container->id, [
                    'status'       => 'collected',
                    'collected_at' => $now,
                ]);
            }

            $route->update([
                'status'       => 'completed',
                'completed_at' => $now,
            ]);

            if ($route->vehicle) {
                $route->vehicle->update(['status' => 'active']);
            }

            if ($route->driver) {
                $route->driver->increment('total_trips');
            }
        });

        return response()->json(['success' => true]);
    }

    private function validateAssignments(?int $vehicleId, ?int $driverId, ?int $ignoreRouteId = null): void
    {
        if ($vehicleId) {
            $vehicle = Vehicle::findOrFail($vehicleId);

            if ($vehicle->status !== 'active') {
                abort(422, __('Selected vehicle is not active.'));
            }

            $query = WasteRoute::where('vehicle_id', $vehicleId)
                ->whereIn('status', ['planned', 'active']);
            if ($ignoreRouteId) $query->whereKeyNot($ignoreRouteId);

            if ($query->exists()) {
                abort(422, __('Selected vehicle is already assigned to another planned or active route.'));
            }
        }

        if ($driverId) {
            $driver = Driver::findOrFail($driverId);

            if ($driver->status !== 'active') {
                abort(422, __('Selected driver is not active.'));
            }

            $query = WasteRoute::where('driver_id', $driverId)
                ->whereIn('status', ['planned', 'active']);
            if ($ignoreRouteId) $query->whereKeyNot($ignoreRouteId);

            if ($query->exists()) {
                abort(422, __('Selected driver is already assigned to another planned or active route.'));
            }
        }
    }

    private function syncContainers(WasteRoute $route, array $containerIds): void
    {
        $containerIds = array_values(array_unique(array_map('intval', $containerIds)));
        $containerData = [];

        foreach ($containerIds as $index => $containerId) {
            $containerData[$containerId] = [
                'order' => $index + 1,
                'status' => 'pending',
                'collected_at' => null,
            ];
        }

        $route->containers()->sync($containerData);
    }
}
