<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\Vehicle;
use App\Models\Dumpsite;
use App\Models\Complaint;
use App\Helpers\GISHelper;
use Illuminate\Http\Request;

class MapDataController extends Controller
{
    public function summary()
    {
        return response()->json([
            'containers' => Container::count(),
            'vehicles'   => Vehicle::count(),
            'dumpsites'  => Dumpsite::count(),
            'complaints_open' => Complaint::where('status', 'open')->count(),
        ]);
    }

    public function containers(Request $request)
    {
        $query = Container::query();
        if ($request->filled('zone'))   $query->where('zone', $request->zone);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('type'))   $query->where('type', $request->type);

        return response()->json(GISHelper::toGeoJsonFeatureCollection($query->get()));
    }

    public function vehicles()
    {
        $vehicles = Vehicle::whereNotNull('current_lat')->whereNotNull('current_lng')->get();

        $features = $vehicles->filter(fn ($v) => !empty($v->toGeoJson()))
            ->map(fn ($v) => $v->toGeoJson())
            ->values();

        return response()->json(['type' => 'FeatureCollection', 'features' => $features]);
    }

    public function dumpsites()
    {
        return response()->json(GISHelper::toGeoJsonFeatureCollection(Dumpsite::all()));
    }

    public function heatmap()
    {
        $containers = Container::select('latitude', 'longitude', 'fill_level')->get();
        return response()->json(GISHelper::generateHeatmapData($containers));
    }

    public function complaints()
    {
        $complaints = Complaint::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('status', '!=', 'closed')
            ->get();

        $features = $complaints->map(fn ($c) => [
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
        ])->values();

        return response()->json(['type' => 'FeatureCollection', 'features' => $features]);
    }
}
