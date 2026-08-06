@extends('layouts.skeleton')
@section('title', __('Users'))
@section('page-title', __('Users Management'))

@section('content')

<div class="page-header">
    <div class="page-header-icon"><i class="fas fa-users"></i></div>
    <div><h4>{{ __('Users') }}</h4><p>{{ __('Manage system users and access') }}</p></div>
    <div class="ms-auto d-flex gap-2">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetUserForm()">
            <i class="fas fa-plus me-1"></i>{{ __('Add User') }}
        </button>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach([['label'=>__('Total'),'value'=>$stats['total'],'color'=>'#555'],
              ['label'=>__('Active'),'value'=>$stats['active'],'color'=>'#2d8a4e'],
              ['label'=>__('Inactive'),'value'=>$stats['inactive'],'color'=>'#dc3545']] as $s)
    <div class="col-4">
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
                <input type="text" name="search" class="form-control" placeholder="{{ __('Name, email...') }}" value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="role" class="form-select form-select-sm">
                <option value="">{{ __('All Roles') }}</option>
                @foreach($roles as $r)<option value="{{ $r->name }}" {{ request('role')==$r->name?'selected':'' }}>{{ ucfirst($r->name) }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-grow-1">{{ __('Filter') }}</button>
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Reset') }}</a>
        </div>
    </form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr>
            <th class="ps-3">{{ __('User') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('Role') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Joined') }}</th>
            <th class="text-center">{{ __('Actions') }}</th>
        </tr></thead>
        <tbody>
            @forelse($users as $u)
            <tr>
                <td class="ps-3">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $u->avatar_url }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="">
                        <div>
                            <div class="fw-bold" style="font-size:.85rem;">{{ $u->name }}</div>
                        </div>
                    </div>
                </td>
                <td><small>{{ $u->email }}</small></td>
                <td><small>{{ $u->phone ?? '—' }}</small></td>
                <td>
                    @foreach($u->roles as $role)
                    <span class="badge bg-primary-subtle text-primary border" style="font-size:.72rem;">{{ ucfirst($role->name) }}</span>
                    @endforeach
                </td>
                <td>
                    <span class="badge {{ $u->is_active ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle' }} border">
                        {{ $u->is_active ? __('Active') : __('Inactive') }}
                    </span>
                </td>
                <td><small>{{ $u->created_at->format('d M Y') }}</small></td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <button class="btn btn-icon btn-sm btn-outline-primary" onclick='editUser(@json($u->load("roles")))' title="{{ __('Edit') }}">
                            <i class="fas fa-edit" style="font-size:.7rem;"></i>
                        </button>
                        @if($u->id !== auth()->id())
                        <form id="udel-{{ $u->id }}" action="{{ route('users.destroy',$u) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-icon btn-sm btn-outline-danger" onclick="confirmDelete('udel-{{ $u->id }}')">
                                <i class="fas fa-trash" style="font-size:.7rem;"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-5 text-muted">{{ __('No users found') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($users->hasPages())<div class="px-3 py-2 border-top">{{ $users->links() }}</div>@endif
</div></div>

{{-- User Modal --}}
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:var(--primary);color:#fff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i><span id="uModalTitle">{{ __('Add User') }}</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm" method="POST" action="{{ route('users.store') }}">
                @csrf <input type="hidden" name="_method" id="uFormMethod" value="POST">
                <div class="modal-body"><div class="row g-3">
                    <div class="col-12"><label class="form-label">{{ __('Name') }} *</label><input type="text" name="name" id="uf2_name" class="form-control" required></div>
                    <div class="col-12"><label class="form-label">{{ __('Email') }} *</label><input type="email" name="email" id="uf2_email" class="form-control" required></div>
                    <div class="col-12"><label class="form-label">{{ __('Phone') }}</label><input type="text" name="phone" id="uf2_phone" class="form-control"></div>
                    <div class="col-12"><label class="form-label">{{ __('Role') }} *</label>
                        <select name="role" id="uf2_role" class="form-select" required>
                            @foreach($roles as $r)<option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>@endforeach
                        </select></div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Password') }} <span id="pwRequired">*</span></label>
                        <input type="password" name="password" id="uf2_password" class="form-control">
                    </div>
                    <div class="col-12"><label class="form-label">{{ __('Confirm Password') }}</label><input type="password" name="password_confirmation" class="form-control"></div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="uf2_active" value="1" checked>
                            <label class="form-check-label" for="uf2_active">{{ __('Active') }}</label>
                        </div>
                    </div>
                </div></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function resetUserForm() {
    document.getElementById('uModalTitle').textContent = '{{ __("Add User") }}';
    document.getElementById('userForm').action = '{{ route("users.store") }}';
    document.getElementById('uFormMethod').value = 'POST';
    document.getElementById('pwRequired').style.display = '';
    ['name','email','phone'].forEach(f => { const el=document.getElementById('uf2_'+f); if(el) el.value=''; });
    document.getElementById('uf2_password').required = true;
    document.getElementById('uf2_active').checked = true;
}

function editUser(u) {
    document.getElementById('uModalTitle').textContent = '{{ __("Edit User") }}';
    document.getElementById('userForm').action = `/users/${u.id}`;
    document.getElementById('uFormMethod').value = 'PUT';
    document.getElementById('pwRequired').style.display = 'none';
    document.getElementById('uf2_name').value  = u.name;
    document.getElementById('uf2_email').value = u.email;
    document.getElementById('uf2_phone').value = u.phone||'';
    document.getElementById('uf2_role').value  = u.roles[0]?.name||'user';
    document.getElementById('uf2_password').required = false;
    document.getElementById('uf2_active').checked = u.is_active;
    new bootstrap.Modal(document.getElementById('userModal')).show();
}
</script>
@endpush
