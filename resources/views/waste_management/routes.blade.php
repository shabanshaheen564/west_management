@extends('layouts.skeleton')
@section('title', __('Routes'))
@section('page-title', __('Collection Routes'))

@push('styles')
<style>
#routePlanMap { height: 320px; border-radius: 10px; }
.waypoint-item { display:flex;align-items:center;gap:.5rem;padding:.4rem .6rem;background:#f8fafc;border-radius:8px;margin-bottom:.3rem;font-size:.82rem; }
.waypoint-handle { cursor:grab;color:#bbb; }
.sortable-ghost { opacity:.4; }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-icon" style="background:#7b3fa0;"><i class="fas fa-route"></i></div>
    <div>
        <h4>{{ __('Routes Management') }}</h4>
        <p>{{ __('Plan and optimize waste collection routes') }}</p>
    </div>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('routes.export') }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-download me-1"></i>{{ __('Export') }}
        </a>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#routeModal" onclick="resetRouteForm()">
            <i class="fas fa-plus me-1"></i>{{ __('New Route') }}
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-3">
    @foreach([
        ['label'=>__('Total'), 'value'=>$stats['total'], 'color'=>'#7b3fa0', 'icon'=>'fa-route'],
        ['label'=>__('Active'), 'value'=>$stats['active'], 'color'=>'#2d8a4e', 'icon'=>'fa-play-circle'],
        ['label'=>__('Today'), 'value'=>$stats['today'], 'color'=>'#1a6b9a', 'icon'=>'fa-calendar-day'],
        ['label'=>__('Completed Today'), 'value'=>$stats['completed_today'], 'color'=>'#e07b39', 'icon'=>'fa-check-circle'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.8rem;font-weight:800;color:{{ $s['color'] }};">{{ $s['value'] }}</div>
            <small class="text-muted"><i class="fas {{ $s['icon'] }} me-1"></i>{{ $s['label'] }}</small>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="{{ __('Search...') }}" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('All Status') }}</option>
                    @foreach(['planned','active','completed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}" placeholder="{{ __('Date') }}">
            </div>
            <div class="col-6 col-md-2">
                <select name="vehicle" class="form-select form-select-sm">
                    <option value="">{{ __('All Vehicles') }}</option>
                    @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" {{ request('vehicle')==$v->id?'selected':'' }}>{{ $v->plate_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">{{ __('Filter') }}</button>
                <a href="{{ route('routes.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>
</div>

{{-- Routes Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">{{ __('Route') }}</th>
                        <th>{{ __('Vehicle') }}</th>
                        <th>{{ __('Driver') }}</th>
                        <th>{{ __('Dumpsite') }}</th>
                        <th>{{ __('Containers') }}</th>
                        <th>{{ __('Distance') }}</th>
                        <th>{{ __('Scheduled') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($routes as $route)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold">{{ $route->name }}</div>
                            <small class="text-muted">{{ $route->code }}</small>
                        </td>
                        <td>{{ $route->vehicle?->plate_number ?? '—' }}</td>
                        <td>{{ $route->driver?->name ?? '—' }}</td>
                        <td>{{ $route->dumpsite?->name ?? '—' }}</td>
                        <td><span class="badge bg-primary-subtle text-primary border">{{ $route->containers->count() }}</span></td>
                        <td>{{ $route->total_distance ? $route->total_distance.' km' : '—' }}</td>
                        <td>
                            @if($route->scheduled_at)
                            <small>{{ $route->scheduled_at->format('d M Y H:i') }}</small>
                            @else —@endif
                        </td>
                        <td><span class="badge bg-{{ $route->status_color }}">{{ ucfirst($route->status) }}</span></td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                @if($route->status === 'planned')
                                <form action="{{ route('routes.activate',$route) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-icon btn-sm btn-outline-success" title="{{ __('Activate') }}">
                                        <i class="fas fa-play" style="font-size:.7rem;"></i>
                                    </button>
                                </form>
                                @elseif($route->status === 'active')
                                <form action="{{ route('routes.complete',$route) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-icon btn-sm btn-outline-primary" title="{{ __('Complete') }}">
                                        <i class="fas fa-check" style="font-size:.7rem;"></i>
                                    </button>
                                </form>
                                @endif
                                <button class="btn btn-icon btn-sm btn-outline-info" title="{{ __('View on Map') }}"
                                    onclick="viewRouteOnMap({{ $route->id }})">
                                    <i class="fas fa-map-marker-alt" style="font-size:.7rem;"></i>
                                </button>
                                <button class="btn btn-icon btn-sm btn-outline-primary" title="{{ __('Edit') }}"
                                   @php $routeData = $route->load(['vehicle','driver','dumpsite','containers']); @endphp
                                    onclick='editRoute({{ Js::from($routeData) }})'>
                                    <i class="fas fa-edit" style="font-size:.7rem;"></i>
                                </button>
                                <form id="rdel-{{ $route->id }}" action="{{ route('routes.destroy',$route) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger" title="{{ __('Delete') }}"
                                        onclick="confirmDelete('rdel-{{ $route->id }}')">
                                        <i class="fas fa-trash" style="font-size:.7rem;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-5 text-muted">
                        <i class="fas fa-route" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                        {{ __('No routes found') }}
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($routes->hasPages())
        <div class="px-3 py-2 border-top">{{ $routes->links() }}</div>
        @endif
    </div>
</div>

{{-- ══ ROUTE MODAL ══ --}}
<div class="modal fade" id="routeModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#7b3fa0;color:#fff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="fas fa-route me-2"></i><span id="routeModalTitle">{{ __('New Route') }}</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="routeForm" method="POST" action="{{ route('routes.store') }}">
                @csrf
                <input type="hidden" name="_method" id="routeMethod" value="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Route Name') }} *</label>
                                    <input type="text" name="name" id="rf_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Route Name (AR)') }}</label>
                                    <input type="text" name="name_ar" id="rf_name_ar" class="form-control" dir="rtl">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Vehicle') }}</label>
                                    <select name="vehicle_id" id="rf_vehicle" class="form-select">
                                        <option value="">{{ __('Select Vehicle') }}</option>
                                        @foreach($vehicles as $v)
                                        <option value="{{ $v->id }}">{{ $v->plate_number }} — {{ $v->brand }} {{ $v->model }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Driver') }}</label>
                                    <select name="driver_id" id="rf_driver" class="form-select">
                                        <option value="">{{ __('Select Driver') }}</option>
                                        @foreach($drivers as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->employee_id }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Dumpsite') }}</label>
                                    <select name="dumpsite_id" id="rf_dumpsite" class="form-select">
                                        <option value="">{{ __('Select Dumpsite') }}</option>
                                        @foreach($dumpsites as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Frequency') }} *</label>
                                    <select name="frequency" id="rf_frequency" class="form-select" required>
                                        @foreach(['daily','alternate','weekly','monthly','on_demand'] as $f)
                                        <option value="{{ $f }}">{{ ucfirst(str_replace('_',' ',$f)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Scheduled At') }} *</label>
                                    <input type="datetime-local" name="scheduled_at" id="rf_scheduled_at" class="form-control" required>
                                </div>
                                <div class="col-md-6" id="statusField" style="display:none;">
                                    <label class="form-label">{{ __('Status') }}</label>
                                    <select name="status" id="rf_status" class="form-select">
                                        @foreach(['planned','active','completed','cancelled'] as $s)
                                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Notes') }}</label>
                                    <textarea name="notes" id="rf_notes" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Containers + Map --}}
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Containers') }}</label>
                            <div style="max-height:160px;overflow-y:auto;border:1.5px solid #e2e8f0;border-radius:8px;padding:.4rem;">
                                @foreach($containers as $c)
                                <div class="form-check py-1">
                                    <input class="form-check-input container-chk" type="checkbox"
                                        name="containers[]" value="{{ $c->id }}" id="cc{{ $c->id }}"
                                        onchange="updateContainerList()">
                                    <label class="form-check-label" for="cc{{ $c->id }}" style="font-size:.8rem;cursor:pointer;">
                                        <span class="badge bg-{{ ['general'=>'secondary','recyclable'=>'info','organic'=>'success','hazardous'=>'danger','electronic'=>'primary'][$c->type]??'secondary' }}" style="font-size:.65rem;">{{ $c->type[0] }}</span>
                                        {{ $c->code }}
                                        <small class="text-muted">{{ $c->zone }}</small>
                                        <span class="text-{{ $c->fill_color }}" style="font-size:.72rem;">{{ $c->fill_level }}%</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-2 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" onclick="selectFull()">
                                    <i class="fas fa-fire me-1 text-danger"></i>{{ __('Select Full (≥80%)') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1" onclick="optimizeRoute()">
                                    <i class="fas fa-magic me-1"></i>{{ __('Optimize') }}
                                </button>
                            </div>
                            <div id="selectedCount" class="text-center mt-1" style="font-size:.78rem;color:#888;">0 {{ __('containers selected') }}</div>
                        </div>

                        {{-- Route Map Preview --}}
                        <div class="col-12">
                            <label class="form-label">{{ __('Route Map Preview') }}</label>
                            <div id="routePlanMap"></div>
                            <div id="routeStats" class="mt-2 d-flex gap-3" style="font-size:.82rem;display:none!important;">
                                <span><i class="fas fa-road me-1 text-primary"></i><strong id="routeDist">—</strong> km</span>
                                <span><i class="fas fa-clock me-1 text-warning"></i><strong id="routeDur">—</strong> min</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>{{ __('Save Route') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let routePlanMap, routePathLayer, routeMarkers = [];

document.getElementById('routeModal').addEventListener('shown.bs.modal', () => {
    if (!routePlanMap) {
        routePlanMap = L.map('routePlanMap').setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM - 1);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(routePlanMap);
        routePathLayer = L.layerGroup().addTo(routePlanMap);
    }
    setTimeout(() => routePlanMap.invalidateSize(), 300);
});

function resetRouteForm() {
    document.getElementById('routeModalTitle').textContent = '{{ __("New Route") }}';
    document.getElementById('routeForm').action = '{{ route("routes.store") }}';
    document.getElementById('routeMethod').value = 'POST';
    document.getElementById('statusField').style.display = 'none';
    ['name','name_ar','notes'].forEach(f => { const el = document.getElementById('rf_'+f); if(el) el.value = ''; });
    document.querySelectorAll('.container-chk').forEach(c => c.checked = false);
    updateContainerList();
}

function editRoute(route) {
    document.getElementById('routeModalTitle').textContent = '{{ __("Edit Route") }}';
    document.getElementById('routeForm').action = `/routes/${route.id}`;
    document.getElementById('routeMethod').value = 'PUT';
    document.getElementById('statusField').style.display = 'block';
    document.getElementById('rf_name').value = route.name;
    document.getElementById('rf_name_ar').value = route.name_ar || '';
    document.getElementById('rf_vehicle').value = route.vehicle_id || '';
    document.getElementById('rf_driver').value = route.driver_id || '';
    document.getElementById('rf_dumpsite').value = route.dumpsite_id || '';
    document.getElementById('rf_frequency').value = route.frequency;
    document.getElementById('rf_status').value = route.status;
    document.getElementById('rf_notes').value = route.notes || '';
    if (route.scheduled_at) {
        document.getElementById('rf_scheduled_at').value = route.scheduled_at.replace(' ','T').slice(0,16);
    }
    const containerIds = (route.containers || []).map(c => c.id);
    document.querySelectorAll('.container-chk').forEach(chk => {
        chk.checked = containerIds.includes(parseInt(chk.value));
    });
    updateContainerList();
    new bootstrap.Modal(document.getElementById('routeModal')).show();
}

function updateContainerList() {
    const checked = document.querySelectorAll('.container-chk:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' {{ __("containers selected") }}';
}

function selectFull() {
    const containers = @json($containers->where('fill_level','>=',80)->pluck('id'));
    document.querySelectorAll('.container-chk').forEach(chk => {
        if (containers.includes(parseInt(chk.value))) chk.checked = true;
    });
    updateContainerList();
}

async function optimizeRoute() {
    const selected = [...document.querySelectorAll('.container-chk:checked')].map(c => parseInt(c.value));
    if (selected.length < 2) { toastr.warning('{{ __("Select at least 2 containers") }}'); return; }

    const btn = event.target;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("Optimizing...") }}';

    try {
        const res = await fetch('{{ route("routes.optimize") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ container_ids: selected, start_lat: DEFAULT_LAT, start_lng: DEFAULT_LNG }),
        });
        const data = await res.json();

        if (data.route_geojson) {
            routePathLayer.clearLayers();
            L.geoJSON(data.route_geojson, { style: { color:'#7b3fa0', weight:3 } }).addTo(routePathLayer);
        }
        if (data.ordered_containers?.length) {
            data.ordered_containers.forEach((c, i) => {
                const lat = c.latitude || c.lat;
                const lng = c.longitude || c.lng;
                if (lat && lng) {
                    L.circleMarker([lat, lng], { radius:10, color:'#7b3fa0', fillColor:'#7b3fa0', fillOpacity:.8 })
                        .bindTooltip((i+1).toString(), { permanent:true, className:'', direction:'center', offset:[0,0] })
                        .addTo(routePathLayer);
                }
            });
            const bounds = data.ordered_containers
                .filter(c => c.latitude || c.lat)
                .map(c => [c.latitude||c.lat, c.longitude||c.lng]);
            if (bounds.length) routePlanMap.fitBounds(bounds, { padding:[20,20] });
        }

        document.getElementById('routeStats').style.display = 'flex';
        document.getElementById('routeDist').textContent = data.total_distance || '—';
        document.getElementById('routeDur').textContent  = data.total_duration || '—';
        toastr.success('{{ __("Route optimized successfully!") }}');
    } catch(e) {
        toastr.error('{{ __("Optimization failed") }}');
    }

    btn.disabled = false; btn.innerHTML = '<i class="fas fa-magic me-1"></i>{{ __("Optimize") }}';
}

async function viewRouteOnMap(routeId) {
    window.location.href = '{{ url("map") }}';
}
</script>
@endpush
