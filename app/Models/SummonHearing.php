<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummonHearing extends Model
{
    use HasFactory;

    protected $fillable = [
        'summon_id',
        'hearing_number',
        'schedule_date',
        'remarks',
        'conducted_by',
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
    ];

    public function summon()
    {
        return $this->belongsTo(Summon::class, 'summon_id');
    }
}
