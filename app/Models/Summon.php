<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Summon extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number',
        'case_type',
        'complainant_name',
        'complainant_contact',
        'respondent_name',
        'respondent_contact',
        'complain_details',
        'incident_date',
        'incident_location',
        'nature_of_complaint',
        'schedule_date',
        'status',
        'hearing_remarks',
        'complainant_resident_id',
        'respondent_resident_id',
        'archived_at',
        'archived_by',
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
        'incident_date' => 'datetime',
    ];

    public function complainantResident()
    {
        return $this->belongsTo(Resident::class, 'complainant_resident_id');
    }

    public function respondentResident()
    {
        return $this->belongsTo(Resident::class, 'respondent_resident_id');
    }

    public function hearings()
    {
        return $this->hasMany(SummonHearing::class, 'summon_id');
    }
}
