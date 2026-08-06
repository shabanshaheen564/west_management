<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Imports\DriversImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $drivers = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => Driver::count(),
            'active' => Driver::where('status', 'active')->count(),
            'on_leave' => Driver::where('status', 'on_leave')->count(),
            'suspended' => Driver::where('status', 'suspended')->count(),
        ];

        return view('waste_management.drivers', compact('drivers', 'stats'));
    }

    public function create()
    {
        return view('waste_management.drivers');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'     => 'required|string|unique:drivers,employee_id',
            'name'            => 'required|string|max:255',
            'name_ar'         => 'nullable|string|max:255',
            'phone'           => 'required|string',
            'email'           => 'nullable|email',
            'national_id'     => 'nullable|string|unique:drivers,national_id',
            'license_number'  => 'required|string|unique:drivers,license_number',
            'license_class'   => 'required|in:A,B,C,D',
            'license_expiry'  => 'required|date',
            'hire_date'       => 'required|date',
            'status'          => 'required|in:active,inactive,on_leave,suspended',
            'address'         => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);

        Driver::create($validated);

        return redirect()->route('drivers.index')
            ->with('success', __('Driver created successfully'));
    }

    public function show(Driver $driver)
    {
        $driver->load('routes', 'vehicle');
        return response()->json(['driver' => $driver]);
    }

    public function edit(Driver $driver)
    {
        return view('waste_management.drivers', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'employee_id'     => 'required|string|unique:drivers,employee_id,' . $driver->id,
            'name'            => 'required|string|max:255',
            'name_ar'         => 'nullable|string|max:255',
            'phone'           => 'required|string',
            'email'           => 'nullable|email',
            'national_id'     => 'nullable|string|unique:drivers,national_id,' . $driver->id,
            'license_number'  => 'required|string|unique:drivers,license_number,' . $driver->id,
            'license_class'   => 'required|in:A,B,C,D',
            'license_expiry'  => 'required|date',
            'hire_date'       => 'required|date',
            'status'          => 'required|in:active,inactive,on_leave,suspended',
            'address'         => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);

        $driver->update($validated);

        return redirect()->route('drivers.index')
            ->with('success', __('Driver updated successfully'));
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()->route('drivers.index')
            ->with('success', __('Driver deleted successfully'));
    }

    public function export()
    {
        return Excel::download(new \App\Exports\DriversExport, 'drivers-' . date('Y-m-d') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new DriversImport, $request->file('file'));

        return redirect()->route('drivers.index')
            ->with('success', __('Drivers imported successfully'));
    }
}
