<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'amount',
        'payment_method',
        'payment_status',
        'receipt_number',
        'proof_of_payment',
        'paid_at',
        'received_by'
    ];

    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
