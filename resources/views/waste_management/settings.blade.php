@extends('layouts.skeleton')
@section('title', __('Settings'))
@section('page-title', __('System Settings'))

@section('content')

<div class="page-header">
    <div class="page-header-icon"><i class="fas fa-cog"></i></div>
    <div><h4>{{ __('Settings') }}</h4><p>{{ __('Configure system preferences') }}</p></div>
</div>

<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="row g-3">
        @foreach($settings as $group => $groupSettings)
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas {{ match($group){'general'=>'fa-sliders-h','gis'=>'fa-map-marked-alt','notifications'=>'fa-bell','system'=>'fa-server',default=>'fa-cog'} }} text-primary"></i>
                    <h6>{{ ucfirst($group) }} {{ __('Settings') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($groupSettings as $setting)
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label">
                                {{ app()->getLocale()==='ar' && $setting->label_ar ? $setting->label_ar : ($setting->label ?? $setting->key) }}
                            </label>
                            @if($setting->type === 'boolean')
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="{{ $setting->key }}" value="1" {{ $setting->value ? 'checked' : '' }}>
                                </div>
                            @elseif($setting->type === 'color')
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" name="{{ $setting->key }}" class="form-control form-control-color" value="{{ $setting->value ?? '#2d8a4e' }}" style="width:50px;height:38px;">
                                    <input type="text" class="form-control" value="{{ $setting->value ?? '#2d8a4e' }}" oninput="document.querySelector('input[name={{ $setting->key }}][type=color]').value=this.value" style="font-family:monospace;">
                                </div>
                            @elseif($setting->type === 'file')
                                <input type="file" name="{{ $setting->key }}_file" class="form-control" accept="image/*">
                                @if($setting->value)
                                <small class="text-muted">{{ __('Current') }}: {{ $setting->value }}</small>
                                @endif
                            @elseif($setting->type === 'number')
                                <input type="number" name="{{ $setting->key }}" class="form-control" value="{{ $setting->value }}" step="any">
                            @else
                                <input type="text" name="{{ $setting->key }}" class="form-control" value="{{ $setting->value }}">
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <div class="col-12">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>{{ __('Save All Settings') }}
            </button>
        </div>
    </div>
</form>

@endsection
