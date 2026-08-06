<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\Vehicle;
use App\Models\Dumpsite;
use App\Models\Complaint;
use App\Models\Route;
use App\Helpers\GISHelper;
use App\Services\GIS\RouteOptimizationService;
use Illuminate\Http\Request;

class GISController extends Controller
{
    public function __construct(private RouteOptimizationService $orsService) {}

    public function map()
    {
        $containers = Container::count();
        $vehicles   = Vehicle::count();
        $dumpsites  = Dumpsite::count();

        return view('waste_management.map', compact('containers', 'vehicles', 'dumpsites'));
    }

    public function getAllLayers()
    {
        $containers = Container::all();
        $vehicles   = Vehicle::whereNotNull('current_lat')->whereNotNull('current_lng')->get();
        $dumpsites  = Dumpsite::all();
        $complaints = Complaint::whereNotNull('latitude')->whereNotNull('longitude')
                               ->where('status', '!=', 'closed')->get();
        $activeRoutes = Route::where('status', 'active')->with('containers')->get();

        return response()->json([
            'containers' => GISHelper::toGeoJsonFeatureCollection($containers),
            'vehicles'   => GISHelper::toGeoJsonFeatureCollection($vehicles),
            'dumpsites'  => GISHelper::toGeoJsonFeatureCollection($dumpsites),
            'complaints' => [
                'type' => 'FeatureCollection',
                'features' => $complaints->map(fn($c) => [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [$c->longitude, $c->latitude]],
                    'properties' => [
                        'id'       => $c->id,
                        'ticket'   => $c->ticket_number,
                        'subject'  => $c->subject,
                        'priority' => $c->priority,
                        'status'   => $c->status,
                        'category' => $c->category,
                    ],
                ])->values()->toArray(),
            ],
            'heatmap' => GISHelper::generateHeatmapData($containers),
        ]);
    }

    public function getContainerLayer(Request $request)
    {
        $query = Container::query();
        if ($request->filled('type'))    $query->where('type', $request->type);
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('zone'))    $query->where('zone', $request->zone);
        if ($request->filled('min_fill'))$query->where('fill_level', '>=', $request->min_fill);

        return response()->json(GISHelper::toGeoJsonFeatureCollection($query->get()));
    }

    public function getVehicleLayer()
    {
        $vehicles = Vehicle::whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->with('driver')
            ->get();

        $features = $vehicles->map(function ($v) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [$v->current_lng, $v->current_lat]],
                'properties' => [
                    'id'           => $v->id,
                    'plate_number' => $v->plate_number,
                    'type'         => $v->type,
                    'status'       => $v->status,
                    'fuel_level'   => $v->fuel_level,
                    'driver'       => $v->driver?->name,
                    'gps_data'     => $v->gps_data,
                ],
            ];
        });

        return response()->json(['type' => 'FeatureCollection', 'features' => $features->values()->toArray()]);
    }

    public function spatialAnalysis(Request $request)
    {
        $request->validate([
            'type'      => 'required|in:radius_search,isochrone,nearest_dumpsite,coverage_analysis',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        return match($request->type) {
            'radius_search'     => $this->radiusSearch($request),
            'isochrone'         => $this->isochrone($request),
            'nearest_dumpsite'  => $this->nearestDumpsite($request),
            'coverage_analysis' => $this->coverageAnalysis($request),
        };
    }

    private function radiusSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        $radius = $request->get('radius', 0.5); // km
        $lat = $request->latitude;
        $lng = $request->longitude;

        $containers = Container::all()->filter(function ($c) use ($lat, $lng, $radius) {
            return GISHelper::distance($lat, $lng, $c->latitude, $c->longitude) <= $radius;
        })->values();

        $dumpsites = Dumpsite::all()->filter(function ($d) use ($lat, $lng, $radius) {
            return GISHelper::distance($lat, $lng, $d->latitude, $d->longitude) <= $radius;
        })->values();

        return response()->json([
            'containers' => GISHelper::toGeoJsonFeatureCollection($containers),
            'dumpsites'  => GISHelper::toGeoJsonFeatureCollection($dumpsites),
            'stats'      => [
                'containers_found' => $containers->count(),
                'dumpsites_found'  => $dumpsites->count(),
                'radius_km'        => $radius,
            ],
        ]);
    }

    private function isochrone(Request $request): \Illuminate\Http\JsonResponse
    {
        $ranges = $request->get('ranges', [300, 600, 900]); // seconds
        $result = $this->orsService->getIsochrone(
            $request->latitude, $request->longitude, $ranges
        );
        return response()->json($result);
    }

    private function nearestDumpsite(Request $request): \Illuminate\Http\JsonResponse
    {
        $lat = $request->latitude;
        $lng = $request->longitude;

        $nearest = Dumpsite::active()->get()->sortBy(function ($d) use ($lat, $lng) {
            return GISHelper::distance($lat, $lng, $d->latitude, $d->longitude);
        })->first();

        if (!$nearest) {
            return response()->json(['error' => 'No active dumpsites found'], 404);
        }

        $distance = GISHelper::distance($lat, $lng, $nearest->latitude, $nearest->longitude);

        return response()->json([
            'dumpsite' => $nearest->toGeoJson(),
            'distance_km' => round($distance, 2),
        ]);
    }

    private function coverageAnalysis(Request $request): \Illuminate\Http\JsonResponse
    {
        $radius = $request->get('radius', 0.3); // km per container
        $containers = Container::active()->get();
        $totalArea = count($containers) * M_PI * $radius * $radius;

        // Count unique zones
        $zones = $containers->pluck('zone')->filter()->unique();

        return response()->json([
            'total_containers'   => $containers->count(),
            'total_coverage_km2' => round($totalArea, 2),
            'zones_covered'      => $zones->count(),
            'containers_needing_service' => $containers->where('fill_level', '>=', 80)->count(),
            'coverage_geojson'   => GISHelper::toGeoJsonFeatureCollection($containers),
        ]);
    }

    public function uploadGeojson(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:json,geojson']);

        $content = file_get_contents($request->file('file')->getRealPath());
        $geojson = json_decode($content, true);

        if (!$geojson || !isset($geojson['type'])) {
            return response()->json(['error' => 'Invalid GeoJSON file'], 422);
        }

        return response()->json([
            'success' => true,
            'geojson' => $geojson,
            'feature_count' => count($geojson['features'] ?? []),
        ]);
    }

    public function exportGeojson(Request $request)
    {
        $layer = $request->get('layer', 'containers');

        $geojson = match($layer) {
            'containers' => GISHelper::toGeoJsonFeatureCollection(Container::all()),
            'vehicles'   => GISHelper::toGeoJsonFeatureCollection(
                Vehicle::whereNotNull('current_lat')->get()
            ),
            'dumpsites'  => GISHelper::toGeoJsonFeatureCollection(Dumpsite::all()),
            default      => ['type' => 'FeatureCollection', 'features' => []],
        };

        return response()->json($geojson)
            ->header('Content-Disposition', "attachment; filename={$layer}-" . date('Y-m-d') . '.geojson');
    }
}
