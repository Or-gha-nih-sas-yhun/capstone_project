<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_number',
        'resident_id',
        'certificate_id',
        'purpose',
        'status',
        'remarks',
        'processed_by',
        'approved_by',
        'requested_at',
        'processed_at',
        'approved_at',
        'released_at',
        'expected_release_date',
        'archived_at',
        'archived_by'
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    public function certificate()
    {
        return $this->belongsTo(Certificate::class, 'certificate_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'request_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
