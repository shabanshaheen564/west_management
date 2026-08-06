<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Container extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'name_ar', 'type', 'capacity', 'fill_level',
        'latitude', 'longitude', 'address', 'address_ar', 'zone',
        'status', 'last_emptied_at', 'last_checked_at', 'sensor_data',
        'rfid_tag', 'notes',
    ];

    protected $casts = [
        'latitude'       => 'float',
        'longitude'      => 'float',
        'capacity'       => 'float',
        'fill_level'     => 'float',
        'sensor_data'    => 'array',
        'last_emptied_at'=> 'datetime',
        'last_checked_at'=> 'datetime',
    ];

    public function routes()
    {
        return $this->belongsToMany(Route::class, 'route_containers')
                    ->withPivot('order', 'status', 'collected_at', 'notes')
                    ->withTimestamps();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active'      => 'success',
            'inactive'    => 'secondary',
            'maintenance' => 'warning',
            'full'        => 'danger',
            default       => 'secondary',
        };
    }

    public function getFillColorAttribute(): string
    {
        if ($this->fill_level >= 90) return 'danger';
        if ($this->fill_level >= 70) return 'warning';
        if ($this->fill_level >= 40) return 'info';
        return 'success';
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'recyclable' => 'fa-recycle',
            'organic'    => 'fa-leaf',
            'hazardous'  => 'fa-radiation',
            'electronic' => 'fa-microchip',
            default      => 'fa-trash',
        };
    }

    public function getNeedsEmptyingAttribute(): bool
    {
        return $this->fill_level >= 80;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNeedsEmptying($query)
    {
        return $query->where('fill_level', '>=', 80);
    }

    public function scopeByZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }

    public function toGeoJson(): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$this->longitude, $this->latitude],
            ],
            'properties' => [
                'id'         => $this->id,
                'code'       => $this->code,
                'name'       => $this->name,
                'type'       => $this->type,
                'fill_level' => $this->fill_level,
                'status'     => $this->status,
                'fill_color' => $this->fill_color,
                'address'    => $this->address,
                'zone'       => $this->zone,
            ],
        ];
    }
}
