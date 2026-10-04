<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'gender',
        'birthdate',
        'civil_status',
        'nationality',
        'religion',
        'occupation',
        'contact_number',
        'email',
        'address',
        'purok',
        'voter_status',
        'years_of_residency',
        'photo',
        'status',
        'archived_at',
        'archived_by'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'resident_id');
    }

    public function requests()
    {
        return $this->hasMany(Request::class, 'resident_id');
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name . ' ' . $this->suffix);
    }

    public function getAgeAttribute()
    {
        return \Carbon\Carbon::parse($this->birthdate)->age;
    }
}
