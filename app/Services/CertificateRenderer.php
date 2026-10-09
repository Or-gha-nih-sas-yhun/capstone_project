<?php

namespace App\Services;

use App\Models\Request as CertificateRequest;

/**
 * Turns a certificate's stored body_template into printable HTML.
 *
 * Resident-supplied values (names, addresses, purposes) are HTML-escaped
 * before substitution, so a resident cannot inject markup into an official
 * document by typing it into their profile. Only the tokens listed in
 * tokens() are recognised; anything else is left untouched so a typo shows
 * up visibly rather than silently rendering as an empty string.
 *
 * A small set of formatting tags is permitted in the template itself, since
 * the template is authored by barangay staff, not residents.
 */
class CertificateRenderer
{
    /** Tags barangay staff may use inside a body template. */
    private const ALLOWED_TAGS = '<strong><b><em><i><u><br><p><span><sup>';

    /**
     * Token => human description, for the admin editor's reference list.
     */
    public static function tokens(): array
    {
        return [
            'full_name'     => "Resident's full name, upper-cased",
            'first_name'    => 'First name',
            'last_name'     => 'Last name',
            'age'           => 'Age in years',
            'gender'        => 'Male / Female',
            'civil_status'  => 'Single, Married, Widowed, …',
            'address'       => 'Street address on file',
            'purok'         => 'Purok / sitio',
            'years_residency' => 'Years of residency',
            'purpose'       => 'Purpose stated on the request',
            'purpose_upper' => 'Purpose, upper-cased',
            'certificate'   => 'Name of this certificate type',
            'barangay'      => 'Barangay name, e.g. Pili',
            'barangay_label' => 'e.g. Barangay Pili',
            'municipality'  => 'Municipality / city',
            'province'      => 'Province',
            'location'      => 'e.g. Pili, Madridejos, Cebu',
            'date'          => 'Today, e.g. 9 October 2026',
            'day'           => 'Day of month, e.g. 9th',
            'month_year'    => 'e.g. October 2026',
            'tracking'      => 'Tracking number of the request',
        ];
    }

    /**
     * Values for every token, already HTML-escaped.
     */
    public function values(CertificateRequest $request): array
    {
        $resident = $request->resident;

        $raw = [
            'full_name'       => strtoupper((string) ($resident->full_name ?? '')),
            'first_name'      => (string) ($resident->first_name ?? ''),
            'last_name'       => (string) ($resident->last_name ?? ''),
            'age'             => (string) ($resident->age ?? ''),
            'gender'          => (string) ($resident->gender ?? ''),
            'civil_status'    => (string) ($resident->civil_status ?? ''),
            'address'         => (string) ($resident->address ?? ''),
            'purok'           => (string) ($resident->purok ?? ''),
            'years_residency' => (string) ($resident->years_of_residency ?? ''),
            'purpose'         => (string) ($request->purpose ?? ''),
            'purpose_upper'   => strtoupper((string) ($request->purpose ?? '')),
            'certificate'     => (string) ($request->certificate->name ?? ''),
            'barangay'        => barangay_name(),
            'barangay_label'  => barangay_label(),
            'municipality'    => (string) setting('barangay.municipality', ''),
            'province'        => (string) setting('barangay.province', ''),
            'location'        => barangay_location(),
            'date'            => date('j F Y'),
            'day'             => date('jS'),
            'month_year'      => date('F Y'),
            'tracking'        => (string) ($request->tracking_number ?? ''),
        ];

        return array_map(
            fn ($value) => htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
            $raw
        );
    }

    /**
     * Render a template string. Returns null when there is nothing to render.
     */
    public function render(?string $template, CertificateRequest $request): ?string
    {
        if ($template === null || trim($template) === '') {
            return null;
        }

        // The template is staff-authored, so a limited tag set survives;
        // anything else (script, iframe, event handlers on stripped tags)
        // is removed before tokens are substituted.
        $safe = strip_tags($template, self::ALLOWED_TAGS);

        $values = $this->values($request);

        return preg_replace_callback(
            '/\{\{\s*([a-z_]+)\s*\}\}/i',
            function ($matches) use ($values) {
                $key = strtolower($matches[1]);

                // Unknown token: leave the placeholder visible so whoever
                // wrote the template can see the mistake.
                return $values[$key] ?? $matches[0];
            },
            $safe
        );
    }
}
