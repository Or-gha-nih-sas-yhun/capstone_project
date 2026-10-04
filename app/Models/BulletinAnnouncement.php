<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulletinAnnouncement extends Model
{
    use HasFactory;

    protected $table = 'bulletin_announcements';

    protected $fillable = [
        'title',
        'content',
        'image_path',
        'category',
        'published_at',
        'created_by',
        'is_pinned',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_pinned' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
