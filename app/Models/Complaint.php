<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number','user_id','complainant_name','complainant_phone',
        'complainant_email','category','subject','description',
        'latitude','longitude','address','priority','status',
        'assigned_to','resolution_notes','resolved_at','attachments',
    ];

    protected $casts = [
        'latitude'    => 'float',
        'longitude'   => 'float',
        'attachments' => 'array',
        'resolved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($complaint) {
            $complaint->ticket_number = 'WMS-'.date('Ymd').'-'.str_pad(
                Complaint::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT
            );
        });
    }

    public function user()       { return $this->belongsTo(User::class); }
    public function assignedTo() { return $this->belongsTo(User::class,'assigned_to'); }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open'        => 'danger',
            'in_progress' => 'warning',
            'resolved'    => 'success',
            'closed'      => 'secondary',
            default       => 'secondary',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'danger',
            'high'   => 'warning',
            'medium' => 'info',
            default  => 'secondary',
        };
    }
}