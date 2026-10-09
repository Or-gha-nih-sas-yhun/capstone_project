<?php

namespace Database\Seeders;

use App\Models\Official;
use App\Models\Purok;
use App\Models\Resident;
use Illuminate\Database\Seeder;

/**
 * Sample officials, puroks and residents for demonstrations and manual
 * testing. Not run by default — a live barangay enters its own data.
 *
 *     php artisan db:seed --class=DemoDataSeeder
 *
 * Names are placeholders; addresses are built from the configured barangay
 * so the demo data matches whatever barangay:setup was given.
 */
class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $this->seedPuroks();
        $this->seedOfficials();
        $this->seedResidents();

        $this->command->info('Demo data seeded for ' . barangay_label() . '.');
    }

    private function seedPuroks(): void
    {
        foreach (['Purok 1', 'Purok 2', 'Purok 3'] as $i => $name) {
            Purok::firstOrCreate(['name' => $name], ['sort_order' => $i, 'status' => 'active']);
        }
    }

    private function seedOfficials(): void
    {
        $officials = [
            ['name' => 'HON. JUAN DELA CRUZ', 'position' => 'Punong Barangay', 'sort_order' => 1],
            ['name' => 'HON. MARIA SANTOS', 'position' => 'Barangay Kagawad', 'sort_order' => 2],
            ['name' => 'HON. PEDRO REYES', 'position' => 'Barangay Kagawad', 'sort_order' => 3],
            ['name' => 'HON. ANA GARCIA', 'position' => 'Barangay Kagawad', 'sort_order' => 4],
            ['name' => 'HON. JOSE LIM', 'position' => 'Barangay Kagawad', 'sort_order' => 5],
            ['name' => 'HON. LUCIA CRUZ', 'position' => 'Barangay Kagawad', 'sort_order' => 6],
            ['name' => 'HON. ROBERTO TAN', 'position' => 'Barangay Kagawad', 'sort_order' => 7],
            ['name' => 'HON. ELENA MENDOZA', 'position' => 'Barangay Kagawad', 'sort_order' => 8],
            ['name' => 'HON. ANTONIO FLORES', 'position' => 'SK Chairman', 'sort_order' => 9],
            ['name' => 'MS. CARMEN VILLANUEVA', 'position' => 'Barangay Secretary', 'sort_order' => 10],
            ['name' => 'MR. MARCO BAUTISTA', 'position' => 'Barangay Treasurer', 'sort_order' => 11],
        ];

        foreach ($officials as $o) {
            Official::firstOrCreate(
                ['name' => $o['name']],
                $o + ['status' => 'active']
            );
        }
    }

    private function seedResidents(): void
    {
        $label = barangay_label();

        $residents = [
            [
                'first_name' => 'Juan', 'middle_name' => 'Santos', 'last_name' => 'Dela Cruz',
                'gender' => 'Male', 'birthdate' => '1990-03-15', 'civil_status' => 'Married',
                'contact_number' => '09171234567', 'email' => 'juan@example.test',
                'address' => '123 Rizal Street, ' . $label, 'purok' => 'Purok 1',
                'voter_status' => 'Registered', 'years_of_residency' => 10, 'status' => 'active',
            ],
            [
                'first_name' => 'Maria', 'middle_name' => 'Reyes', 'last_name' => 'Santos',
                'gender' => 'Female', 'birthdate' => '1995-07-22', 'civil_status' => 'Single',
                'contact_number' => '09281234567', 'email' => 'maria@example.test',
                'address' => '456 Mabini Street, ' . $label, 'purok' => 'Purok 2',
                'voter_status' => 'Registered', 'years_of_residency' => 5, 'status' => 'active',
            ],
            [
                'first_name' => 'Pedro', 'middle_name' => 'Cruz', 'last_name' => 'Garcia',
                'gender' => 'Male', 'birthdate' => '1985-11-08', 'civil_status' => 'Married',
                'contact_number' => '09391234567', 'email' => 'pedro@example.test',
                'address' => '789 Bonifacio Street, ' . $label, 'purok' => 'Purok 3',
                'voter_status' => 'Registered', 'years_of_residency' => 15, 'status' => 'active',
            ],
        ];

        foreach ($residents as $r) {
            Resident::firstOrCreate(
                ['first_name' => $r['first_name'], 'last_name' => $r['last_name']],
                $r
            );
        }
    }
}
