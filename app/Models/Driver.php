<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_id',
        'name',
        'name_ar',
        'phone',
        'email',
        'license_number',
        'license_class',
        'license_expiry',
        'hire_date',
        'status',
        'avatar',
        'rating',
        'total_trips',
        'address',
        'national_id',
        'notes',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'hire_date' => 'date',
        'rating' => 'float',
        'total_trips' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'driver_id');
    }
    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'on_leave' => 'warning',
            'suspended' => 'danger',
            'inactive' => 'secondary',
            default => 'secondary',
        };
    }

    // public function getIsLicenseExpiredAttribute(): bool
    // {
    //     return $this->license_expiry->isPast();
    // }

    // public function getIsLicenseExpiringSoonAttribute(): bool
    // {
    //     return $this->license_expiry->diffInDays(now()) <= 30 && !$this->is_license_expired;
    // }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
            ->whereDoesntHave('routes', function ($q) {
                $q->where('status', 'active');
            });
    }
}
