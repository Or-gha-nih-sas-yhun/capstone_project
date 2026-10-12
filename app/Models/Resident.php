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
        'is_pwd',
        'is_single_parent',
        'nationality',
        'religion',
        'occupation',
        'contact_number',
        'phone_number',
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

    protected $casts = [
        'is_pwd'           => 'boolean',
        'is_single_parent' => 'boolean',
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
