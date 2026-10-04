<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'status',
        'resident_id',
        'photo',
        'verification_code',
        'email_verified_at',
        'admin_otp_code',
        'admin_otp_expires_at',
        'archived_at',
        'archived_by'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'admin_otp_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'admin_otp_code',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }
}
