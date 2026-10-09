<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Read a runtime barangay setting.
     */
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('barangay_name')) {
    /**
     * Bare name, e.g. "Pili".
     */
    function barangay_name(): string
    {
        return (string) setting('barangay.name', 'Barangay');
    }
}

if (! function_exists('barangay_label')) {
    /**
     * Prefixed name, e.g. "Barangay Pili".
     */
    function barangay_label(): string
    {
        return 'Barangay ' . barangay_name();
    }
}

if (! function_exists('barangay_location')) {
    /**
     * "Pili, Madridejos, Cebu" — the non-empty parts only.
     */
    function barangay_location(bool $withPrefix = false): string
    {
        $parts = array_filter([
            $withPrefix ? barangay_label() : barangay_name(),
            setting('barangay.municipality'),
            setting('barangay.province'),
        ]);

        return implode(', ', $parts);
    }
}

if (! function_exists('barangay_province_line')) {
    /**
     * "Province of Cebu" for document letterheads.
     */
    function barangay_province_line(): string
    {
        $province = setting('barangay.province');

        return $province ? 'Province of ' . $province : '';
    }
}

if (! function_exists('barangay_municipality_line')) {
    /**
     * "Municipality of Madridejos" / "City of ..." for letterheads.
     */
    function barangay_municipality_line(): string
    {
        $municipality = setting('barangay.municipality');

        if (! $municipality) {
            return '';
        }

        $type = setting('barangay.municipality_type', 'Municipality');

        // Avoid "City of Cebu City" when the name already carries the word,
        // which is how most Philippine cities are commonly written.
        if (preg_match('/\b' . preg_quote($type, '/') . '\b/i', $municipality)) {
            return $municipality;
        }

        return $type . ' of ' . $municipality;
    }
}

if (! function_exists('barangay_municipality_province')) {
    /**
     * "Madridejos, Cebu" — used on "issued at" / signing lines.
     */
    function barangay_municipality_province(): string
    {
        $parts = array_filter([
            setting('barangay.municipality'),
            setting('barangay.province'),
        ]);

        return implode(', ', $parts);
    }
}

if (! function_exists('barangay_office_title')) {
    /**
     * Heading over the signature block, e.g. "Office of the Punong Barangay".
     */
    function barangay_office_title(): string
    {
        return (string) setting('barangay.office_title', 'Office of the Punong Barangay');
    }
}

if (! function_exists('purok_rule')) {
    /**
     * Validation rules for a purok field.
     *
     * Accepts any name in the puroks table, including inactive ones, so a
     * resident whose purok was later deactivated or renamed can still have
     * their record edited. Falls back to free text when the barangay has not
     * defined any puroks yet.
     */
    function purok_rule(): array
    {
        $base = ['nullable', 'string', 'max:100'];

        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('puroks')) {
                return $base;
            }

            $names = \App\Models\Purok::pluck('name')->all();
        } catch (\Throwable $e) {
            return $base;
        }

        if (! $names) {
            return $base;
        }

        $base[] = \Illuminate\Validation\Rule::in($names);

        return $base;
    }
}

if (! function_exists('setting_image')) {
    /**
     * Public URL for an image setting, falling back to a bundled asset.
     */
    function setting_image(string $key, string $fallback = 'assets/images/pili_logo.png'): string
    {
        $path = setting($key, $fallback);

        // Already an absolute URL (e.g. remote logo) — use as-is.
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}

if (! function_exists('sms_signature')) {
    /**
     * Trailing attribution on outgoing SMS, e.g. "- Barangay Pili".
     */
    function sms_signature(): string
    {
        return (string) setting('system.sms_signature', barangay_label());
    }
}

if (! function_exists('is_mobile_app')) {
    /**
     * Whether the current request came from the bundled Android wrapper.
     *
     * Accepts the configured token plus the legacy "BrgyPiliApp" value so
     * APKs already installed on residents' phones keep working after a
     * barangay renames the app.
     */
    function is_mobile_app($request = null): bool
    {
        $request = $request ?: request();

        if (! $request) {
            return false;
        }

        $ua = (string) $request->header('User-Agent', '');

        if ($ua === '') {
            return false;
        }

        foreach (array_unique([config('barangay.mobile_ua_token', 'BrgyPortalApp'), 'BrgyPiliApp']) as $token) {
            if ($token && str_contains($ua, $token)) {
                return true;
            }
        }

        return false;
    }
}
