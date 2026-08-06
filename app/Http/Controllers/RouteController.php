<?php

namespace App\Http\Controllers;

use App\Models\Route as WasteRoute;  // ← غيّر هون
use App\Models\Container;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Dumpsite;
use App\Exports\RoutesExport;
use App\Services\GIS\RouteOptimizationService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RouteController extends Controller
{
    public function __construct(private RouteOptimizationService $optimizationService) {}

    public function index(Request $request)
    {
        $query = WasteRoute::with(['vehicle', 'driver', 'dumpsite', 'containers']);  // ← غيّر

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('name', 'like', "%$q%")
                                         ->orWhere('code', 'like', "%$q%"));
        }
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('date'))     $query->whereDate('scheduled_at', $request->date);
        if ($request->filled('vehicle'))  $query->where('vehicle_id', $request->vehicle);
        if ($request->filled('driver'))   $query->where('driver_id', $request->driver);

        $routes    = $query->orderByDesc('scheduled_at')->paginate(15)->withQueryString();
        $vehicles  = Vehicle::available()->get();
        $drivers   = Driver::available()->get();
        $dumpsites = Dumpsite::active()->get();
        $containers= Container::active()->get();

        $stats = [
            'total'          => WasteRoute::count(),  // ← غيّر
            'active'         => WasteRoute::where('status', 'active')->count(),  // ← غيّر
            'today'          => WasteRoute::whereDate('scheduled_at', today())->count(),  // ← غيّر
            'completed_today'=> WasteRoute::whereDate('completed_at', today())->where('status', 'completed')->count(),  // ← غيّر
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

        $validated['code'] = 'RTE-' . strtoupper(uniqid());

        $route = WasteRoute::create($validated);  // ← غيّر

        if ($request->filled('containers')) {
            $containerData = [];
            foreach ($request->containers as $index => $containerId) {
                $containerData[$containerId] = ['order' => $index + 1, 'status' => 'pending'];
            }
            $route->containers()->sync($containerData);
        }

        return redirect()->route('routes.index')
            ->with('success', __('Route created successfully'));
    }

    public function update(Request $request, WasteRoute $route)  // ← غيّر
    {
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

        $route->update($validated);

        if ($request->has('containers')) {
            $containerData = [];
            foreach ($request->containers ?? [] as $index => $containerId) {
                $containerData[$containerId] = ['order' => $index + 1, 'status' => 'pending'];
            }
            $route->containers()->sync($containerData);
        }

        return redirect()->route('routes.index')
            ->with('success', __('Route updated successfully'));
    }

    public function destroy(WasteRoute $route)  // ← غيّر
    {
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

    public function getGeojson(WasteRoute $route)  // ← غيّر
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

    public function activate(WasteRoute $route)  // ← غيّر
    {
        $route->update(['status' => 'active', 'started_at' => now()]);
        if ($route->vehicle_id) {
            $route->vehicle->update(['status' => 'on_route']);
        }
        return response()->json(['success' => true]);
    }

    public function complete(WasteRoute $route)  // ← غيّر
    {
        $route->update(['status' => 'completed', 'completed_at' => now()]);
        if ($route->vehicle_id) {
            $route->vehicle->update(['status' => 'active']);
        }
        return response()->json(['success' => true]);
    }
}