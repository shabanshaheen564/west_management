<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code','name','name_ar','vehicle_id','driver_id','dumpsite_id',
        'status','frequency','waypoints','geojson_path','total_distance',
        'estimated_duration','actual_distance','actual_duration',
        'scheduled_at','started_at','completed_at',
        'start_lat','start_lng','end_lat','end_lng','notes',
    ];

    protected $casts = [
        'waypoints'    => 'array',
        'geojson_path' => 'array',
        'scheduled_at' => 'datetime',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver()  { return $this->belongsTo(Driver::class); }
    public function dumpsite(){ return $this->belongsTo(Dumpsite::class); }

    public function containers()
    {
        return $this->belongsToMany(Container::class, 'route_containers')
                    ->withPivot('order','status','collected_at','notes')
                    ->orderBy('route_containers.order')
                    ->withTimestamps();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'planned'   => 'info',
            'active'    => 'success',
            'completed' => 'primary',
            'cancelled' => 'danger',
            default     => 'secondary',
        };
    }

    public function scopeActive($query) { return $query->where('status','active'); }
    public function scopeToday($query)  { return $query->whereDate('scheduled_at', today()); }
}