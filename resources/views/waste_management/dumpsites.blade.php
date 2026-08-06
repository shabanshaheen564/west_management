@extends('layouts.skeleton')
@section('title', __('Dumpsites'))
@section('page-title', __('Dumpsites Management'))

@section('content')

<div class="page-header">
    <div class="page-header-icon" style="background:#e07b39;"><i class="fas fa-industry"></i></div>
    <div>
        <h4>{{ __('Dumpsites Management') }}</h4>
        <p>{{ __('Manage waste disposal sites') }}</p>
    </div>
    <div class="ms-auto d-flex gap-2">
        <button class="btn btn-sm btn-primary" style="background:#e07b39;border-color:#e07b39;"
            data-bs-toggle="modal" data-bs-target="#dumpsiteModal" onclick="resetDumpsiteForm()">
            <i class="fas fa-plus me-1"></i>{{ __('Add Dumpsite') }}
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-3">
    @foreach([
        ['label'=>__('Total'),'value'=>$stats['total'],'color'=>'#555'],
        ['label'=>__('Active'),'value'=>$stats['active'],'color'=>'#2d8a4e'],
        ['label'=>__('Avg Fill'),'value'=>$stats['avg_fill'].'%','color'=>'#ffc107'],
        ['label'=>__('Full'),'value'=>$stats['full'],'color'=>'#dc3545'],
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
        <div class="col-12 col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control" placeholder="{{ __('Name, code...') }}" value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">{{ __('All Status') }}</option>
                @foreach(['active','inactive','full','maintenance'] as $s)
                <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="type" class="form-select form-select-sm">
                <option value="">{{ __('All Types') }}</option>
                @foreach(['landfill','transfer_station','recycling_center','composting'] as $t)
                <option value="{{ $t }}" {{ request('type')==$t?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-grow-1">{{ __('Filter') }}</button>
            <a href="{{ route('dumpsites.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Reset') }}</a>
        </div>
    </form>
</div></div>

<div class="row g-3">
    @forelse($dumpsites as $d)
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h6 class="mb-0 fw-bold">{{ app()->getLocale()==='ar' && $d->name_ar ? $d->name_ar : $d->name }}</h6>
                        <small class="text-muted">{{ $d->code }}</small>
                    </div>
                    <span class="badge bg-{{ $d->status_color }}">{{ ucfirst($d->status) }}</span>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">{{ __('Fill Level') }}</small>
                        <small class="fw-bold text-{{ $d->fill_color }}">{{ $d->fill_percentage }}%</small>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-{{ $d->fill_color }}" style="width:{{ $d->fill_percentage }}%"></div>
                    </div>
                </div>
                <div class="row g-2 mb-3" style="font-size:.8rem;">
                    <div class="col-6">
                        <div class="text-muted">{{ __('Type') }}</div>
                        <div class="fw-600">{{ ucfirst(str_replace('_',' ',$d->type)) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">{{ __('Capacity') }}</div>
                        <div class="fw-600">{{ number_format($d->total_capacity) }} t</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">{{ __('Current Fill') }}</div>
                        <div class="fw-600">{{ number_format($d->current_fill) }} t</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">{{ __('Hours') }}</div>
                        <div class="fw-600">
                            @if($d->opening_time && $d->closing_time)
                            {{ $d->opening_time }} – {{ $d->closing_time }}
                            @else —@endif
                        </div>
                    </div>
                </div>
                @if($d->address)
                <div style="font-size:.78rem;color:#888;margin-bottom:.75rem;">
                    <i class="fas fa-map-marker-alt me-1"></i>{{ $d->address }}
                </div>
                @endif
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick='editDumpsite(@json($d))'>
                        <i class="fas fa-edit me-1"></i>{{ __('Edit') }}
                    </button>
                    <form id="dsdel-{{ $d->id }}" action="{{ route('dumpsites.destroy',$d) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('dsdel-{{ $d->id }}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card text-center py-5 text-muted">
            <div><i class="fas fa-industry" style="font-size:2.5rem;opacity:.3;"></i></div>
            <div class="mt-2">{{ __('No dumpsites found') }}</div>
        </div>
    </div>
    @endforelse
</div>

@if($dumpsites->hasPages())
<div class="mt-3">{{ $dumpsites->links() }}</div>
@endif

{{-- Modal --}}
<div class="modal fade" id="dumpsiteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#e07b39;color:#fff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="fas fa-industry me-2"></i><span id="dsModalTitle">{{ __('Add Dumpsite') }}</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="dumpsiteForm" method="POST" action="{{ route('dumpsites.store') }}">
                @csrf <input type="hidden" name="_method" id="dsFormMethod" value="POST">
                <div class="modal-body"><div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Name (EN)') }} *</label>
                        <input type="text" name="name" id="dsf_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Name (AR)') }}</label>
                        <input type="text" name="name_ar" id="dsf_name_ar" class="form-control" dir="rtl">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Type') }} *</label>
                        <select name="type" id="dsf_type" class="form-select" required>
                            @foreach(['landfill','transfer_station','recycling_center','composting'] as $t)
                            <option value="{{ $t }}">{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Status') }} *</label>
                        <select name="status" id="dsf_status" class="form-select" required>
                            @foreach(['active','inactive','full','maintenance'] as $s)
                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Total Capacity (tons)') }} *</label>
                        <input type="number" name="total_capacity" id="dsf_total_capacity" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Current Fill (tons)') }}</label>
                        <input type="number" name="current_fill" id="dsf_current_fill" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Latitude') }} *</label>
                        <input type="number" name="latitude" id="dsf_latitude" class="form-control" step="any" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Longitude') }} *</label>
                        <input type="number" name="longitude" id="dsf_longitude" class="form-control" step="any" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Opening Time') }}</label>
                        <input type="time" name="opening_time" id="dsf_opening_time" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Closing Time') }}</label>
                        <input type="time" name="closing_time" id="dsf_closing_time" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Address') }}</label>
                        <input type="text" name="address" id="dsf_address" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" id="dsf_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="background:#e07b39;border-color:#e07b39;">
                        <i class="fas fa-save me-1"></i>{{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function resetDumpsiteForm() {
    document.getElementById('dsModalTitle').textContent = '{{ __("Add Dumpsite") }}';
    document.getElementById('dumpsiteForm').action = '{{ route("dumpsites.store") }}';
    document.getElementById('dsFormMethod').value = 'POST';
    ['name','name_ar','total_capacity','latitude','longitude','address','notes'].forEach(f => {
        const el = document.getElementById('dsf_'+f); if(el) el.value = f==='current_fill'?0:'';
    });
    document.getElementById('dsf_current_fill').value = 0;
    document.getElementById('dsf_type').value = 'landfill';
    document.getElementById('dsf_status').value = 'active';
    ['opening_time','closing_time'].forEach(f => { const el = document.getElementById('dsf_'+f); if(el) el.value=''; });
}

function editDumpsite(d) {
    document.getElementById('dsModalTitle').textContent = '{{ __("Edit Dumpsite") }}';
    document.getElementById('dumpsiteForm').action = `/dumpsites/${d.id}`;
    document.getElementById('dsFormMethod').value = 'PUT';
    ['name','name_ar','total_capacity','current_fill','latitude','longitude','address','notes'].forEach(f => {
        const el = document.getElementById('dsf_'+f); if(el) el.value = d[f]||'';
    });
    document.getElementById('dsf_type').value   = d.type;
    document.getElementById('dsf_status').value = d.status;
    document.getElementById('dsf_opening_time').value = d.opening_time||'';
    document.getElementById('dsf_closing_time').value = d.closing_time||'';
    new bootstrap.Modal(document.getElementById('dumpsiteModal')).show();
}
</script>
@endpush
