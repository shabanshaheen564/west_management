@extends('layouts.skeleton')
@section('title', __('Dashboard'))
@section('page-title', __('Dashboard'))


@push('styles')
<style>
.stat-card-green  { background: linear-gradient(135deg, #2d8a4e, #1f6438); }
.stat-card-blue   { background: linear-gradient(135deg, #1a6b9a, #0e4d73); }
.stat-card-orange { background: linear-gradient(135deg, #e07b39, #b85e20); }
.stat-card-red    { background: linear-gradient(135deg, #dc3545, #a71d2a); }
.stat-card-purple { background: linear-gradient(135deg, #7b3fa0, #5a2d75); }
.stat-card-teal   { background: linear-gradient(135deg, #17a2b8, #0f7a8a); }
.activity-item { display:flex; gap:.75rem; padding:.6rem 0; border-bottom:1px solid #f0f4f8; }
.activity-icon { width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0; }
.route-row:hover { cursor:pointer; }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-icon"><i class="fas fa-chart-pie"></i></div>
    <div>
        <h4>{{ __('Dashboard') }}</h4>
        <p>{{ __('Welcome back,') }} <strong>{{ auth()->user()->name }}</strong> — {{ now()->format('l, d M Y') }}</p>
    </div>
    <div class="ms-auto d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print me-1"></i>{{ __('Print') }}
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-file-pdf me-1"></i>{{ __('Full Report') }}
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-green">
            <div class="stat-icon" style="background:rgba(255,255,255,.2)"><i class="fas fa-trash-alt"></i></div>
            <div>
                <div class="stat-value">{{ $stats['containers']['total'] }}</div>
                <div class="stat-label">{{ __('Containers') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-orange">
            <div class="stat-icon" style="background:rgba(255,255,255,.2)"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="stat-value">{{ $stats['containers']['needs_emptying'] }}</div>
                <div class="stat-label">{{ __('Needs Emptying') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-blue">
            <div class="stat-icon" style="background:rgba(255,255,255,.2)"><i class="fas fa-truck"></i></div>
            <div>
                <div class="stat-value">{{ $stats['vehicles']['total'] }}</div>
                <div class="stat-label">{{ __('Vehicles') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-green" style="background:linear-gradient(135deg,#38a169,#276749)">
            <div class="stat-icon" style="background:rgba(255,255,255,.2)"><i class="fas fa-route"></i></div>
            <div>
                <div class="stat-value">{{ $stats['routes']['today_total'] }}</div>
                <div class="stat-label">{{ __("Today's Routes") }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-red">
            <div class="stat-icon" style="background:rgba(255,255,255,.2)"><i class="fas fa-exclamation-circle"></i></div>
            <div>
                <div class="stat-value">{{ $stats['complaints']['open'] }}</div>
                <div class="stat-label">{{ __('Open Complaints') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-teal">
            <div class="stat-icon" style="background:rgba(255,255,255,.2)"><i class="fas fa-industry"></i></div>
            <div>
                <div class="stat-value">{{ $stats['dumpsites']['total'] }}</div>
                <div class="stat-label">{{ __('Dumpsites') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line text-primary"></i>
                <h6>{{ __('Collection Trend (Last 7 Days)') }}</h6>
                <span class="badge bg-success ms-auto">{{ $stats['routes']['today_completed'] }} {{ __('Completed Today') }}</span>
            </div>
            <div class="card-body" style="height:260px;position:relative;">
                <canvas id="collectionTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie text-warning"></i>
                <h6>{{ __('Fill Distribution') }}</h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center" style="height:260px;">
                <canvas id="fillDistChart" style="max-height:140px;max-width:140px;"></canvas>
                <div class="mt-2 w-100">
                    @php $fillColors = ['success','info','warning','danger']; @endphp
                    @foreach($fillDistribution as $i => $d)
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <small class="text-muted" style="width:50px;font-size:.72rem;">{{ $d['range'] }}</small>
                        <div class="flex-grow-1">
                            <div class="progress" style="height:5px;">
                                <div class="progress-bar bg-{{ $fillColors[$i] }}" style="width:{{ $stats['containers']['total'] > 0 ? round($d['count']/$stats['containers']['total']*100) : 0 }}%"></div>
                            </div>
                        </div>
                        <small class="fw-bold" style="font-size:.72rem;">{{ $d['count'] }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-fire text-danger"></i>
                <h6>{{ __('Critical Containers') }}</h6>
                <a href="{{ route('containers.index') }}" class="ms-auto btn btn-sm btn-outline-danger">{{ __('View All') }}</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">{{ __('Container') }}</th>
                            <th>{{ __('Zone') }}</th>
                            <th>{{ __('Fill Level') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($criticalContainers as $c)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold">{{ $c->code }}</div>
                                <small class="text-muted">{{ Str::limit($c->address ?? '', 25) }}</small>
                            </td>
                            <td><span class="badge bg-secondary">{{ $c->zone ?? '-' }}</span></td>
                            <td style="min-width:100px;">
                                <div class="d-flex align-items-center gap-1">
                                    <div class="fill-indicator flex-grow-1">
                                        <div class="fill-bar bg-{{ $c->fill_color }}" style="width:{{ $c->fill_level }}%"></div>
                                    </div>
                                    <small class="fw-bold text-{{ $c->fill_color }}">{{ $c->fill_level }}%</small>
                                </div>
                            </td>
                            <td>
                                <form action="{{ route('containers.emptied', $c) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success" title="{{ __('Mark Emptied') }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-3 text-muted">
                                <i class="fas fa-check-circle text-success me-2"></i>{{ __('All containers are within normal levels') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-route text-primary"></i>
                <h6>{{ __("Today's Routes") }}</h6>
                <a href="{{ route('routes.index') }}" class="ms-auto btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">{{ __('Route') }}</th>
                            <th>{{ __('Driver') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeRoutes as $route)
                        <tr class="route-row" onclick="window.location='{{ route('routes.index') }}'">
                            <td class="ps-3">
                                <div class="fw-bold">{{ $route->name }}</div>
                                <small class="text-muted">{{ $route->vehicle?->plate_number ?? '-' }}</small>
                            </td>
                            <td>{{ $route->driver?->name ?? '-' }}</td>
                            <td><span class="badge bg-{{ $route->status_color }}">{{ ucfirst($route->status) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-3 text-muted">{{ __('No routes scheduled for today') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-exclamation-circle text-danger"></i>
                <h6>{{ __('Recent Complaints') }}</h6>
                <a href="{{ route('complaints.index') }}" class="ms-auto btn btn-sm btn-outline-danger">{{ __('View All') }}</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentComplaints as $c)
                <div class="activity-item px-3">
                    <div class="activity-icon" style="background:#f8d7da;">
                        <i class="fas fa-exclamation text-danger"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold" style="font-size:.82rem;">{{ $c->ticket_number }}</span>
                            <span class="badge bg-{{ $c->priority_color }}">{{ ucfirst($c->priority) }}</span>
                        </div>
                        <div style="font-size:.78rem;color:#555;">{{ Str::limit($c->subject, 45) }}</div>
                        <small class="text-muted">{{ $c->complainant_name }} · {{ $c->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-smile text-success d-block mb-1" style="font-size:1.5rem;"></i>
                    {{ __('No complaints yet') }}
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-wrench text-warning"></i>
                <h6>{{ __('Upcoming Vehicle Maintenance') }}</h6>
                <a href="{{ route('vehicles.index') }}" class="ms-auto btn btn-sm btn-outline-warning">{{ __('View All') }}</a>
            </div>
            <div class="card-body p-0">
                @forelse($upcomingMaintenance as $v)
                <div class="activity-item px-3">
                    <div class="activity-icon" style="background:#fff3cd;">
                        <i class="fas fa-truck text-warning"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold" style="font-size:.82rem;">{{ $v->plate_number }}</span>
                            @php $daysLeft = now()->diffInDays($v->next_maintenance, false); @endphp
                            <span class="badge bg-{{ $daysLeft < 0 ? 'danger' : ($daysLeft < 5 ? 'warning' : 'info') }}">
                                {{ $daysLeft < 0 ? __('Overdue') : $daysLeft.' '.__('days') }}
                            </span>
                        </div>
                        <div style="font-size:.78rem;color:#555;">{{ $v->brand }} {{ $v->model }}</div>
                        <small class="text-muted">{{ $v->next_maintenance?->format('d M Y') }}</small>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle text-success d-block mb-1" style="font-size:1.5rem;"></i>
                    {{ __('No maintenance due soon') }}
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const trendData = @json($collectionTrend);
new Chart(document.getElementById('collectionTrendChart'), {
    type: 'bar',
    data: {
        labels: trendData.map(d => d.date),
        datasets: [
            {
                label: '{{ __("Completed Routes") }}',
                data: trendData.map(d => d.completed),
                backgroundColor: 'rgba(45,138,78,.8)',
                borderRadius: 6,
                borderSkipped: false,
            },
            {
                label: '{{ __("Distance (km)") }}',
                data: trendData.map(d => d.distance),
                type: 'line',
                borderColor: '#1a6b9a',
                backgroundColor: 'rgba(26,107,154,.1)',
                tension: .4,
                fill: true,
                pointRadius: 4,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
        scales: {
            y:  { grid: { color: '#f0f4f8' } },
            y1: { position: 'right', grid: { display: false } },
            x:  { grid: { display: false } },
        }
    }
});

const fillData = @json($fillDistribution);
new Chart(document.getElementById('fillDistChart'), {
    type: 'doughnut',
    data: {
        labels: fillData.map(d => d.range),
        datasets: [{
            data: fillData.map(d => d.count),
            backgroundColor: ['#2d8a4e','#17a2b8','#ffc107','#dc3545'],
            borderWidth: 0, hoverOffset: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        cutout: '65%',
    }
});
</script>
@endpush