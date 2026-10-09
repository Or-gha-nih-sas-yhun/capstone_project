<?php

namespace App\Console\Commands;

use App\Models\Purok;
use App\Models\Setting;
use Illuminate\Console\Command;

/**
 * First-run configuration for a new barangay deployment.
 *
 * Everything this writes can also be edited later from
 * Admin → Barangay Settings; this exists so a deployment can be configured
 * before anyone logs in.
 */
class BarangaySetup extends Command
{
    protected $signature = 'barangay:setup
                            {--show : Print the current configuration and exit}';

    protected $description = 'Configure this deployment for a barangay (name, municipality, province, puroks)';

    public function handle(): int
    {
        if ($this->option('show')) {
            return $this->showCurrent();
        }

        $this->info('Barangay deployment setup');
        $this->line('Press Enter to keep the value shown in brackets.');
        $this->newLine();

        $name = $this->ask('Barangay name (without the word "Barangay")', setting('barangay.name', ''));

        $municipalityType = $this->choice(
            'Is it a municipality or a city?',
            ['Municipality', 'City'],
            setting('barangay.municipality_type', 'Municipality') === 'City' ? 1 : 0
        );

        $municipality = $this->ask('Municipality / city name', setting('barangay.municipality', ''));
        $province = $this->ask('Province', setting('barangay.province', ''));
        $region = $this->ask('Region (optional)', setting('barangay.region', ''));
        $email = $this->ask('Official barangay email (optional)', setting('barangay.email', ''));
        $contact = $this->ask('Contact number (optional)', setting('barangay.contact', ''));

        $defaultPrefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name) ?: 'BRGY');
        $prefix = $this->ask('Tracking number prefix', setting('system.tracking_prefix', substr($defaultPrefix, 0, 12)));

        $officeTitle = $this->ask(
            'Office title printed on documents',
            setting('barangay.office_title', 'Office of the Punong Barangay')
        );

        Setting::putMany([
            'barangay.name'              => trim((string) $name),
            'barangay.municipality'      => trim((string) $municipality),
            'barangay.municipality_type' => $municipalityType,
            'barangay.province'          => trim((string) $province),
            'barangay.region'            => trim((string) $region),
            'barangay.email'             => trim((string) $email),
            'barangay.contact'           => trim((string) $contact),
            'barangay.office_title'      => trim((string) $officeTitle),
            'barangay.hall_name'         => 'Barangay ' . trim((string) $name) . ' Hall',
            'barangay.address'           => 'Barangay ' . trim((string) $name) . ', '
                                            . trim((string) $municipality) . ', ' . trim((string) $province),
        ], 'identity');

        Setting::putMany([
            'brand.app_title'   => 'Barangay ' . trim((string) $name) . ' Clearance & Certificate System',
            'brand.short_name'  => 'Brgy. ' . trim((string) $name),
            'brand.portal_name' => 'Barangay ' . trim((string) $name) . ' Portal',
        ], 'branding');

        Setting::putMany([
            'system.tracking_prefix' => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $prefix) ?: 'BRGY'),
            'system.sms_signature'   => 'Barangay ' . trim((string) $name),
            'system.setup_complete'  => '1',
        ], 'system');

        $this->setupPuroks();

        $this->newLine();
        $this->info('Saved.');
        $this->newLine();
        $this->showCurrent();

        $this->newLine();
        $this->line('Remaining steps:');
        $this->line('  1. Upload the barangay and municipality seals in Admin -> Barangay Settings.');
        $this->line('  2. Add the barangay officials in Admin -> Officials (names on documents come from there).');
        $this->line('  3. Review certificate types and fees in Admin -> Certificates.');

        return self::SUCCESS;
    }

    private function setupPuroks(): void
    {
        $existing = Purok::count();

        if ($existing > 0 && ! $this->confirm("There are already {$existing} purok(s). Add more?", false)) {
            return;
        }

        $this->newLine();
        $this->line('Enter the puroks / sitios, separated by commas (leave blank to skip).');
        $answer = $this->ask('Puroks', '');

        if (! $answer) {
            return;
        }

        $order = Purok::max('sort_order') ?? -1;
        $added = 0;

        foreach (explode(',', $answer) as $name) {
            $name = trim($name);

            if ($name === '') {
                continue;
            }

            // firstOrCreate keeps the command safe to re-run.
            $purok = Purok::firstOrCreate(
                ['name' => $name],
                ['sort_order' => ++$order, 'status' => 'active']
            );

            if ($purok->wasRecentlyCreated) {
                $added++;
            }
        }

        $this->info("Added {$added} purok(s).");
    }

    private function showCurrent(): int
    {
        $this->table(['Setting', 'Value'], [
            ['Barangay', barangay_label()],
            ['Location', barangay_location()],
            ['Letterhead', barangay_province_line() . ' / ' . barangay_municipality_line()],
            ['Office title', setting('barangay.office_title', '—')],
            ['Email', setting('barangay.email', '—')],
            ['Tracking prefix', setting('system.tracking_prefix', '—')],
            ['SMS signature', setting('system.sms_signature', '—')],
            ['Puroks', Purok::count() ? implode(', ', Purok::options()) : '— none defined —'],
        ]);

        return self::SUCCESS;
    }
}
