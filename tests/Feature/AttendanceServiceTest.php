<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_out_finds_today_attendance_stored_as_midnight_datetime(): void
    {
        Carbon::setTestNow('2026-06-28 10:00:00');

        $user = User::factory()->create([
            'pin' => '123456',
            'is_active' => true,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => now()->subHours(2),
            'clock_in_method' => 'pin',
        ]);

        $attendance = app(AttendanceService::class)->clockOut('123456');

        $this->assertSame($user->id, $attendance->user_id);
        $this->assertTrue($attendance->clock_out->equalTo(now()));
    }
}
