<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Dumpsite;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'containers' => [
                'total'          => Container::count(),
                'active'         => Container::where('status', 'active')->count(),
                'needs_emptying' => Container::where('fill_level', '>=', 80)->count(),
                'full'           => Container::where('status', 'full')->count(),
                'avg_fill'       => round(Container::avg('fill_level'), 1),
            ],
            'vehicles' => [
                'total'       => Vehicle::count(),
                'active'      => Vehicle::where('status', 'active')->count(),
                'on_route'    => Vehicle::where('status', 'on_route')->count(),
                'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            ],
            'drivers' => [
                'total'    => Driver::count(),
                'active'   => Driver::where('status', 'active')->count(),
                'on_leave' => Driver::where('status', 'on_leave')->count(),
            ],
            'routes' => [
                'today_total'     => Route::whereDate('scheduled_at', today())->count(),
                'today_completed' => Route::whereDate('scheduled_at', today())->where('status', 'completed')->count(),
                'today_active'    => Route::whereDate('scheduled_at', today())->where('status', 'active')->count(),
                'total_distance'  => round(Route::where('status', 'completed')->whereDate('completed_at', today())->sum('actual_distance'), 1),
            ],
            'complaints' => [
                'total'    => Complaint::count(),
                'open'     => Complaint::where('status', 'open')->count(),
                'urgent'   => Complaint::where('priority', 'urgent')->where('status', 'open')->count(),
                'resolved_today' => Complaint::whereDate('resolved_at', today())->count(),
            ],
            'dumpsites' => [
                'total'  => Dumpsite::count(),
                'active' => Dumpsite::where('status', 'active')->count(),
                'avg_fill' => round(Dumpsite::avg('fill_percentage'), 1),
            ],
        ];

        // Collection trend (last 7 days)
        $collectionTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $collectionTrend[] = [
                'date'      => $date->format('M d'),
                'completed' => Route::whereDate('completed_at', $date)->where('status', 'completed')->count(),
                'distance'  => round(Route::whereDate('completed_at', $date)->sum('actual_distance'), 1),
            ];
        }

        // Container fill distribution
        $fillDistribution = [
            ['range' => '0-25%',  'count' => Container::whereBetween('fill_level', [0, 25])->count()],
            ['range' => '26-50%', 'count' => Container::whereBetween('fill_level', [26, 50])->count()],
            ['range' => '51-75%', 'count' => Container::whereBetween('fill_level', [51, 75])->count()],
            ['range' => '76-100%','count' => Container::whereBetween('fill_level', [76, 100])->count()],
        ];

        // Complaints by category
        $complaintsByCategory = Complaint::selectRaw('category, count(*) as count')
            ->groupBy('category')->get()
            ->map(fn($c) => ['category' => ucfirst(str_replace('_', ' ', $c->category)), 'count' => $c->count]);

        // Active routes
        $activeRoutes = Route::with(['vehicle', 'driver'])
            ->where('status', 'active')
            ->orWhere(fn($q) => $q->where('status', 'planned')->whereDate('scheduled_at', today()))
            ->limit(5)->get();

        // Critical containers (fill >= 80%)
        $criticalContainers = Container::where('fill_level', '>=', 80)
            ->orderByDesc('fill_level')
            ->limit(5)->get();

        // Recent complaints
        $recentComplaints = Complaint::with('user')
            ->orderByDesc('created_at')
            ->limit(5)->get();

        // Upcoming maintenance
        $upcomingMaintenance = Vehicle::whereDate('next_maintenance', '<=', now()->addDays(7))
            ->orderBy('next_maintenance')
            ->limit(5)->get();

        return view('waste_management.dashboard', compact(
            'stats', 'collectionTrend', 'fillDistribution',
            'complaintsByCategory', 'activeRoutes', 'criticalContainers',
            'recentComplaints', 'upcomingMaintenance'
        ));
    }

    public function markAllNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function profile(Request $request)
    {
        return view('waste_management.profile', ['user' => $request->user()]);
    }

    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'weekly_collections');

        return match($type) {
            'weekly_collections' => $this->weeklyCollections(),
            'container_types'    => $this->containerTypes(),
            'vehicle_status'     => $this->vehicleStatus(),
            'complaint_trend'    => $this->complaintTrend(),
            default              => [],
        };
    }

    private function weeklyCollections(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'label' => $date->format('D'),
                'value' => Route::whereDate('completed_at', $date)->count(),
            ];
        }
        return $data;
    }

    private function containerTypes(): array
    {
        return Container::selectRaw('type, count(*) as count')
            ->groupBy('type')->get()
            ->map(fn($c) => ['label' => ucfirst($c->type), 'value' => $c->count])
            ->toArray();
    }

    private function vehicleStatus(): array
    {
        return Vehicle::selectRaw('status, count(*) as count')
            ->groupBy('status')->get()
            ->map(fn($v) => ['label' => ucfirst(str_replace('_', ' ', $v->status)), 'value' => $v->count])
            ->toArray();
    }

    private function complaintTrend(): array
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'label'    => $date->format('M d'),
                'open'     => Complaint::whereDate('created_at', $date)->count(),
                'resolved' => Complaint::whereDate('resolved_at', $date)->count(),
            ];
        }
        return $data;
    }
}
