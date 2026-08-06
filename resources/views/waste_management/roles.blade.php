@extends('layouts.skeleton')
@section('title', __('Roles & Permissions'))
@section('page-title', __('Roles & Permissions'))

@section('content')

<div class="page-header">
    <div class="page-header-icon" style="background:#dc3545;"><i class="fas fa-shield-alt"></i></div>
    <div><h4>{{ __('Roles & Permissions') }}</h4><p>{{ __('Manage access control and permissions') }}</p></div>
    <div class="ms-auto">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#roleModal">
            <i class="fas fa-plus me-1"></i>{{ __('Add Role') }}
        </button>
    </div>
</div>

<div class="row g-3">
    @foreach($roles as $role)
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <div style="width:36px;height:36px;background:{{ match($role->name){'admin'=>'#dc3545','supervisor'=>'#1a6b9a','driver'=>'#7b3fa0',default=>'#2d8a4e'} }};border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.9rem;">
                    <i class="fas {{ match($role->name){'admin'=>'fa-crown','supervisor'=>'fa-user-tie','driver'=>'fa-id-badge',default=>'fa-user'} }}"></i>
                </div>
                <h6>{{ ucfirst($role->name) }}</h6>
                <span class="badge bg-secondary ms-1">{{ $role->users_count }} {{ __('users') }}</span>
                @if(!in_array($role->name,['admin','user']))
                <div class="ms-auto d-flex gap-2">
                    <form id="rdel2-{{ $role->id }}" action="{{ route('roles.destroy',$role) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-icon btn-sm btn-outline-danger" onclick="confirmDelete('rdel2-{{ $role->id }}')">
                            <i class="fas fa-trash" style="font-size:.7rem;"></i>
                        </button>
                    </form>
                </div>
                @endif
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('roles.update',$role) }}">
                    @csrf @method('PUT')
                    <div class="row g-2">
                        @foreach($permissions as $group => $perms)
                        <div class="col-12">
                            <div style="font-size:.72rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.3rem;">{{ ucfirst($group) }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($perms as $perm)
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                        value="{{ $perm->name }}" id="p_{{ $role->id }}_{{ $perm->id }}"
                                        {{ $role->hasPermissionTo($perm->name) ? 'checked' : '' }}
                                        {{ $role->name==='admin' ? 'disabled checked' : '' }}>
                                    <label class="form-check-label" for="p_{{ $role->id }}_{{ $perm->id }}" style="font-size:.78rem;">
                                        {{ ucfirst(str_replace(['_',' '],['_',' '],$perm->name)) }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($role->name !== 'admin')
                    <div class="mt-3">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-save me-1"></i>{{ __('Save Permissions') }}
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Add Role Modal --}}
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#dc3545;color:#fff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="fas fa-shield-alt me-2"></i>{{ __('Add Role') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label">{{ __('Role Name') }} *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. inspector">
                    <small class="text-muted">{{ __('Lowercase, no spaces') }}</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-save me-1"></i>{{ __('Create Role') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
