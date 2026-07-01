<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Carbon;

class AttendanceService
{
    public function clockIn(string $pin): Attendance
    {
        $user = User::where('pin', $pin)->where('is_active', true)->first();
        abort_unless($user, 422, 'PIN tidak valid.');

        $today = Carbon::today();
        $existing = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();

        abort_if($existing && $existing->clock_in, 422, 'Sudah absen masuk hari ini.');

        $now = now();
        $workShift = $user->workShifts()->first();
        
        $isLate = false;
        $lateMinutes = null;
        $workShiftId = null;

        if ($workShift) {
            $workShiftId = $workShift->id;
            $startTimeStr = $workShift->start_time instanceof \Carbon\Carbon 
                ? $workShift->start_time->format('H:i:s') 
                : $workShift->start_time;
            
            $shiftStartTime = Carbon::parse($today->toDateString() . ' ' . $startTimeStr);
            
            if ($now->greaterThan($shiftStartTime)) {
                $isLate = true;
                $lateMinutes = $now->diffInMinutes($shiftStartTime);
            }
        }

        $attendance = $existing ?? new Attendance([
            'user_id' => $user->id,
            'date' => $today->toDateString(),
        ]);

        $attendance->fill([
            'clock_in' => $now,
            'clock_in_method' => 'pin',
            'work_shift_id' => $workShiftId,
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
        ])->save();

        return $attendance->fresh(['user', 'workShift']);
    }

    public function clockOut(string $pin): Attendance
    {
        $user = User::where('pin', $pin)->where('is_active', true)->first();
        abort_unless($user, 422, 'PIN tidak valid.');

        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();

        abort_unless($attendance && $attendance->clock_in, 422, 'Belum absen masuk hari ini.');
        abort_if($attendance->clock_out, 422, 'Sudah absen pulang hari ini.');

        $attendance->update(['clock_out' => now()]);
        return $attendance->fresh('user');
    }
}
