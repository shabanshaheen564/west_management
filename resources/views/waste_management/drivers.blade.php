@extends('layouts.skeleton')
@section('title', __('Drivers'))
@section('page-title', __('Drivers Management'))

@section('content')

<div class="page-header">
    <div class="page-header-icon" style="background:#7b3fa0;"><i class="fas fa-id-badge"></i></div>
    <div>
        <h4>{{ __('Drivers Management') }}</h4>
        <p>{{ __('Manage all drivers and licenses') }}</p>
    </div>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('drivers.export') }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-download me-1"></i>{{ __('Export') }}
        </a>
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importDModal">
            <i class="fas fa-upload me-1"></i>{{ __('Import') }}
        </button>
        <button class="btn btn-sm btn-primary" style="background:#7b3fa0;border-color:#7b3fa0;"
            data-bs-toggle="modal" data-bs-target="#driverModal" onclick="resetDriverForm()">
            <i class="fas fa-plus me-1"></i>{{ __('Add Driver') }}
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-3">
    @foreach([
        ['label'=>__('Total'),'value'=>$stats['total'],'color'=>'#555'],
        ['label'=>__('Active'),'value'=>$stats['active'],'color'=>'#2d8a4e'],
        ['label'=>__('On Leave'),'value'=>$stats['on_leave'],'color'=>'#ffc107'],
        ['label'=>__('Suspended'),'value'=>$stats['suspended'],'color'=>'#dc3545'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.8rem;font-weight:800;color:{{ $s['color'] }};">{{ $s['value'] }}</div>
            <small class="text-muted">{{ $s['label'] }}</small>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-12 col-md-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control" placeholder="{{ __('Name, employee ID, phone...') }}" value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">{{ __('All Status') }}</option>
                @foreach(['active','inactive','on_leave','suspended'] as $s)
                <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-grow-1">{{ __('Filter') }}</button>
            <a href="{{ route('drivers.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Reset') }}</a>
        </div>
    </form>
</div></div>

{{-- Table --}}
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr>
            <th class="ps-3">{{ __('Employee') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('License') }}</th>
            <th>{{ __('License Expiry') }}</th>
            <th>{{ __('Hire Date') }}</th>
            <th>{{ __('Rating') }}</th>
            <th>{{ __('Trips') }}</th>
            <th>{{ __('Status') }}</th>
            <th class="text-center">{{ __('Actions') }}</th>
        </tr></thead>
        <tbody>
@forelse($drivers as $d)
<tr>
    <td class="ps-3">
        <div class="fw-bold">{{ $d->name ?? '-' }}</div>
        <small class="text-muted">{{ $d->employee_id ?? '-' }}</small>
    </td>

    <td><small>{{ $d->phone ?? '-' }}</small></td>

    <td>
        <div>{{ $d->license_number ?? '-' }}</div>
        <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.68rem;">
            Class {{ $d->license_class ?? '-' }}
        </span>
    </td>

    <td>
        @php
            $lDays = $d->license_expiry ? now()->diffInDays($d->license_expiry, false) : null;
        @endphp

        <small class="{{ $lDays !== null && $lDays < 0 ? 'text-danger' : ($lDays !== null && $lDays < 30 ? 'text-warning' : 'text-muted') }}">
            {{ $d->license_expiry ? $d->license_expiry->format('d M Y') : '-' }}

            @if($lDays !== null && $lDays < 0)
                <i class="fas fa-exclamation-triangle ms-1"></i>
            @endif
        </small>
    </td>

    <td>
        <small>
            {{ $d->hire_date ? $d->hire_date->format('d M Y') : '-' }}
        </small>
    </td>

    <td>
        <div class="d-flex align-items-center gap-1">
            <i class="fas fa-star text-warning" style="font-size:.75rem;"></i>
            <small class="fw-bold">{{ number_format($d->rating ?? 0, 1) }}</small>
        </div>
    </td>

    <td>
        <span class="badge bg-primary-subtle text-primary border">
            {{ number_format($d->total_trips ?? 0) }}
        </span>
    </td>

    <td>
        <span class="badge bg-{{ $d->status_color }}-subtle text-{{ $d->status_color }} border">
            {{ ucfirst(str_replace('_',' ',$d->status ?? '')) }}
        </span>
    </td>

    <td class="text-center">
        <div class="d-flex gap-1 justify-content-center">
            <button class="btn btn-icon btn-sm btn-outline-primary"
                    onclick='editDriver(@json($d))'>
                <i class="fas fa-edit" style="font-size:.7rem;"></i>
            </button>

            <form action="{{ route('drivers.destroy',$d) }}" method="POST">
                @csrf @method('DELETE')
                <button type="button"
                        class="btn btn-icon btn-sm btn-outline-danger"
                        onclick="confirmDelete(this.form)">
                    <i class="fas fa-trash" style="font-size:.7rem;"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-5 text-muted">
        No drivers found
    </td>
</tr>
@endforelse
</tbody>
    </table>
</div>
@if($drivers->hasPages())<div class="px-3 py-2 border-top">{{ $drivers->links() }}</div>@endif
</div></div>

{{-- Driver Modal --}}
<div class="modal fade" id="driverModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#7b3fa0;color:#fff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="fas fa-id-badge me-2"></i><span id="dModalTitle">{{ __('Add Driver') }}</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="driverForm" method="POST" action="{{ route('drivers.store') }}">
                @csrf <input type="hidden" name="_method" id="dFormMethod" value="POST">
                <div class="modal-body"><div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Employee ID') }} *</label>
                        <input type="text" name="employee_id" id="df_employee_id" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Name (EN)') }} *</label>
                        <input type="text" name="name" id="df_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Name (AR)') }}</label>
                        <input type="text" name="name_ar" id="df_name_ar" class="form-control" dir="rtl">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Phone') }} *</label>
                        <input type="text" name="phone" id="df_phone" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="email" name="email" id="df_email" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('National ID') }}</label>
                        <input type="text" name="national_id" id="df_national_id" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('License Number') }} *</label>
                        <input type="text" name="license_number" id="df_license_number" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('License Class') }} *</label>
                        <select name="license_class" id="df_license_class" class="form-select" required>
                            @foreach(['A','B','C','D'] as $lc)
                            <option value="{{ $lc }}">Class {{ $lc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('License Expiry') }} *</label>
                        <input type="date" name="license_expiry" id="df_license_expiry" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Hire Date') }} *</label>
                        <input type="date" name="hire_date" id="df_hire_date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Status') }} *</label>
                        <select name="status" id="df_status" class="form-select" required>
                            @foreach(['active','inactive','on_leave','suspended'] as $s)
                            <option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Address') }}</label>
                        <input type="text" name="address" id="df_address" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" id="df_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="background:#7b3fa0;border-color:#7b3fa0;">
                        <i class="fas fa-save me-1"></i>{{ __('Save Driver') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importDModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:16px;border:none;">
        <div class="modal-header" style="background:#198754;color:#fff;border-radius:16px 16px 0 0;">
            <h5 class="modal-title"><i class="fas fa-file-excel me-2"></i>{{ __('Import Drivers') }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="{{ route('drivers.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="alert alert-info" style="font-size:.82rem;">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ __('Columns: employee_id, name, phone, license_number, license_class, license_expiry, hire_date, status') }}
                </div>
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
function resetDriverForm() {
    document.getElementById('dModalTitle').textContent = '{{ __("Add Driver") }}';
    document.getElementById('driverForm').action = '{{ route("drivers.store") }}';
    document.getElementById('dFormMethod').value = 'POST';
    ['employee_id','name','name_ar','phone','email','national_id','license_number','address','notes'].forEach(f => {
        const el = document.getElementById('df_'+f); if(el) el.value = '';
    });
    ['license_expiry','hire_date'].forEach(f => {
        const el = document.getElementById('df_'+f); if(el) el.value = '';
    });
    document.getElementById('df_license_class').value = 'C';
    document.getElementById('df_status').value = 'active';
}

function editDriver(d) {
    document.getElementById('dModalTitle').textContent = '{{ __("Edit Driver") }}';
    document.getElementById('driverForm').action = `/drivers/${d.id}`;
    document.getElementById('dFormMethod').value = 'PUT';
    ['employee_id','name','name_ar','phone','email','national_id','license_number','address','notes'].forEach(f => {
        const el = document.getElementById('df_'+f);
        if(el) el.value = d[f] || '';
    });
    document.getElementById('df_license_expiry').value = (d.license_expiry||'').slice(0,10);
    document.getElementById('df_hire_date').value       = (d.hire_date||'').slice(0,10);
    document.getElementById('df_license_class').value  = d.license_class;
    document.getElementById('df_status').value          = d.status;
    new bootstrap.Modal(document.getElementById('driverModal')).show();
}
</script>
@endpush
