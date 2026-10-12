<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Baseline data every barangay deployment needs: staff accounts and the
 * standard certificate types issued under the Local Government Code.
 *
 * Nothing here is specific to one barangay. Sample officials and residents
 * for demos live in DemoDataSeeder, which is not run by default:
 *
 *     php artisan db:seed --class=DemoDataSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->seedUsers();
        $this->seedCertificates();

        $this->command->info('Baseline data seeded.');
        $this->command->line('Next: php artisan barangay:setup');
    }

    private function seedUsers(): void
    {
        // Derived from the deployment's own domain rather than a fixed
        // barangay address, so a new deployment does not inherit another
        // barangay's email domain.
        $domain = config('barangay.domain', 'localhost');

        $accounts = [
            ['username' => 'admin', 'role' => 'admin'],
            ['username' => 'staff1', 'role' => 'staff'],
        ];

        foreach ($accounts as $account) {
            User::firstOrCreate(
                ['username' => $account['username']],
                [
                    'email'    => $account['username'] . '@' . $domain,
                    'password' => Hash::make('password'),
                    'role'     => $account['role'],
                    'status'   => 'active',
                ]
            );
        }

        $this->command->warn('Default accounts use the password "password" — change them before going live.');
    }

    private function seedCertificates(): void
    {
        $certs = [
            [
                'name' => 'Barangay Clearance',
                'description' => 'General clearance issued by the barangay for various purposes',
                'category' => 'Clearance',
                'fee' => 50.00,
                'processing_days' => 1,
                'template_file' => 'certificate_clearance.php',
                'requirements' => 'Valid ID, Proof of residency',
                'status' => 'active',
            ],
            [
                'name' => 'Certificate of Residency',
                'description' => 'Certifies that the person is a legitimate resident of this barangay',
                'category' => 'Certification',
                'fee' => 50.00,
                'processing_days' => 1,
                'template_file' => 'certificate_residency.php',
                'requirements' => 'Valid ID',
                'status' => 'active',
            ],
            [
                'name' => 'Certificate of Indigency',
                'description' => 'Certifies that the resident belongs to the indigent sector',
                'category' => 'Social Services',
                'fee' => 0.00,
                'processing_days' => 1,
                'template_file' => 'certificate_indigency.php',
                'requirements' => 'Valid ID, Proof of indigency',
                'status' => 'active',
            ],
            [
                'name' => 'Business Clearance',
                'description' => 'Clearance required for business permit applications',
                'category' => 'Business',
                'fee' => 100.00,
                'processing_days' => 3,
                'template_file' => 'certificate_clearance.php',
                'requirements' => 'Valid ID, Business documents',
                'status' => 'active',
            ],
            [
                'name' => 'Certificate of Good Moral Character',
                'description' => 'Certifies good moral standing in the community',
                'category' => 'Certification',
                'fee' => 50.00,
                'processing_days' => 1,
                'template_file' => 'certificate_moral.php',
                'requirements' => 'Valid ID',
                'status' => 'active',
            ],
            [
                'name' => 'First Time Jobseeker Certificate',
                'description' => 'For first-time jobseekers as per RA 11261',
                'category' => 'Employment',
                'fee' => 0.00,
                'processing_days' => 1,
                'template_file' => 'certificate.php',
                'requirements' => 'Valid ID, Barangay Certificate',
                'status' => 'active',
            ],
        ];

        foreach ($certs as $c) {
            // Keeps the seeder safe to re-run on an existing deployment and
            // avoids duplicating a type the barangay has already edited.
            Certificate::firstOrCreate(['name' => $c['name']], $c);
        }
    }
}
