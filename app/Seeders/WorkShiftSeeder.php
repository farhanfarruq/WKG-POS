<?php

namespace App\Seeders;

use App\Models\WorkShift;
use Illuminate\Database\Seeder;

class WorkShiftSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->shifts() as $shift) {
            WorkShift::updateOrCreate(
                ['name' => $shift['name']],
                $shift + ['description' => 'Shift kerja operasional 24 jam']
            );
        }
    }

    private function shifts(): array
    {
        return [
            ['name' => 'Shift Pagi', 'start_time' => '06:00:00', 'end_time' => '14:00:00'],
            ['name' => 'Shift Sore', 'start_time' => '14:00:00', 'end_time' => '22:00:00'],
            ['name' => 'Shift Malam', 'start_time' => '22:00:00', 'end_time' => '06:00:00'],
        ];
    }
}
