<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * The puroks / sitios a barangay is divided into.
 *
 * residents.purok stays a plain string rather than a foreign key: that keeps
 * historical records readable after a purok is renamed or removed, and avoids
 * rewriting every resident query. This table drives the dropdowns and is
 * validated against on new input only.
 */
class Purok extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sort_order', 'status'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Active purok names for dropdowns. Returns [] when the table does not
     * exist yet, so views fall back to a free-text field.
     */
    public static function options(): array
    {
        try {
            if (! Schema::hasTable('puroks')) {
                return [];
            }

            return static::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function residentCount(): int
    {
        return Resident::where('purok', $this->name)->count();
    }
}
