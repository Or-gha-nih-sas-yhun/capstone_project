<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'fee',
        'processing_days',
        'template_file',
<<<<<<< HEAD
=======
        'body_template',
        'header_title',
        'signatory_position',
>>>>>>> origin/master
        'requirements',
        'status',
        'archived_at',
        'archived_by'
    ];

    public function requests()
    {
        return $this->hasMany(Request::class, 'certificate_id');
    }
}
