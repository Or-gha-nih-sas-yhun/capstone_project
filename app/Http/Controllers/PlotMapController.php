<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Purok;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PlotMapController extends Controller
{
    /**
     * Default coordinates for Barangay Pili, Madridejos, Cebu.
     */
    protected float $defaultLat = 11.278500;
    protected float $defaultLng = 123.731500;

    /**
     * Default Purok coordinates for Barangay Pili if not yet configured in DB.
     */
    protected array $purokCoordinates = [
        'Purok 1' => ['lat' => 11.2812, 'lng' => 123.7305, 'color' => '#2563eb'],
        'Purok 2' => ['lat' => 11.2785, 'lng' => 123.7335, 'color' => '#7c3aed'],
        'Purok 3' => ['lat' => 11.2758, 'lng' => 123.7290, 'color' => '#059669'],
        'Purok 4' => ['lat' => 11.2825, 'lng' => 123.7350, 'color' => '#d97706'],
        'Purok 5' => ['lat' => 11.2760, 'lng' => 123.7360, 'color' => '#dc2626'],
        'Purok 6' => ['lat' => 11.2730, 'lng' => 123.7320, 'color' => '#0891b2'],
        'Purok 7' => ['lat' => 11.2800, 'lng' => 123.7260, 'color' => '#4f46e5'],
    ];

    /**
     * Show the main plot mapping page.
     */
    public function index(Request $request)
    {
        $hasCoords = Schema::hasTable('residents') && Schema::hasColumn('residents', 'latitude');

        $query = Resident::whereNull('archived_at');

        $totalResidents = (clone $query)->count();

        $mappedCount = $hasCoords
            ? (clone $query)->whereNotNull('latitude')->whereNotNull('longitude')->count()
            : 0;

        $unmappedCount = $totalResidents - $mappedCount;

        $pwdCount = (clone $query)->where('is_pwd', true)->count();

        $seniorDate = Carbon::now()->subYears(60)->toDateString();
        $seniorCount = (clone $query)->whereDate('birthdate', '<=', $seniorDate)->count();

        $soloParentCount = (clone $query)->where('is_single_parent', true)->count();
        $voterCount = (clone $query)->where('voter_status', 'Registered')->count();

        // Get puroks
        $puroks = Purok::options();
        if (empty($puroks)) {
            $puroks = ['Purok 1', 'Purok 2', 'Purok 3'];
        }

        $stats = [
            'total'       => $totalResidents,
            'mapped'      => $mappedCount,
            'unmapped'    => $unmappedCount,
            'pwd'         => $pwdCount,
            'senior'      => $seniorCount,
            'solo_parent' => $soloParentCount,
            'voter'       => $voterCount,
        ];

        $center = [
            'lat' => (float) (function_exists('setting') ? setting('barangay.latitude', $this->defaultLat) : $this->defaultLat),
            'lng' => (float) (function_exists('setting') ? setting('barangay.longitude', $this->defaultLng) : $this->defaultLng),
        ];

        return view('admin.plot_map', compact('stats', 'puroks', 'center'));
    }

    /**
     * Return JSON markers for the interactive map.
     */
    public function data(Request $request): JsonResponse
    {
        $filter = $request->query('filter', 'all');
        $purokFilter = $request->query('purok', 'all');
        $search = trim((string) $request->query('search', ''));
        $showEstimated = filter_var($request->query('estimated', true), FILTER_VALIDATE_BOOLEAN);

        $hasCoords = Schema::hasTable('residents') && Schema::hasColumn('residents', 'latitude');

        $query = Resident::whereNull('archived_at');

        // Filter sector
        if ($filter === 'pwd') {
            $query->where('is_pwd', true);
        } elseif ($filter === 'senior') {
            $seniorDate = Carbon::now()->subYears(60)->toDateString();
            $query->whereDate('birthdate', '<=', $seniorDate);
        } elseif ($filter === 'solo_parent') {
            $query->where('is_single_parent', true);
        } elseif ($filter === 'voter') {
            $query->where('voter_status', 'Registered');
        }

        // Filter Purok
        if ($purokFilter && $purokFilter !== 'all') {
            $query->where('purok', $purokFilter);
        }

        // Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $residents = $query->get();

        $markers = [];
        $purokCounters = [];

        foreach ($residents as $res) {
            $hasExact = $hasCoords && ! is_null($res->latitude) && ! is_null($res->longitude);
            $lat = null;
            $lng = null;
            $isEstimated = false;

            if ($hasExact) {
                $lat = (float) $res->latitude;
                $lng = (float) $res->longitude;
            } elseif ($showEstimated) {
                // Synthesize deterministic pseudo-location around the resident's Purok center
                $purokName = $res->purok ?: 'Purok 1';
                $purokCenter = $this->purokCoordinates[$purokName] ?? [
                    'lat' => $this->defaultLat,
                    'lng' => $this->defaultLng,
                ];

                $count = $purokCounters[$purokName] ?? 0;
                $purokCounters[$purokName] = $count + 1;

                // Golden ratio spiral offset for clean natural household distribution around center
                $angle = $count * 2.39996; // Golden angle in radians
                $radius = 0.00035 + (0.00015 * sqrt($count)); // ~40m to ~250m spread
                $lat = $purokCenter['lat'] + ($radius * sin($angle));
                $lng = $purokCenter['lng'] + ($radius * cos($angle) * 1.05);
                $isEstimated = true;
            }

            if ($lat !== null && $lng !== null) {
                $age = $res->birthdate ? Carbon::parse($res->birthdate)->age : null;
                $isSenior = $age !== null && $age >= 60;

                // Priority tag determination
                $primaryTag = 'General';
                $color = '#2563eb'; // blue

                if ($res->is_pwd) {
                    $primaryTag = 'PWD';
                    $color = '#7c3aed'; // purple
                } elseif ($isSenior) {
                    $primaryTag = 'Senior Citizen';
                    $color = '#d97706'; // amber
                } elseif ($res->is_single_parent) {
                    $primaryTag = 'Solo Parent';
                    $color = '#059669'; // teal
                }

                $markers[] = [
                    'id'           => $res->id,
                    'name'         => $res->full_name,
                    'first_name'   => $res->first_name,
                    'last_name'    => $res->last_name,
                    'purok'        => $res->purok ?: 'Unassigned',
                    'address'      => $res->address ?: 'Barangay Pili',
                    'gender'       => $res->gender,
                    'age'          => $age,
                    'birthdate'    => $res->birthdate,
                    'contact'      => $res->contact_number ?: $res->phone_number ?: 'N/A',
                    'civil_status' => $res->civil_status,
                    'voter_status' => $res->voter_status,
                    'is_pwd'       => (bool) $res->is_pwd,
                    'is_senior'    => $isSenior,
                    'is_solo'      => (bool) $res->is_single_parent,
                    'primary_tag'  => $primaryTag,
                    'color'        => $color,
                    'is_estimated' => $isEstimated,
                    'lat'          => round($lat, 7),
                    'lng'          => round($lng, 7),
                ];
            }
        }

        // Include Barangay Landmarks
        $landmarks = [
            [
                'name'        => function_exists('setting') ? setting('barangay.hall_name', 'Barangay Pili Hall') : 'Barangay Pili Hall',
                'type'        => 'Barangay Hall & Session Room',
                'description' => 'Main Barangay Administrative Office and Public Help Desk',
                'icon'        => 'building-columns',
                'color'       => '#dc2626',
                'lat'         => 11.2786,
                'lng'         => 123.7314,
            ],
            [
                'name'        => 'Barangay Health Center',
                'type'        => 'Health & Medical Facility',
                'description' => 'Barangay Health Station, First Aid, and Vaccination Center',
                'icon'        => 'hospital',
                'color'       => '#16a34a',
                'lat'         => 11.2789,
                'lng'         => 123.7319,
            ],
            [
                'name'        => 'Barangay Covered Court / Evacuation Center',
                'type'        => 'Evacuation & Multi-Purpose Facility',
                'description' => 'Primary Emergency Evacuation Center (Capacity: 250 residents)',
                'icon'        => 'tent',
                'color'       => '#ea580c',
                'lat'         => 11.2781,
                'lng'         => 123.7310,
            ],
            [
                'name'        => 'Pili Elementary / Day Care Center',
                'type'        => 'Education & Child Development',
                'description' => 'Early Childhood Care and Secondary Shelter Facility',
                'icon'        => 'school',
                'color'       => '#0284c7',
                'lat'         => 11.2798,
                'lng'         => 123.7328,
            ],
        ];

        return response()->json([
            'count'     => count($markers),
            'markers'   => $markers,
            'landmarks' => $landmarks,
            'puroks'    => $this->purokCoordinates,
        ]);
    }

    /**
     * Update a resident's latitude and longitude via AJAX.
     */
    public function updateResidentLocation(Request $request): JsonResponse
    {
        $residentId = $request->input('resident_id', $request->input('residentId'));
        $request->merge(['resident_id' => $residentId]);

        $request->validate([
            'resident_id' => 'required|integer|exists:residents,id',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
        ]);

        $hasCoords = Schema::hasTable('residents') && Schema::hasColumn('residents', 'latitude');
        if (! $hasCoords) {
            return response()->json([
                'success' => false,
                'message' => 'Coordinates columns do not exist in the database yet. Please run migrations first.',
            ], 422);
        }

        $resident = Resident::findOrFail($request->resident_id);
        $resident->latitude = $request->latitude;
        $resident->longitude = $request->longitude;
        $resident->save();

        if (class_exists(ActivityLog::class) && Auth::check()) {
            try {
                ActivityLog::create([
                    'user_id'     => Auth::id(),
                    'action'      => 'Plot Map Location Update',
                    'description' => "Updated spot map coordinates for {$resident->full_name} ({$resident->purok}) to [{$request->latitude}, {$request->longitude}].",
                    'ip_address'  => $request->ip(),
                ]);
            } catch (\Throwable $e) {
                // Silently ignore log failure
            }
        }

        return response()->json([
            'success'  => true,
            'message'  => "Location updated successfully for {$resident->full_name}.",
            'resident' => [
                'id'   => $resident->id,
                'name' => $resident->full_name,
                'lat'  => (float) $resident->latitude,
                'lng'  => (float) $resident->longitude,
            ],
        ]);
    }

    /**
     * Returns list of residents who do not have an exact GPS pin yet.
     */
    public function unmappedList(Request $request): JsonResponse
    {
        $hasCoords = Schema::hasTable('residents') && Schema::hasColumn('residents', 'latitude');

        $query = Resident::whereNull('archived_at');

        if ($hasCoords) {
            $query->where(function ($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            });
        }

        $purok = $request->query('purok');
        if ($purok && $purok !== 'all') {
            $query->where('purok', $purok);
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $residents = $query->orderBy('last_name')->orderBy('first_name')->limit(50)->get([
            'id', 'first_name', 'last_name', 'purok', 'address', 'gender', 'birthdate', 'is_pwd', 'is_single_parent',
        ]);

        return response()->json([
            'count'     => count($residents),
            'residents' => $residents,
        ]);
    }
}
