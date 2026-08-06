<?php

namespace App\Http\Controllers;

use App\Imports\VehiclesImport;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use Maatwebsite\Excel\Facades\Excel;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $vehicles = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => Vehicle::count(),
            'active' => Vehicle::where('status', 'active')->count(),
            'on_route' => Vehicle::where('status', 'on_route')->count(),
            'maintenance' => Vehicle::where('status', 'maintenance')->count(),
        ];

        return view('waste_management.vehicles', compact('vehicles', 'stats'));
    }

    public function create()
    {
        return view('waste_management.vehicles');
    }

    private function rules($vehicleId = null): array
    {
        $plateUnique = 'unique:vehicles,plate_number' . ($vehicleId ? ",{$vehicleId}" : '');

        return [
            'plate_number'         => "required|string|{$plateUnique}",
            'brand'                => 'required|string|max:255',
            'model'                => 'required|string|max:255',
            'year'                 => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'type'                 => 'required|in:truck,mini_truck,compactor,tipper,suction',
            'capacity'             => 'required|numeric|min:0',
            'status'               => 'required|in:active,inactive,maintenance,on_route',
            'fuel_type'            => 'nullable|string',
            'fuel_level'           => 'nullable|numeric|min:0|max:100',
            'last_maintenance'     => 'nullable|date',
            'next_maintenance'     => 'nullable|date',
            'insurance_number'     => 'nullable|string',
            'insurance_expiry'     => 'nullable|date',
            'registration_number'  => 'nullable|string',
            'registration_expiry'  => 'nullable|date',
            'notes'                => 'nullable|string',
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Vehicle::create($validated);

        return redirect()->route('vehicles.index')
            ->with('success', __('Vehicle created successfully'));
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load('driver', 'routes');
        return response()->json(['vehicle' => $vehicle]);
    }

    public function edit(Vehicle $vehicle)
    {
        return view('waste_management.vehicles', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate($this->rules($vehicle->id));

        $vehicle->update($validated);

        return redirect()->route('vehicles.index')
            ->with('success', __('Vehicle updated successfully'));
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', __('Vehicle deleted successfully'));
    }

    public function updateLocation(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'current_lat' => 'required|numeric|between:-90,90',
            'current_lng' => 'required|numeric|between:-180,180',
        ]);

        $vehicle->update($validated);

        return response()->json(['success' => true, 'vehicle' => $vehicle]);
    }

    public function export()
    {
        return Excel::download(new \App\Exports\VehiclesExport, 'vehicles-' . date('Y-m-d') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new VehiclesImport, $request->file('file'));

        return redirect()->route('vehicles.index')
            ->with('success', __('Vehicles imported successfully'));
    }
}
