<?php

namespace App\Http\Controllers;
use Maatwebsite\Excel\Facades\Excel;  
use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query();

        if ($request->search) {
            $query->where('plate_number', 'like', "%{$request->search}%")
                ->orWhere('brand', 'like', "%{$request->search}%")
                ->orWhere('model', 'like', "%{$request->search}%");
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $vehicles = $query->paginate(10);

        $stats = [
            'total' => Vehicle::count(),
            'active' => Vehicle::where('status', 'active')->count(),
            'on_route' => Vehicle::where('status', 'on_route')->count(),
            'maintenance' => Vehicle::where('status', 'maintenance')->count(),
        ];

        return view('waste_management.vehicles', compact('vehicles', 'stats'));
    }

    public function store(Request $request)
    {
        Vehicle::create($request->all());
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        Vehicle::findOrFail($id)->update($request->all());
        return redirect()->back();
    }

    public function destroy($id)
    {
        Vehicle::findOrFail($id)->delete();
        return redirect()->back();
    }
    public function export()
    {
        return Excel::download(new \App\Exports\VehiclesExport, 'vehicles-' . date('Y-m-d') . '.xlsx');
    }
}