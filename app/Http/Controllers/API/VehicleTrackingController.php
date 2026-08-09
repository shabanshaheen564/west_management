<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleTrackingController extends Controller
{
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

    public function updateLocation(Request $request, Vehicle $vehicle)
    {
        $user = $request->user();
        $canUpdateAnyVehicle = $user->hasAnyRole(['admin', 'supervisor']);
        $isAssignedDriver = $user->driver && $vehicle->driver_id === $user->driver->id;

        if (!$canUpdateAnyVehicle && !$isAssignedDriver) {
            return response()->json([
                'success' => false,
                'message' => __('You are not allowed to update this vehicle location.'),
            ], 403);
        }

        $validated = $request->validate([
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
            'speed'      => 'nullable|numeric|min:0',
            'heading'    => 'nullable|numeric|between:0,360',
            'fuel_level' => 'nullable|numeric|min:0|max:100',
        ]);

        $history = is_array($vehicle->gps_data) ? $vehicle->gps_data : [];
        $history[] = [
            'lat'         => $validated['latitude'],
            'lng'         => $validated['longitude'],
            'speed'       => $validated['speed'] ?? null,
            'heading'     => $validated['heading'] ?? null,
            'fuel_level'  => $validated['fuel_level'] ?? null,
            'recorded_at' => now()->toISOString(),
        ];
        $history = array_slice($history, -200);

        $vehicleData = [
            'current_lat' => $validated['latitude'],
            'current_lng' => $validated['longitude'],
            'gps_data'    => $history,
        ];

        if (array_key_exists('fuel_level', $validated)) {
            $vehicleData['fuel_level'] = $validated['fuel_level'];
        }

        $vehicle->update($vehicleData);

        return response()->json([
            'success' => true,
            'vehicle' => $vehicle->fresh()->load('driver'),
        ]);
    }

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
