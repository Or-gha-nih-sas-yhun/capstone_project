<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Certificate;
use App\Models\Official;
use App\Models\Resident;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Users (without resident link first)
        User::create([
            'username' => 'admin',
            'email' => 'admin@brgy-pili.gov.ph',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        User::create([
            'username' => 'staff1',
            'email' => 'staff@brgy-pili.gov.ph',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'status' => 'active',
        ]);

        // 2. Seed Certificates
        $certs = [
            [
                'name' => 'Barangay Clearance',
                'description' => 'General clearance issued by the barangay for various purposes',
                'category' => 'Clearance',
                'fee' => 50.00,
                'processing_days' => 1,
                'template_file' => 'certificate_clearance.php',
                'requirements' => 'Valid ID, Proof of residency',
                'status' => 'active'
            ],
            [
                'name' => 'Certificate of Residency',
                'description' => 'Certifies that the person is a legitimate resident of Barangay Pili',
                'category' => 'Certification',
                'fee' => 50.00,
                'processing_days' => 1,
                'template_file' => 'certificate_residency.php',
                'requirements' => 'Valid ID',
                'status' => 'active'
            ],
            [
                'name' => 'Certificate of Indigency',
                'description' => 'Certifies that the resident belongs to the indigent sector',
                'category' => 'Social Services',
                'fee' => 0.00,
                'processing_days' => 1,
                'template_file' => 'certificate_indigency.php',
                'requirements' => 'Valid ID, Proof of indigency',
                'status' => 'active'
            ],
            [
                'name' => 'Business Clearance',
                'description' => 'Clearance required for business permit applications',
                'category' => 'Business',
                'fee' => 100.00,
                'processing_days' => 3,
                'template_file' => 'certificate_clearance.php',
                'requirements' => 'Valid ID, Business documents',
                'status' => 'active'
            ],
            [
                'name' => 'Certificate of Good Moral Character',
                'description' => 'Certifies good moral standing in the community',
                'category' => 'Certification',
                'fee' => 50.00,
                'processing_days' => 1,
                'template_file' => 'certificate_moral.php',
                'requirements' => 'Valid ID',
                'status' => 'active'
            ],
            [
                'name' => 'First Time Jobseeker Certificate',
                'description' => 'For first-time jobseekers as per RA 11261',
                'category' => 'Employment',
                'fee' => 0.00,
                'processing_days' => 1,
                'template_file' => 'certificate.php',
                'requirements' => 'Valid ID, Barangay Certificate',
                'status' => 'active'
            ]
        ];

        foreach ($certs as $c) {
            Certificate::create($c);
        }

        // 3. Seed Officials
        $officials = [
            ['name' => 'HON. JUAN DELA CRUZ', 'position' => 'Barangay Captain', 'sort_order' => 1, 'status' => 'active'],
            ['name' => 'HON. MARIA SANTOS', 'position' => 'Barangay Kagawad', 'sort_order' => 2, 'status' => 'active'],
            ['name' => 'HON. PEDRO REYES', 'position' => 'Barangay Kagawad', 'sort_order' => 3, 'status' => 'active'],
            ['name' => 'HON. ANA GARCIA', 'position' => 'Barangay Kagawad', 'sort_order' => 4, 'status' => 'active'],
            ['name' => 'HON. JOSE LIM', 'position' => 'Barangay Kagawad', 'sort_order' => 5, 'status' => 'active'],
            ['name' => 'HON. LUCIA CRUZ', 'position' => 'Barangay Kagawad', 'sort_order' => 6, 'status' => 'active'],
            ['name' => 'HON. ROBERTO TAN', 'position' => 'Barangay Kagawad', 'sort_order' => 7, 'status' => 'active'],
            ['name' => 'HON. ELENA MENDOZA', 'position' => 'Barangay Kagawad', 'sort_order' => 8, 'status' => 'active'],
            ['name' => 'HON. ANTONIO FLORES', 'position' => 'SK Chairman', 'sort_order' => 9, 'status' => 'active'],
            ['name' => 'MS. CARMEN VILLANUEVA', 'position' => 'Barangay Secretary', 'sort_order' => 10, 'status' => 'active'],
            ['name' => 'MR. MARCO BAUTISTA', 'position' => 'Barangay Treasurer', 'sort_order' => 11, 'status' => 'active'],
        ];

        foreach ($officials as $o) {
            Official::create($o);
        }

        // 4. Seed Residents and link users
        $residents = [
            [
                'first_name' => 'Juan',
                'middle_name' => 'Santos',
                'last_name' => 'Dela Cruz',
                'gender' => 'Male',
                'birthdate' => '1990-03-15',
                'civil_status' => 'Married',
                'contact_number' => '09171234567',
                'email' => 'juan@email.com',
                'address' => '123 Rizal Street, Barangay Pili',
                'purok' => 'Purok 1',
                'voter_status' => 'Registered',
                'years_of_residency' => 10,
                'status' => 'active'
            ],
            [
                'first_name' => 'Maria',
                'middle_name' => 'Reyes',
                'last_name' => 'Santos',
                'gender' => 'Female',
                'birthdate' => '1995-07-22',
                'civil_status' => 'Single',
                'contact_number' => '09281234567',
                'email' => 'maria@email.com',
                'address' => '456 Mabini Street, Barangay Pili',
                'purok' => 'Purok 2',
                'voter_status' => 'Registered',
                'years_of_residency' => 5,
                'status' => 'active'
            ],
            [
                'first_name' => 'Pedro',
                'middle_name' => 'Cruz',
                'last_name' => 'Garcia',
                'gender' => 'Male',
                'birthdate' => '1985-11-08',
                'civil_status' => 'Married',
                'contact_number' => '09391234567',
                'email' => 'pedro@email.com',
                'address' => '789 Bonifacio Street, Barangay Pili',
                'purok' => 'Purok 3',
                'voter_status' => 'Registered',
                'years_of_residency' => 15,
                'status' => 'active'
            ]
        ];

        foreach ($residents as $r) {
            Resident::create($r);
        }
    }
}
