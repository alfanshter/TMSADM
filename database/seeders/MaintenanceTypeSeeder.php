<?php
namespace Database\Seeders;
use App\Models\MaintenanceType;
use Illuminate\Database\Seeder;

class MaintenanceTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Cleaning Critical',
            'Just Cleaning',
            'Replacement Part',
            'Preventive (PM)',
        ];

        foreach ($types as $type) {
            MaintenanceType::create(['name' => $type]);
        }
    }
}

