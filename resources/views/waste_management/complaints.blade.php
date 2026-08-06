@extends('layouts.skeleton')
@section('title', __('Complaints'))
@section('page-title', __('Complaints Management'))

@section('content')

<div class="page-header">
    <div class="page-header-icon" style="background:#dc3545;"><i class="fas fa-exclamation-circle"></i></div>
    <div>
        <h4>{{ __('Complaints') }}</h4>
        <p>{{ __('Track and resolve citizen complaints') }}</p>
    </div>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('complaints.export') }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-download me-1"></i>{{ __('Export') }}
        </a>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#complaintModal" onclick="resetComplaintForm()">
            <i class="fas fa-plus me-1"></i>{{ __('Add Complaint') }}
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-3">
    @foreach([
        ['label'=>__('Total'),'value'=>$stats['total'],'color'=>'#555','icon'=>'fa-list'],
        ['label'=>__('Open'),'value'=>$stats['open'],'color'=>'#dc3545','icon'=>'fa-door-open'],
        ['label'=>__('Urgent'),'value'=>$stats['urgent'],'color'=>'#e07b39','icon'=>'fa-fire'],
        ['label'=>__('Resolved'),'value'=>$stats['resolved'],'color'=>'#2d8a4e','icon'=>'fa-check-circle'],
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
        <form method="GET" class="row g-2">
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="{{ __('Ticket, name, subject...') }}" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('All Status') }}</option>
                    @foreach(['open','in_progress','resolved','closed','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="priority" class="form-select form-select-sm">
                    <option value="">{{ __('All Priority') }}</option>
                    @foreach(['low','medium','high','urgent'] as $p)
                    <option value="{{ $p }}" {{ request('priority')==$p?'selected':'' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="category" class="form-select form-select-sm">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach(['missed_collection','damaged_container','illegal_dumping','odor','noise','hazardous_waste','other'] as $c)
                    <option value="{{ $c }}" {{ request('category')==$c?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$c)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">{{ __('Filter') }}</button>
                <a href="{{ route('complaints.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Reset') }}</a>
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
                        <th class="ps-3">{{ __('Ticket') }}</th>
                        <th>{{ __('Complainant') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Subject') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Assigned To') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $c)
                    <tr>
                        <td class="ps-3"><code class="text-primary">{{ $c->ticket_number }}</code></td>
                        <td>
                            <div class="fw-bold" style="font-size:.85rem;">{{ $c->complainant_name }}</div>
                            @if($c->complainant_phone)<small class="text-muted">{{ $c->complainant_phone }}</small>@endif
                        </td>
                        <td><small class="text-muted">{{ ucfirst(str_replace('_',' ',$c->category)) }}</small></td>
                        <td><small>{{ Str::limit($c->subject, 40) }}</small></td>
                        <td><span class="badge bg-{{ $c->priority_color }}">{{ ucfirst($c->priority) }}</span></td>
                        <td><span class="badge bg-{{ $c->status_color }}-subtle text-{{ $c->status_color }} border">{{ ucfirst(str_replace('_',' ',$c->status)) }}</span></td>
                        <td>{{ $c->assignedTo?->name ?? '—' }}</td>
                        <td><small>{{ $c->created_at->format('d M Y') }}</small></td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-icon btn-sm btn-outline-primary" onclick='updateComplaint(@json($c))' title="{{ __('Update') }}">
                                    <i class="fas fa-edit" style="font-size:.7rem;"></i>
                                </button>
                                @if($c->latitude && $c->longitude)
                                <a href="{{ route('map') }}" class="btn btn-icon btn-sm btn-outline-info" title="{{ __('View on Map') }}">
                                    <i class="fas fa-map-marker-alt" style="font-size:.7rem;"></i>
                                </a>
                                @endif
                                <form id="cdel-{{ $c->id }}" action="{{ route('complaints.destroy',$c) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger" onclick="confirmDelete('cdel-{{ $c->id }}')">
                                        <i class="fas fa-trash" style="font-size:.7rem;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-5 text-muted">
                        <i class="fas fa-smile" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                        {{ __('No complaints found') }}
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($complaints->hasPages())
        <div class="px-3 py-2 border-top">{{ $complaints->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="complaintModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#dc3545;color:#fff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="fas fa-exclamation-circle me-2"></i>{{ __('New Complaint') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('complaints.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">{{ __('Complainant Name') }} *</label>
                            <input type="text" name="complainant_name" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">{{ __('Phone') }}</label>
                            <input type="text" name="complainant_phone" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="complainant_email" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Category') }} *</label>
                            <select name="category" class="form-select" required>
                                @foreach(['missed_collection','damaged_container','illegal_dumping','odor','noise','hazardous_waste','other'] as $c)
                                <option value="{{ $c }}">{{ ucfirst(str_replace('_',' ',$c)) }}</option>
                                @endforeach
                            </select></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Priority') }} *</label>
                            <select name="priority" class="form-select" required>
                                @foreach(['low','medium','high','urgent'] as $p)
                                <option value="{{ $p }}" {{ $p==='medium'?'selected':'' }}>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select></div>
                        <div class="col-12"><label class="form-label">{{ __('Subject') }} *</label>
                            <input type="text" name="subject" class="form-control" required></div>
                        <div class="col-12"><label class="form-label">{{ __('Description') }} *</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea></div>
                        <div class="col-12"><label class="form-label">{{ __('Address / Location') }}</label>
                            <input type="text" name="address" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Latitude') }}</label>
                            <input type="number" name="latitude" class="form-control" step="any"></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Longitude') }}</label>
                            <input type="number" name="longitude" class="form-control" step="any"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-save me-1"></i>{{ __('Submit Complaint') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Update Modal --}}
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#1a6b9a;color:#fff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>{{ __('Update Complaint') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="uf_status" class="form-select">
                                @foreach(['open','in_progress','resolved','closed','rejected'] as $s)
                                <option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                                @endforeach
                            </select></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Priority') }}</label>
                            <select name="priority" id="uf_priority" class="form-select">
                                @foreach(['low','medium','high','urgent'] as $p)
                                <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                                @endforeach
                            </select></div>
                        <div class="col-12"><label class="form-label">{{ __('Assign To') }}</label>
                            <select name="assigned_to" id="uf_assigned_to" class="form-select">
                                <option value="">{{ __('Unassigned') }}</option>
                                @foreach($staff as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select></div>
                        <div class="col-12"><label class="form-label">{{ __('Resolution Notes') }}</label>
                            <textarea name="resolution_notes" id="uf_resolution_notes" class="form-control" rows="3"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function resetComplaintForm() {}

function updateComplaint(c) {
    document.getElementById('updateForm').action = `/complaints/${c.id}`;
    document.getElementById('uf_status').value = c.status;
    document.getElementById('uf_priority').value = c.priority;
    document.getElementById('uf_assigned_to').value = c.assigned_to || '';
    document.getElementById('uf_resolution_notes').value = c.resolution_notes || '';
    new bootstrap.Modal(document.getElementById('updateModal')).show();
}
</script>
@endpush
