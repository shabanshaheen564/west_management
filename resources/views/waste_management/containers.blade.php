@extends('layouts.skeleton')
@section('title', __('Containers'))
@section('page-title', __('Waste Containers'))

@push('styles')
<style>
#miniMap { height: 220px; border-radius: 10px; }
.container-type-badge { font-size:.7rem; padding:.3em .6em; border-radius:6px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; }
.fill-cell { min-width: 130px; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-icon"><i class="fas fa-trash-alt"></i></div>
    <div>
        <h4>{{ __('Containers Management') }}</h4>
        <p>{{ __('Monitor and manage all waste containers') }}</p>
    </div>
    <div class="ms-auto d-flex gap-2 flex-wrap">
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-excel me-1"></i>{{ __('Import') }}
        </button>
        <a href="{{ route('containers.export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}"
           class="btn btn-sm btn-outline-success">
            <i class="fas fa-download me-1"></i>{{ __('Export Excel') }}
        </a>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#containerModal" onclick="resetForm()">
            <i class="fas fa-plus me-1"></i>{{ __('Add Container') }}
        </button>
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.8rem;font-weight:800;color:#2d8a4e;">{{ $stats['total'] }}</div>
            <small class="text-muted">{{ __('Total') }}</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.8rem;font-weight:800;color:#17a2b8;">{{ $stats['active'] }}</div>
            <small class="text-muted">{{ __('Active') }}</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.8rem;font-weight:800;color:#dc3545;">{{ $stats['needs_emptying'] }}</div>
            <small class="text-muted">{{ __('Needs Emptying') }}</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.8rem;font-weight:800;color:#ffc107;">{{ $stats['avg_fill'] }}%</div>
            <small class="text-muted">{{ __('Avg Fill Level') }}</small>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="{{ __('Search code, name, address...') }}" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('All Status') }}</option>
                    @foreach(['active','inactive','maintenance','full'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">{{ __('All Types') }}</option>
                    @foreach(['general','recyclable','organic','hazardous','electronic'] as $t)
                    <option value="{{ $t }}" {{ request('type')==$t?'selected':'' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="zone" class="form-select form-select-sm">
                    <option value="">{{ __('All Zones') }}</option>
                    @foreach($zones as $z)
                    <option value="{{ $z }}" {{ request('zone')==$z?'selected':'' }}>{{ $z }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">{{ __('Filter') }}</button>
                <a href="{{ route('containers.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:40px;">#</th>
                        <th>{{ __('Code / Name') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Zone') }}</th>
                        <th class="fill-cell">{{ __('Fill Level') }}</th>
                        <th>{{ __('Capacity') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Last Emptied') }}</th>
                        <th class="text-center" style="width:120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($containers as $c)
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:.8rem;">{{ $c->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $c->code }}</div>
                            <small class="text-muted">{{ app()->getLocale()==='ar' && $c->name_ar ? $c->name_ar : $c->name }}</small>
                        </td>
                        <td>
                            <span class="container-type-badge
                                {{ match($c->type) {
                                    'recyclable'=>'bg-info-subtle text-info',
                                    'organic'=>'bg-success-subtle text-success',
                                    'hazardous'=>'bg-danger-subtle text-danger',
                                    'electronic'=>'bg-primary-subtle text-primary',
                                    default=>'bg-secondary-subtle text-secondary'
                                } }}">
                                <i class="fas {{ $c->type_icon }} me-1"></i>{{ ucfirst($c->type) }}
                            </span>
                        </td>
                        <td>
                            @if($c->zone)
                            <span class="badge bg-light text-dark border">{{ $c->zone }}</span>
                            @else<span class="text-muted">—</span>@endif
                        </td>
                        <td class="fill-cell">
                            <div class="d-flex align-items-center gap-2">
                                <div class="fill-indicator flex-grow-1" style="height:8px;">
                                    <div class="fill-bar bg-{{ $c->fill_color }}" style="width:{{ $c->fill_level }}%"></div>
                                </div>
                                <span class="fw-bold text-{{ $c->fill_color }}" style="font-size:.8rem;min-width:35px;">{{ $c->fill_level }}%</span>
                            </div>
                        </td>
                        <td><small>{{ number_format($c->capacity) }}L</small></td>
                        <td>
                            <span class="badge bg-{{ $c->status_color }}-subtle text-{{ $c->status_color }} border border-{{ $c->status_color }}-subtle">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>
                        <td>
                            @if($c->last_emptied_at)
                            <small title="{{ $c->last_emptied_at->format('Y-m-d H:i') }}">
                                {{ $c->last_emptied_at->diffForHumans() }}
                            </small>
                            @else<small class="text-muted">—</small>@endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-icon btn-sm btn-outline-primary" title="{{ __('Edit') }}"
                                    onclick='editContainer(@json($c))'>
                                    <i class="fas fa-edit" style="font-size:.75rem;"></i>
                                </button>
                                @if($c->fill_level > 0)
                                <form action="{{ route('containers.emptied',$c) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-icon btn-sm btn-outline-success" title="{{ __('Mark Emptied') }}">
                                        <i class="fas fa-check" style="font-size:.75rem;"></i>
                                    </button>
                                </form>
                                @endif
                                <form id="del-{{ $c->id }}" action="{{ route('containers.destroy',$c) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger" title="{{ __('Delete') }}"
                                        onclick="confirmDelete('del-{{ $c->id }}')">
                                        <i class="fas fa-trash" style="font-size:.75rem;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-trash-alt" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                            {{ __('No containers found') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($containers->hasPages())
        <div class="px-3 py-2 border-top">{{ $containers->links() }}</div>
        @endif
    </div>
</div>

{{-- ══ ADD/EDIT MODAL ══ --}}
<div class="modal fade" id="containerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:var(--primary);color:#fff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="fas fa-trash-alt me-2"></i><span id="modalTitle">{{ __('Add Container') }}</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="containerForm" method="POST" action="{{ route('containers.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="f_code" class="form-control" required placeholder="CNT-001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Name (EN)') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="f_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Name (AR)') }}</label>
                            <input type="text" name="name_ar" id="f_name_ar" class="form-control" dir="rtl">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                            <select name="type" id="f_type" class="form-select" required>
                                @foreach(['general','recyclable','organic','hazardous','electronic'] as $t)
                                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Capacity (Liters)') }} <span class="text-danger">*</span></label>
                            <input type="number" name="capacity" id="f_capacity" class="form-control" required min="1" value="1000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Fill Level (%)') }}</label>
                            <input type="number" name="fill_level" id="f_fill_level" class="form-control" min="0" max="100" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Latitude') }} <span class="text-danger">*</span></label>
                            <input type="number" name="latitude" id="f_latitude" class="form-control" required step="any" placeholder="31.9038">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Longitude') }} <span class="text-danger">*</span></label>
                            <input type="number" name="longitude" id="f_longitude" class="form-control" required step="any" placeholder="35.2034">
                        </div>
                        {{-- Mini Map for picking location --}}
                        <div class="col-12">
                            <label class="form-label">{{ __('Pick Location on Map') }}</label>
                            <div id="miniMap"></div>
                            <small class="text-muted">{{ __('Click on the map to set coordinates') }}</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Address (EN)') }}</label>
                            <input type="text" name="address" id="f_address" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Zone') }}</label>
                            <input type="text" name="zone" id="f_zone" class="form-control" list="zonesList" placeholder="Zone A">
                            <datalist id="zonesList">
                                @foreach($zones as $z)<option value="{{ $z }}">@endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select name="status" id="f_status" class="form-select" required>
                                @foreach(['active','inactive','maintenance','full'] as $s)
                                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('RFID Tag') }}</label>
                            <input type="text" name="rfid_tag" id="f_rfid_tag" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Address (AR)') }}</label>
                            <input type="text" name="address_ar" id="f_address_ar" class="form-control" dir="rtl">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" id="f_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>{{ __('Save Container') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══ IMPORT MODAL ══ --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#198754;color:#fff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="fas fa-file-excel me-2"></i>{{ __('Import Containers') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('containers.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info" style="font-size:.82rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ __('File must have columns: code, name, type, capacity, latitude, longitude, zone, status') }}
                    </div>
                    <label class="form-label fw-bold">{{ __('Excel / CSV File') }}</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-upload me-1"></i>{{ __('Import') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Mini map for location picker
let miniMap, miniMarker;
const modal = document.getElementById('containerModal');
modal.addEventListener('shown.bs.modal', () => {
    if (!miniMap) {
        miniMap = L.map('miniMap').setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(miniMap);
        miniMap.on('click', e => {
            document.getElementById('f_latitude').value  = e.latlng.lat.toFixed(6);
            document.getElementById('f_longitude').value = e.latlng.lng.toFixed(6);
            if (miniMarker) miniMarker.setLatLng(e.latlng);
            else miniMarker = L.marker(e.latlng, {draggable:true}).addTo(miniMap)
                .on('dragend', e2 => {
                    const p = e2.target.getLatLng();
                    document.getElementById('f_latitude').value  = p.lat.toFixed(6);
                    document.getElementById('f_longitude').value = p.lng.toFixed(6);
                });
        });
    }
    setTimeout(() => miniMap.invalidateSize(), 300);
});

function resetForm() {
    document.getElementById('modalTitle').textContent = '{{ __("Add Container") }}';
    document.getElementById('containerForm').action = '{{ route("containers.store") }}';
    document.getElementById('formMethod').value = 'POST';
    ['code','name','name_ar','capacity','fill_level','address','address_ar','zone','rfid_tag','notes'].forEach(f => {
        const el = document.getElementById('f_'+f);
        if (el) el.value = f === 'capacity' ? 1000 : f === 'fill_level' ? 0 : '';
    });
    document.getElementById('f_type').value   = 'general';
    document.getElementById('f_status').value = 'active';
    document.getElementById('f_latitude').value  = '';
    document.getElementById('f_longitude').value = '';
    if (miniMarker) { miniMarker.remove(); miniMarker = null; }
}

function editContainer(c) {
    document.getElementById('modalTitle').textContent = '{{ __("Edit Container") }}';
    document.getElementById('containerForm').action = `/containers/${c.id}`;
    document.getElementById('formMethod').value = 'PUT';
    const fields = {
        code: c.code, name: c.name, name_ar: c.name_ar || '',
        capacity: c.capacity, fill_level: c.fill_level,
        address: c.address || '', address_ar: c.address_ar || '',
        zone: c.zone || '', rfid_tag: c.rfid_tag || '', notes: c.notes || '',
    };
    Object.entries(fields).forEach(([k,v]) => {
        const el = document.getElementById('f_'+k);
        if (el) el.value = v;
    });
    document.getElementById('f_type').value   = c.type;
    document.getElementById('f_status').value = c.status;
    document.getElementById('f_latitude').value  = c.latitude;
    document.getElementById('f_longitude').value = c.longitude;

    const modal = new bootstrap.Modal(document.getElementById('containerModal'));
    modal.show();

    setTimeout(() => {
        if (miniMap) {
            const latlng = [c.latitude, c.longitude];
            miniMap.setView(latlng, 16);
            if (miniMarker) miniMarker.setLatLng(latlng);
            else miniMarker = L.marker(latlng, {draggable:true}).addTo(miniMap);
            miniMap.invalidateSize();
        }
    }, 400);
}
</script>
@endpush
