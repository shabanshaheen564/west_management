{{-- REPORTS VIEW --}}
@extends('layouts.skeleton')
@section('title', __('Reports'))
@section('page-title', __('Reports & Analytics'))

@section('content')
<div class="page-header">
    <div class="page-header-icon" style="background:#e07b39;"><i class="fas fa-chart-bar"></i></div>
    <div><h4>{{ __('Reports') }}</h4><p>{{ __('Generate and download system reports') }}</p></div>
</div>

<div class="row g-3">
    @foreach([
        ['type'=>'dashboard','label'=>__('Dashboard Summary'),'icon'=>'fa-chart-pie','color'=>'#2d8a4e','desc'=>__('Overall system statistics and KPIs')],
        ['type'=>'containers','label'=>__('Containers Report'),'icon'=>'fa-trash-alt','color'=>'#1a6b9a','desc'=>__('Container inventory, fill levels, zones')],
        ['type'=>'vehicles','label'=>__('Vehicles Report'),'icon'=>'fa-truck','color'=>'#7b3fa0','desc'=>__('Fleet status, maintenance schedule')],
        ['type'=>'routes','label'=>__('Routes Report'),'icon'=>'fa-route','color'=>'#e07b39','desc'=>__('Collection routes, distances, completion')],
        ['type'=>'complaints','label'=>__('Complaints Report'),'icon'=>'fa-exclamation-circle','color'=>'#dc3545','desc'=>__('Complaint trends, resolution times')],
    ] as $r)
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;background:{{ $r['color'] }};border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem;flex-shrink:0;">
                        <i class="fas {{ $r['icon'] }}"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">{{ $r['label'] }}</h6>
                        <small class="text-muted">{{ $r['desc'] }}</small>
                    </div>
                </div>
                <form method="POST" action="{{ route('reports.generate') }}">
                    @csrf
                    <input type="hidden" name="type" value="{{ $r['type'] }}">
                    <input type="hidden" name="format" value="pdf">
                    @if(in_array($r['type'],['routes','complaints']))
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:.75rem;">{{ __('From') }}</label>
                            <input type="date" name="date_from" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:.75rem;">{{ __('To') }}</label>
                            <input type="date" name="date_to" class="form-control form-control-sm">
                        </div>
                    </div>
                    @endif
                    @if($r['type']==='containers')
                    <div class="mb-3">
                        <select name="status" class="form-select form-select-sm mb-2">
                            <option value="">{{ __('All Status') }}</option>
                            @foreach(['active','inactive','maintenance','full'] as $s)
                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <button type="submit" class="btn btn-sm w-100" style="background:{{ $r['color'] }};color:#fff;">
                        <i class="fas fa-file-pdf me-1"></i>{{ __('Generate PDF') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Charts Section --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line text-primary"></i>
                <h6>{{ __('Monthly Trend Analysis') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" style="height:250px;"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
fetch('{{ route("dashboard.chart-data") }}?type=complaint_trend')
    .then(r => r.json())
    .then(data => {
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: data.map(d => d.label),
                datasets: [
                    { label: '{{ __("New Complaints") }}', data: data.map(d => d.open), borderColor:'#dc3545', backgroundColor:'rgba(220,53,69,.1)', fill:true, tension:.4 },
                    { label: '{{ __("Resolved") }}', data: data.map(d => d.resolved), borderColor:'#2d8a4e', backgroundColor:'rgba(45,138,78,.1)', fill:true, tension:.4 },
                ]
            },
            options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'top' } },
                scales:{ x:{ grid:{ display:false } }, y:{ grid:{ color:'#f0f4f8' } } } }
        });
    });
</script>
@endpush
