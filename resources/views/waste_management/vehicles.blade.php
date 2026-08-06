@extends('layouts.skeleton')
@section('title', __('Vehicles'))
@section('page-title', __('Vehicle Fleet'))

@section('content')
<div class="page-header">
    <div class="page-header-icon" style="background:#1a6b9a;"><i class="fas fa-truck"></i></div>
    <div><h4>{{ __('Vehicles Management') }}</h4><p>{{ __('Manage fleet vehicles and maintenance') }}</p></div>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('vehicles.export') }}" class="btn btn-sm btn-outline-success"><i class="fas fa-download me-1"></i>{{ __('Export') }}</a>
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importVModal"><i class="fas fa-upload me-1"></i>{{ __('Import') }}</button>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#vehicleModal" onclick="resetVehicleForm()"><i class="fas fa-plus me-1"></i>{{ __('Add Vehicle') }}</button>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach([
        ['label'=>__('Total'),'value'=>$stats['total'],'color'=>'#555'],
        ['label'=>__('Active'),'value'=>$stats['active'],'color'=>'#2d8a4e'],
        ['label'=>__('On Route'),'value'=>$stats['on_route'],'color'=>'#1a6b9a'],
        ['label'=>__('Maintenance'),'value'=>$stats['maintenance'],'color'=>'#ffc107'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.8rem;font-weight:800;color:{{ $s['color'] }};">{{ $s['value'] }}</div>
            <small class="text-muted">{{ $s['label'] }}</small>
        </div>
    </div>
    @endforeach
</div>

<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-12 col-md-5"><div class="input-group input-group-sm"><span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="search" class="form-control" placeholder="{{ __('Plate, model, brand...') }}" value="{{ request('search') }}"></div></div>
        <div class="col-6 col-md-2"><select name="status" class="form-select form-select-sm">
            <option value="">{{ __('All Status') }}</option>
            @foreach(['active','inactive','maintenance','on_route'] as $s)<option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
        </select></div>
        <div class="col-6 col-md-2"><select name="type" class="form-select form-select-sm">
            <option value="">{{ __('All Types') }}</option>
            @foreach(['truck','mini_truck','compactor','tipper','suction'] as $t)<option value="{{ $t }}" {{ request('type')==$t?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>@endforeach
        </select></div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-grow-1">{{ __('Filter') }}</button>
            <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Reset') }}</a>
        </div>
    </form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0" dir="ltr">
        <thead><tr>
            <th class="ps-3">{{ __('Plate / Brand') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Capacity') }}</th>
            <th>{{ __('Driver') }}</th>
            <th>{{ __('Fuel') }}</th>
            <th>{{ __('Next Maintenance') }}</th>
            <th>{{ __('Insurance Expiry') }}</th>
            <th>{{ __('Status') }}</th>
            <th class="text-center">{{ __('Actions') }}</th>
        </tr></thead>
        <tbody>
            @forelse($vehicles as $v)
            <tr>
                <td class="ps-3">
                    <div class="fw-bold">{{ $v->plate_number }}</div>
                    <small class="text-muted">{{ $v->brand }} {{ $v->model }} ({{ $v->year }})</small>
                </td>
                <td><span class="badge bg-secondary-subtle text-secondary border">{{ ucfirst(str_replace('_',' ',$v->type)) }}</span></td>
                <td><small>{{ $v->capacity }} t</small></td>
                {{-- جديد --}}
            <td>{!! $v->driver?->name ?? '<span class="text-muted">—</span>' !!}</td>
                <td>
                    @if($v->fuel_level !== null)
                    <div class="d-flex align-items-center gap-1">
                        <div class="fill-indicator" style="width:50px;height:6px;">
                            <div class="fill-bar bg-{{ $v->fuel_level>50?'success':($v->fuel_level>20?'warning':'danger') }}" style="width:{{ $v->fuel_level }}%"></div>
                        </div>
                        <small>{{ $v->fuel_level }}%</small>
                    </div>
                    @else<small class="text-muted">—</small>@endif
                </td>
                <td>
                    @if($v->next_maintenance)
                    @php $days = now()->diffInDays($v->next_maintenance, false); @endphp
                    <small class="text-{{ $days<0?'danger':($days<7?'warning':'muted') }}">{{ $v->next_maintenance->format('d M Y') }}</small>
                    @else<small class="text-muted">—</small>@endif
                </td>
                <td>
                    @if($v->insurance_expiry)
                    @php $iDays = now()->diffInDays($v->insurance_expiry, false); @endphp
                    <small class="text-{{ $iDays<0?'danger':($iDays<30?'warning':'muted') }}">{{ $v->insurance_expiry->format('d M Y') }}</small>
                    @else<small class="text-muted">—</small>@endif
                </td>
                <td><span class="badge bg-{{ $v->status_color }}-subtle text-{{ $v->status_color }} border">{{ ucfirst(str_replace('_',' ',$v->status)) }}</span></td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <button class="btn btn-icon btn-sm btn-outline-primary" onclick='editVehicle(@json($v))' title="{{ __('Edit') }}">
                            <i class="fas fa-edit" style="font-size:.7rem;"></i>
                        </button>
                        <form id="vdel-{{ $v->id }}" action="{{ route('vehicles.destroy',$v) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-icon btn-sm btn-outline-danger" onclick="confirmDelete('vdel-{{ $v->id }}')">
                                <i class="fas fa-trash" style="font-size:.7rem;"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-truck" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>{{ __('No vehicles found') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($vehicles->hasPages())<div class="px-3 py-2 border-top">{{ $vehicles->links() }}</div>@endif
</div></div>

{{-- Vehicle Modal --}}
<div class="modal fade" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#1a6b9a;color:#fff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="fas fa-truck me-2"></i><span id="vModalTitle">{{ __('Add Vehicle') }}</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="vehicleForm" method="POST" action="{{ route('vehicles.store') }}">
                @csrf <input type="hidden" name="_method" id="vFormMethod" value="POST">
                <div class="modal-body"><div class="row g-3">
                    <div class="col-md-4"><label class="form-label">{{ __('Plate Number') }} *</label><input type="text" name="plate_number" id="vf_plate_number" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">{{ __('Brand') }} *</label><input type="text" name="brand" id="vf_brand" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">{{ __('Model') }} *</label><input type="text" name="model" id="vf_model" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">{{ __('Year') }} *</label><input type="number" name="year" id="vf_year" class="form-control" min="2000" max="{{ date('Y')+1 }}" required></div>
                    <div class="col-md-3"><label class="form-label">{{ __('Type') }} *</label>
                        <select name="type" id="vf_type" class="form-select" required>
                            @foreach(['truck','mini_truck','compactor','tipper','suction'] as $t)<option value="{{ $t }}">{{ ucfirst(str_replace('_',' ',$t)) }}</option>@endforeach
                        </select></div>
                    <div class="col-md-3"><label class="form-label">{{ __('Capacity (tons)') }} *</label><input type="number" name="capacity" id="vf_capacity" class="form-control" step="0.1" required></div>
                    <div class="col-md-3"><label class="form-label">{{ __('Fuel Type') }}</label><input type="text" name="fuel_type" id="vf_fuel_type" class="form-control" value="diesel"></div>
                    <div class="col-md-4"><label class="form-label">{{ __('Status') }} *</label>
                        <select name="status" id="vf_status" class="form-select" required>
                            @foreach(['active','inactive','maintenance','on_route'] as $s)<option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
                        </select></div>
                    <div class="col-md-4"><label class="form-label">{{ __('Last Maintenance') }}</label><input type="date" name="last_maintenance" id="vf_last_maintenance" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">{{ __('Next Maintenance') }}</label><input type="date" name="next_maintenance" id="vf_next_maintenance" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">{{ __('Insurance Number') }}</label><input type="text" name="insurance_number" id="vf_insurance_number" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">{{ __('Insurance Expiry') }}</label><input type="date" name="insurance_expiry" id="vf_insurance_expiry" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">{{ __('Registration Number') }}</label><input type="text" name="registration_number" id="vf_registration_number" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">{{ __('Registration Expiry') }}</label><input type="date" name="registration_expiry" id="vf_registration_expiry" class="form-control"></div>
                    <div class="col-12"><label class="form-label">{{ __('Notes') }}</label><textarea name="notes" id="vf_notes" class="form-control" rows="2"></textarea></div>
                </div></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="background:#1a6b9a;border-color:#1a6b9a;"><i class="fas fa-save me-1"></i>{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importVModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:16px;border:none;">
        <div class="modal-header" style="background:#198754;color:#fff;border-radius:16px 16px 0 0;">
            <h5 class="modal-title"><i class="fas fa-file-excel me-2"></i>{{ __('Import Vehicles') }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="{{ route('vehicles.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="alert alert-info" style="font-size:.82rem;"><i class="fas fa-info-circle me-1"></i>{{ __('Columns: plate_number, brand, model, year, type, capacity, status, fuel_type') }}</div>
                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-upload me-1"></i>{{ __('Import') }}</button>
            </div>
        </form>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
function resetVehicleForm() {
    document.getElementById('vModalTitle').textContent = '{{ __("Add Vehicle") }}';
    document.getElementById('vehicleForm').action = '{{ route("vehicles.store") }}';
    document.getElementById('vFormMethod').value = 'POST';
    ['plate_number','brand','model','year','capacity','fuel_type','insurance_number','registration_number','notes'].forEach(f => {
        const el = document.getElementById('vf_'+f); if(el) el.value = f==='fuel_type'?'diesel':'';
    });
    ['last_maintenance','next_maintenance','insurance_expiry','registration_expiry'].forEach(f => {
        const el = document.getElementById('vf_'+f); if(el) el.value = '';
    });
    document.getElementById('vf_type').value   = 'truck';
    document.getElementById('vf_status').value = 'active';
}

function editVehicle(v) {
    document.getElementById('vModalTitle').textContent = '{{ __("Edit Vehicle") }}';
    document.getElementById('vehicleForm').action = `/vehicles/${v.id}`;
    document.getElementById('vFormMethod').value = 'PUT';
    const fields = ['plate_number','brand','model','year','capacity','fuel_type','insurance_number','registration_number','notes'];
    fields.forEach(f => { const el=document.getElementById('vf_'+f); if(el) el.value=v[f]||''; });
    const dates = ['last_maintenance','next_maintenance','insurance_expiry','registration_expiry'];
    dates.forEach(f => { const el=document.getElementById('vf_'+f); if(el) el.value=(v[f]||'').slice(0,10); });
    document.getElementById('vf_type').value   = v.type;
    document.getElementById('vf_status').value = v.status;
    new bootstrap.Modal(document.getElementById('vehicleModal')).show();
}
</script>
@endpush
