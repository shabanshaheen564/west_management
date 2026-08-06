<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plate_number', 'model', 'brand', 'year', 'type', 'capacity',
        'status', 'current_lat', 'current_lng', 'fuel_level', 'fuel_type',
        'last_maintenance', 'next_maintenance', 'insurance_number',
        'insurance_expiry', 'registration_number', 'registration_expiry',
        'gps_data', 'notes',
    ];

    protected $casts = [
        'gps_data'         => 'array',
        'last_maintenance' => 'date',
        'next_maintenance' => 'date',
        'insurance_expiry' => 'date',
        'registration_expiry' => 'date',
        'current_lat'      => 'float',
        'current_lng'      => 'float',
        'fuel_level'       => 'float',
        'capacity'         => 'float',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    public function activeRoute()
    {
        return $this->hasOne(Route::class)->where('status', 'active');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active'      => 'success',
            'on_route'    => 'primary',
            'maintenance' => 'warning',
            'inactive'    => 'danger',
            default       => 'secondary',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'compactor'   => 'fa-truck',
            'tipper'      => 'fa-truck-loading',
            'suction'     => 'fa-toilet',
            'mini_truck'  => 'fa-shuttle-van',
            default       => 'fa-truck',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->whereIn('status', ['active']);
    }

    public function toGeoJson(): array
    {
        if (!$this->current_lat || !$this->current_lng) return [];
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$this->current_lng, $this->current_lat],
            ],
            'properties' => [
                'id'           => $this->id,
                'plate_number' => $this->plate_number,
                'type'         => $this->type,
                'status'       => $this->status,
                'fuel_level'   => $this->fuel_level,
            ],
        ];
    }
}
