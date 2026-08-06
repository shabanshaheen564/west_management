<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Exports\ContainersExport;
use App\Imports\ContainersImport;
use App\Helpers\GISHelper;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ContainerController extends Controller
{
    public function index(Request $request)
    {
        $query = Container::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('name', 'like', "%$q%")
                                         ->orWhere('code', 'like', "%$q%")
                                         ->orWhere('address', 'like', "%$q%"));
        }
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('type'))    $query->where('type', $request->type);
        if ($request->filled('zone'))    $query->where('zone', $request->zone);

        $containers = $query->orderByDesc('fill_level')->paginate(15)->withQueryString();

        $zones = Container::distinct()->pluck('zone')->filter()->sort()->values();

        $stats = [
            'total'          => Container::count(),
            'needs_emptying' => Container::where('fill_level', '>=', 80)->count(),
            'avg_fill'       => round(Container::avg('fill_level'), 1),
            'active'         => Container::where('status', 'active')->count(),
        ];

        return view('waste_management.containers', compact('containers', 'zones', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'       => 'required|string|unique:containers,code',
            'name'       => 'required|string|max:255',
            'name_ar'    => 'nullable|string|max:255',
            'type'       => 'required|in:general,recyclable,organic,hazardous,electronic',
            'capacity'   => 'required|numeric|min:1',
            'fill_level' => 'nullable|numeric|min:0|max:100',
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
            'address'    => 'nullable|string',
            'address_ar' => 'nullable|string',
            'zone'       => 'nullable|string',
            'status'     => 'required|in:active,inactive,maintenance,full',
            'rfid_tag'   => 'nullable|string|unique:containers,rfid_tag',
            'notes'      => 'nullable|string',
        ]);

        $container = Container::create($validated);

        return redirect()->route('containers.index')
            ->with('success', __('Container created successfully'));
    }

    public function show(Container $container)
    {
        $container->load('routes');
        $nearbyContainers = Container::where('id', '!=', $container->id)
            ->get()
            ->filter(fn($c) => GISHelper::distance(
                $container->latitude, $container->longitude, $c->latitude, $c->longitude
            ) <= 0.5)
            ->values();

        return response()->json([
            'container'          => $container,
            'nearby_containers'  => $nearbyContainers,
        ]);
    }

    public function update(Request $request, Container $container)
    {
        $validated = $request->validate([
            'code'       => 'required|string|unique:containers,code,' . $container->id,
            'name'       => 'required|string|max:255',
            'name_ar'    => 'nullable|string|max:255',
            'type'       => 'required|in:general,recyclable,organic,hazardous,electronic',
            'capacity'   => 'required|numeric|min:1',
            'fill_level' => 'nullable|numeric|min:0|max:100',
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
            'address'    => 'nullable|string',
            'address_ar' => 'nullable|string',
            'zone'       => 'nullable|string',
            'status'     => 'required|in:active,inactive,maintenance,full',
            'rfid_tag'   => 'nullable|string|unique:containers,rfid_tag,' . $container->id,
            'notes'      => 'nullable|string',
        ]);

        $container->update($validated);

        return redirect()->route('containers.index')
            ->with('success', __('Container updated successfully'));
    }

    public function destroy(Container $container)
    {
        $container->delete();
        return redirect()->route('containers.index')
            ->with('success', __('Container deleted successfully'));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['zone', 'status', 'type']);
        return Excel::download(new ContainersExport($filters), 'containers-' . date('Y-m-d') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new ContainersImport, $request->file('file'));

        return redirect()->route('containers.index')
            ->with('success', __('Containers imported successfully'));
    }

    public function geojson(Request $request)
    {
        $query = Container::query();
        if ($request->filled('zone'))    $query->where('zone', $request->zone);
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('type'))    $query->where('type', $request->type);
        if ($request->filled('min_fill')) $query->where('fill_level', '>=', $request->min_fill);

        $containers = $query->get();
        return response()->json(GISHelper::toGeoJsonFeatureCollection($containers));
    }

    public function heatmap()
    {
        $containers = Container::select('latitude', 'longitude', 'fill_level')->get();
        return response()->json(GISHelper::generateHeatmapData($containers));
    }

    public function updateFillLevel(Request $request, Container $container)
    {
        $request->validate(['fill_level' => 'required|numeric|min:0|max:100']);

        $container->update([
            'fill_level'      => $request->fill_level,
            'last_checked_at' => now(),
            'status'          => $request->fill_level >= 100 ? 'full' : $container->status,
        ]);

        return response()->json(['success' => true, 'container' => $container]);
    }

    public function markEmptied(Container $container)
    {
        $container->update([
            'fill_level'      => 0,
            'last_emptied_at' => now(),
            'status'          => 'active',
        ]);

        return response()->json(['success' => true]);
    }
}
