@extends('layouts.skeleton')
@section('title', __('Profile'))
@section('page-title', __('My Profile'))

@section('content')

<div class="page-header">
    <div class="page-header-icon" style="background:#555;"><i class="fas fa-user"></i></div>
    <div>
        <h4>{{ __('My Profile') }}</h4>
        <p>{{ __('Your account information') }}</p>
    </div>
</div>

<div class="card" style="max-width:480px;">
    <div class="card-body">
        <div class="mb-3">
            <label class="text-muted" style="font-size:.8rem;">{{ __('Name') }}</label>
            <div>{{ $user->name }}</div>
        </div>
        <div class="mb-3">
            <label class="text-muted" style="font-size:.8rem;">{{ __('Email') }}</label>
            <div>{{ $user->email }}</div>
        </div>
        @if($user->roles->isNotEmpty())
        <div class="mb-3">
            <label class="text-muted" style="font-size:.8rem;">{{ __('Role') }}</label>
            <div>{{ $user->roles->pluck('name')->implode(', ') }}</div>
        </div>
        @endif
    </div>
</div>

@endsection
