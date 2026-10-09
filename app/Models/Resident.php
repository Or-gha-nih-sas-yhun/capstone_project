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
        // Drop the blank parts before joining: concatenating with spaces and
        // trimming only the ends left a double space inside the name whenever
        // a middle name or suffix was missing, which showed up on printed
        // certificates.
        $parts = array_filter(
            array_map(
                fn ($part) => trim((string) $part),
                [$this->first_name, $this->middle_name, $this->last_name, $this->suffix]
            ),
            fn ($part) => $part !== ''
        );

        return implode(' ', $parts);
    }

    public function getAgeAttribute()
    {
        return \Carbon\Carbon::parse($this->birthdate)->age;
    }
}
