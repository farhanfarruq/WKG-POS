<?php

namespace Tests\Feature;

use App\Models\WorkShift;
use App\Seeders\WorkShiftSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkShiftSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_three_24_hour_work_shifts_without_duplicates(): void
    {
        $this->seed(WorkShiftSeeder::class);
        $this->seed(WorkShiftSeeder::class);

        $this->assertSame(3, WorkShift::count());
        $this->assertDatabaseHas('work_shifts', [
            'name' => 'Shift Pagi',
            'start_time' => '06:00:00',
            'end_time' => '14:00:00',
        ]);
        $this->assertDatabaseHas('work_shifts', [
            'name' => 'Shift Sore',
            'start_time' => '14:00:00',
            'end_time' => '22:00:00',
        ]);
        $this->assertDatabaseHas('work_shifts', [
            'name' => 'Shift Malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
        ]);
    }
}
