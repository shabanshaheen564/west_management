<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleTrackingController extends Controller
{
    /**
     * Current position of every trackable vehicle (for live tracking dashboards).
     */
    public function index()
    {
        $vehicles = Vehicle::whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->with('driver')
            ->get(['id', 'plate_number', 'type', 'status', 'current_lat', 'current_lng', 'fuel_level', 'driver_id']);

        return response()->json([
            'vehicles' => $vehicles->map(fn ($v) => [
                'id'           => $v->id,
                'plate_number' => $v->plate_number,
                'type'         => $v->type,
                'status'       => $v->status,
                'latitude'     => $v->current_lat,
                'longitude'    => $v->current_lng,
                'fuel_level'   => $v->fuel_level,
                'driver'       => $v->driver?->name,
                'updated_at'   => $v->updated_at,
            ]),
        ]);
    }

    /**
     * Push a new GPS point from a tracking device / mobile app.
     * We don't have a dedicated location-history table, so we keep a
     * capped rolling log inside the `gps_data` json column.
     */
    public function updateLocation(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed'     => 'nullable|numeric|min:0',
            'heading'   => 'nullable|numeric|between:0,360',
        ]);

        $history = $vehicle->gps_data ?? [];
        $history[] = [
            'lat'       => $validated['latitude'],
            'lng'       => $validated['longitude'],
            'speed'     => $validated['speed'] ?? null,
            'heading'   => $validated['heading'] ?? null,
            'recorded_at' => now()->toISOString(),
        ];
        // Keep only the most recent 200 points so the column doesn't grow forever.
        $history = array_slice($history, -200);

        $vehicle->update([
            'current_lat' => $validated['latitude'],
            'current_lng' => $validated['longitude'],
            'gps_data'    => $history,
        ]);

        return response()->json(['success' => true, 'vehicle' => $vehicle]);
    }

    /**
     * Return the recorded GPS trail for a vehicle.
     */
    public function getHistory(Request $request, Vehicle $vehicle)
    {
        $history = collect($vehicle->gps_data ?? []);

        if ($request->filled('from')) {
            $from = $request->date('from');
            $history = $history->filter(fn ($p) => isset($p['recorded_at']) && $p['recorded_at'] >= $from->toISOString());
        }
        if ($request->filled('to')) {
            $to = $request->date('to');
            $history = $history->filter(fn ($p) => isset($p['recorded_at']) && $p['recorded_at'] <= $to->toISOString());
        }

        return response()->json([
            'vehicle_id' => $vehicle->id,
            'points'     => $history->values(),
        ]);
    }
}
