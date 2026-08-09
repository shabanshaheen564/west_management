<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dumpsite extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code','name','name_ar','latitude','longitude','address',
        'type','status','total_capacity','current_fill','fill_percentage',
        'opening_time','closing_time','accepted_waste_types','boundary_polygon','notes',
    ];

    protected $casts = [
        'latitude'             => 'float',
        'longitude'            => 'float',
        'total_capacity'       => 'float',
        'current_fill'         => 'float',
        'fill_percentage'      => 'float',
        'accepted_waste_types' => 'array',
        'boundary_polygon'     => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Dumpsite $dumpsite) {
            $capacity = (float) $dumpsite->total_capacity;
            $fill = max(0, (float) $dumpsite->current_fill);

            if ($capacity <= 0) {
                $dumpsite->fill_percentage = 0;
                return;
            }

            $fill = min($fill, $capacity);
            $dumpsite->current_fill = $fill;
            $dumpsite->fill_percentage = round(($fill / $capacity) * 100, 2);

            // Do not override maintenance/inactive states automatically.
            if (!in_array($dumpsite->status, ['maintenance', 'inactive'], true)) {
                $dumpsite->status = $dumpsite->fill_percentage >= 100 ? 'full' : 'active';
            }
        });
    }

    public function routes() { return $this->hasMany(Route::class); }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active'      => 'success',
            'full'        => 'danger',
            'maintenance' => 'warning',
            default       => 'secondary',
        };
    }

    public function getFillColorAttribute(): string
    {
        if ($this->fill_percentage >= 90) return 'danger';
        if ($this->fill_percentage >= 70) return 'warning';
        if ($this->fill_percentage >= 40) return 'info';
        return 'success';
    }

    public function toGeoJson(): array
    {
        return [
            'type' => 'Feature',
            'geometry' => ['type'=>'Point','coordinates'=>[$this->longitude,$this->latitude]],
            'properties' => [
                'id'              => $this->id,
                'code'            => $this->code,
                'name'            => $this->name,
                'type'            => $this->type,
                'fill_percentage' => $this->fill_percentage,
                'status'          => $this->status,
            ],
        ];
    }

    public function scopeActive($query) { return $query->where('status','active'); }
}
