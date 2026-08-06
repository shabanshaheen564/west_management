<?php

namespace App\Services\Reports;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Container;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Complaint;
use App\Models\Dumpsite;
use Carbon\Carbon;

class PdfReportService
{
    public function generateContainersReport(array $filters = []): \Barryvdh\DomPDF\PDF
    {
        $query = Container::query();

        if (!empty($filters['zone'])) {
            $query->where('zone', $filters['zone']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $containers = $query->get();
        $stats = [
            'total'         => $containers->count(),
            'active'        => $containers->where('status', 'active')->count(),
            'needs_emptying'=> $containers->where('fill_level', '>=', 80)->count(),
            'avg_fill'      => round($containers->avg('fill_level'), 1),
        ];

        return Pdf::loadView('reports.containers', compact('containers', 'stats', 'filters'))
                  ->setPaper('a4', 'landscape');
    }

    public function generateVehiclesReport(array $filters = []): \Barryvdh\DomPDF\PDF
    {
        $vehicles = Vehicle::with('driver')->get();
        $stats = [
            'total'       => $vehicles->count(),
            'active'      => $vehicles->where('status', 'active')->count(),
            'on_route'    => $vehicles->where('status', 'on_route')->count(),
            'maintenance' => $vehicles->where('status', 'maintenance')->count(),
        ];

        return Pdf::loadView('reports.vehicles', compact('vehicles', 'stats'))
                  ->setPaper('a4', 'landscape');
    }

    public function generateRoutesReport(array $filters = []): \Barryvdh\DomPDF\PDF
    {
        $query = Route::with(['vehicle', 'driver', 'dumpsite']);

        if (!empty($filters['date_from'])) {
            $query->whereDate('scheduled_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('scheduled_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $routes = $query->get();
        $stats = [
            'total'          => $routes->count(),
            'completed'      => $routes->where('status', 'completed')->count(),
            'total_distance' => round($routes->sum('actual_distance'), 1),
            'avg_duration'   => round($routes->avg('actual_duration')),
        ];

        return Pdf::loadView('reports.routes', compact('routes', 'stats', 'filters'))
                  ->setPaper('a4', 'landscape');
    }

    public function generateComplaintsReport(array $filters = []): \Barryvdh\DomPDF\PDF
    {
        $query = Complaint::with(['user', 'assignedTo']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $complaints = $query->get();
        $stats = [
            'total'       => $complaints->count(),
            'open'        => $complaints->where('status', 'open')->count(),
            'resolved'    => $complaints->where('status', 'resolved')->count(),
            'urgent'      => $complaints->where('priority', 'urgent')->count(),
            'avg_resolve' => round($complaints->whereNotNull('resolved_at')
                ->map(fn($c) => $c->created_at->diffInDays($c->resolved_at))
                ->avg()),
        ];

        return Pdf::loadView('reports.complaints', compact('complaints', 'stats', 'filters'))
                  ->setPaper('a4', 'landscape');
    }

    public function generateDashboardReport(): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'generated_at'    => now()->format('Y-m-d H:i:s'),
            'containers'      => [
                'total'         => Container::count(),
                'needs_emptying'=> Container::where('fill_level', '>=', 80)->count(),
                'avg_fill'      => round(Container::avg('fill_level'), 1),
            ],
            'vehicles' => [
                'total'    => Vehicle::count(),
                'active'   => Vehicle::where('status', 'active')->count(),
                'on_route' => Vehicle::where('status', 'on_route')->count(),
            ],
            'routes' => [
                'today_total'    => Route::whereDate('scheduled_at', today())->count(),
                'today_completed'=> Route::whereDate('scheduled_at', today())->where('status', 'completed')->count(),
            ],
            'complaints' => [
                'open'   => Complaint::where('status', 'open')->count(),
                'urgent' => Complaint::where('priority', 'urgent')->where('status', 'open')->count(),
            ],
        ];

        return Pdf::loadView('reports.dashboard', compact('data'))
                  ->setPaper('a4');
    }
}
