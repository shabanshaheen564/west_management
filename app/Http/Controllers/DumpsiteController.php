<?php

namespace App\Http\Controllers;

use App\Models\Dumpsite;
use Illuminate\Http\Request;

class DumpsiteController extends Controller
{
    public function index(Request $request)
    {
        $query = Dumpsite::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('name', 'like', "%$q%")
                ->orWhere('code', 'like', "%$q%"));
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('type'))   $query->where('type', $request->type);

        $dumpsites = $query->orderBy('name')->paginate(12)->withQueryString();

        $stats = [
            'total'    => Dumpsite::count(),
            'active'   => Dumpsite::where('status', 'active')->count(),
            'avg_fill' => round(Dumpsite::avg('fill_percentage') ?? 0),
            'full'     => Dumpsite::where('status', 'full')->count(),
        ];

        return view('waste_management.dumpsites', compact('dumpsites', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'name_ar'        => 'nullable|string',
            'type'           => 'required|in:landfill,transfer_station,recycling_center,composting',
            'status'         => 'required|in:active,inactive,full,maintenance',
            'total_capacity' => 'required|numeric|min:0',
            'current_fill'   => 'nullable|numeric|min:0',
            'latitude'       => 'required|numeric',
            'longitude'      => 'required|numeric',
            'opening_time'   => 'nullable|date_format:H:i',
            'closing_time'   => 'nullable|date_format:H:i',
            'address'        => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $validated['code'] = 'DS-' . strtoupper(uniqid());
        Dumpsite::create($validated);

        return redirect()->route('dumpsites.index')
            ->with('success', __('Dumpsite created successfully'));
    }

    public function update(Request $request, Dumpsite $dumpsite)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'name_ar'        => 'nullable|string',
            'type'           => 'required|in:landfill,transfer_station,recycling_center,composting',
            'status'         => 'required|in:active,inactive,full,maintenance',
            'total_capacity' => 'required|numeric|min:0',
            'current_fill'   => 'nullable|numeric|min:0',
            'latitude'       => 'required|numeric',
            'longitude'      => 'required|numeric',
            'opening_time'   => 'nullable|date_format:H:i',
            'closing_time'   => 'nullable|date_format:H:i',
            'address'        => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $dumpsite->update($validated);

        return redirect()->route('dumpsites.index')
            ->with('success', __('Dumpsite updated successfully'));
    }

    public function destroy(Dumpsite $dumpsite)
    {
        $dumpsite->delete();
        return redirect()->route('dumpsites.index')
            ->with('success', __('Dumpsite deleted successfully'));
    }

    public function create()
    {
        return view('waste_management.dumpsites');
    }

    public function show(Dumpsite $dumpsite)
    {
        return view('waste_management.dumpsites', compact('dumpsite'));
    }

    public function edit(Dumpsite $dumpsite)
    {
        return view('waste_management.dumpsites', compact('dumpsite'));
    }
}