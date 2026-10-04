<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'term_start',
        'term_end',
        'photo',
        'status',
        'sort_order',
        'archived_at',
        'archived_by'
    ];

    /**
     * Get all active officials ordered by sort_order.
     */
    public static function getActiveOfficials()
    {
        $officials = self::whereNull('archived_at')
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($officials->isEmpty()) {
            $officials = self::whereNull('archived_at')->orderBy('sort_order')->orderBy('name')->get();
        }

        return $officials;
    }

    /**
     * Get the Punong Barangay / Barangay Captain.
     */
    public static function getCaptain()
    {
        $officials = self::getActiveOfficials();

        // 1. Check positions with captain / punong / chairman / chairperson / kapitan
        $captain = $officials->first(function ($o) {
            $pos = strtolower($o->position ?? '');
            return str_contains($pos, 'captain')
                || str_contains($pos, 'punong')
                || str_contains($pos, 'chairman')
                || str_contains($pos, 'chairperson')
                || str_contains($pos, 'kapitan');
        });

        // 2. Fallback to the first official in sort_order
        if (!$captain && $officials->isNotEmpty()) {
            $captain = $officials->first();
        }

        return $captain;
    }

    /**
     * Get Barangay Secretary.
     */
    public static function getSecretary()
    {
        $officials = self::getActiveOfficials();

        return $officials->first(function ($o) {
            $pos = strtolower($o->position ?? '');
            return str_contains($pos, 'sec') || str_contains($pos, 'kalihim');
        });
    }

    /**
     * Get Barangay Treasurer.
     */
    public static function getTreasurer()
    {
        $officials = self::getActiveOfficials();

        return $officials->first(function ($o) {
            $pos = strtolower($o->position ?? '');
            return str_contains($pos, 'treas') || str_contains($pos, 'ingat-yaman');
        });
    }

    /**
     * Get SK Chairperson.
     */
    public static function getSkChairman()
    {
        $officials = self::getActiveOfficials();

        return $officials->first(function ($o) {
            $pos = strtolower($o->position ?? '');
            return str_contains($pos, 'sk') || str_contains($pos, 'kabataan');
        });
    }

    /**
     * Get Barangay Kagawads / Councilors.
     */
    public static function getKagawads()
    {
        $officials = self::getActiveOfficials();

        $kagawads = $officials->filter(function ($o) {
            $pos = strtolower($o->position ?? '');
            return str_contains($pos, 'kagawad')
                || str_contains($pos, 'councilor')
                || str_contains($pos, 'konsehal')
                || str_contains($pos, 'member');
        });

        if ($kagawads->isEmpty()) {
            $captain = self::getCaptain();
            $sec = self::getSecretary();
            $treas = self::getTreasurer();
            $sk = self::getSkChairman();

            $kagawads = $officials->reject(function ($o) use ($captain, $sec, $treas, $sk) {
                return ($captain && $o->id === $captain->id)
                    || ($sec && $o->id === $sec->id)
                    || ($treas && $o->id === $treas->id)
                    || ($sk && $o->id === $sk->id);
            });
        }

        return $kagawads;
    }
}
