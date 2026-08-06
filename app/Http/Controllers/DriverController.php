<?php

namespace App\Http\Controllers;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::query();

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('employee_id', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        // Filter status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $drivers = $query->latest()->paginate(10);

        $stats = [
            'total' => Driver::count(),
            'active' => Driver::where('status', 'active')->count(),
            'on_leave' => Driver::where('status', 'on_leave')->count(),
            'suspended' => Driver::where('status', 'suspended')->count(),
        ];

        return view('waste_management.drivers', compact('drivers', 'stats'));
    }
    public function export()
    {
        return Excel::download(new \App\Exports\DriversExport, 'drivers-' . date('Y-m-d') . '.xlsx');
    }
}