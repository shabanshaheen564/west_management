<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\Vehicle;
use App\Models\Dumpsite;
use App\Models\Complaint;
use App\Models\Route;
use App\Helpers\GISHelper;
use Illuminate\Http\Request;

class GISController extends Controller
{
    public function map()
    {
        return view('waste_management.map', [
            'containers' => Container::count(),
            'vehicles'   => Vehicle::whereNotNull('current_lat')->whereNotNull('current_lng')->count(),
            'dumpsites'  => Dumpsite::count(),
        ]);
    }

    public function getAllLayers()
    {
        $containers = Container::all();
        $vehicles = Vehicle::whereNotNull('current_lat')->whereNotNull('current_lng')->with('driver')->get();
        $dumpsites = Dumpsite::all();
        $complaints = Complaint::whereNotNull('latitude')->whereNotNull('longitude')
            ->where('status', '!=', 'closed')->get();

        return response()->json([
            'containers' => GISHelper::toGeoJsonFeatureCollection($containers),
            'vehicles' => $vehicles->map(fn($v) => [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [$v->current_lng, $v->current_lat]],
                'properties' => [
                    'id' => $v->id,
                    'plate_number' => $v->plate_number,
                    'type' => $v->type,
                    'status' => $v->status,
                    'fuel_level' => $v->fuel_level,
                    'driver' => $v->driver?->name,
                ],
            ])->values()->toArray(),
            'dumpsites' => GISHelper::toGeoJsonFeatureCollection($dumpsites),
            'complaints' => [
                'type' => 'FeatureCollection',
                'features' => $complaints->map(fn($c) => [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [$c->longitude, $c->latitude]],
                    'properties' => [
                        'id' => $c->id,
                        'ticket' => $c->ticket_number,
                        'subject' => $c->subject,
                        'priority' => $c->priority,
                        'status' => $c->status,
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
        foreach (['type', 'status', 'zone'] as $field) {
            if ($request->filled($field)) $query->where($field, $request->input($field));
        }
        if ($request->filled('min_fill')) $query->where('fill_level', '>=', $request->min_fill);
        return response()->json(GISHelper::toGeoJsonFeatureCollection($query->get()));
    }

    public function getVehicleLayer()
    {
        $vehicles = Vehicle::whereNotNull('current_lat')->whereNotNull('current_lng')->with('driver')->get();
        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $vehicles->map(fn($v) => [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [$v->current_lng, $v->current_lat]],
                'properties' => [
                    'id' => $v->id, 'plate_number' => $v->plate_number, 'type' => $v->type,
                    'status' => $v->status, 'fuel_level' => $v->fuel_level, 'driver' => $v->driver?->name,
                ],
            ])->values()->toArray(),
        ]);
    }

    public function spatialAnalysis(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:radius_search,nearest_dumpsite',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:10',
        ]);

        return $validated['type'] === 'radius_search'
            ? $this->radiusSearch($request)
            : $this->nearestDumpsite($request);
    }

    private function radiusSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        $radius = (float) $request->input('radius', 0.5);
        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;

        $containers = Container::all()->filter(fn($c) =>
            $c->latitude !== null && $c->longitude !== null &&
            GISHelper::distance($lat, $lng, $c->latitude, $c->longitude) <= $radius
        )->values();

        $dumpsites = Dumpsite::active()->get()->filter(fn($d) =>
            $d->latitude !== null && $d->longitude !== null &&
            GISHelper::distance($lat, $lng, $d->latitude, $d->longitude) <= $radius
        )->values();

        return response()->json([
            'containers' => GISHelper::toGeoJsonFeatureCollection($containers),
            'dumpsites' => GISHelper::toGeoJsonFeatureCollection($dumpsites),
            'stats' => ['containers_found' => $containers->count(), 'dumpsites_found' => $dumpsites->count(), 'radius_km' => $radius],
        ]);
    }

    private function nearestDumpsite(Request $request): \Illuminate\Http\JsonResponse
    {
        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;
        $nearest = Dumpsite::active()->get()->filter(fn($d) => $d->latitude !== null && $d->longitude !== null)
            ->sortBy(fn($d) => GISHelper::distance($lat, $lng, $d->latitude, $d->longitude))->first();

        if (!$nearest) return response()->json(['error' => 'No active dumpsites found'], 404);

        return response()->json([
            'dumpsite' => $nearest->toGeoJson(),
            'distance_km' => round(GISHelper::distance($lat, $lng, $nearest->latitude, $nearest->longitude), 2),
        ]);
    }

    public function uploadGeojson(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:json,geojson']);
        $geojson = json_decode(file_get_contents($request->file('file')->getRealPath()), true);
        if (!$geojson || !isset($geojson['type'])) return response()->json(['error' => 'Invalid GeoJSON file'], 422);
        return response()->json(['success' => true, 'geojson' => $geojson, 'feature_count' => count($geojson['features'] ?? [])]);
    }

    public function exportGeojson(Request $request)
    {
        $layer = $request->get('layer', 'containers');
        $geojson = match ($layer) {
            'containers' => GISHelper::toGeoJsonFeatureCollection(Container::all()),
            'vehicles' => GISHelper::toGeoJsonFeatureCollection(Vehicle::whereNotNull('current_lat')->whereNotNull('current_lng')->get()),
            'dumpsites' => GISHelper::toGeoJsonFeatureCollection(Dumpsite::all()),
            default => ['type' => 'FeatureCollection', 'features' => []],
        };
        return response()->json($geojson)->header('Content-Disposition', "attachment; filename={$layer}-" . date('Y-m-d') . '.geojson');
    }
}
