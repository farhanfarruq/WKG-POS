<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use HasApiResponse;

    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function clockIn(Request $request): JsonResponse
    {
        $request->validate(['pin' => ['required', 'string', 'digits:6']]);

        $attendance = $this->attendanceService->clockIn($request->pin);

        return $this->successResponse([
            'employee'     => $attendance->user->name,
            'clock_in'     => $attendance->clock_in->format('H:i:s'),
            'date'         => $attendance->date->format('d/m/Y'),
            'is_late'      => $attendance->is_late,
            'late_minutes' => $attendance->late_minutes,
            'shift_name'   => $attendance->workShift ? $attendance->workShift->name : null,
            'shift_start'  => $attendance->workShift ? \Carbon\Carbon::parse($attendance->workShift->start_time)->format('H:i') : null,
        ], 'Absen masuk berhasil.');
    }

    public function clockOut(Request $request): JsonResponse
    {
        $request->validate(['pin' => ['required', 'string', 'digits:6']]);

        $attendance = $this->attendanceService->clockOut($request->pin);

        return $this->successResponse([
            'employee'    => $attendance->user->name,
            'clock_out'   => $attendance->clock_out->format('H:i:s'),
            'duration'    => $attendance->workDurationMinutes() . ' menit',
        ], 'Absen pulang berhasil.');
    }
}
