<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\CloseShiftRequest;
use App\Http\Requests\Shift\OpenShiftRequest;
use App\Models\Shift;
use App\Services\ShiftService;
use App\Traits\HasApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class ShiftController extends Controller
{
    use HasApiResponse, AuthorizesRequests;

    public function __construct(private readonly ShiftService $shiftService) {}

    public function current(): JsonResponse
    {
        $shift = $this->shiftService->getCurrentShift();

        return $this->successResponse($shift
            ? $shift->load('openedBy')
            : null,
            $shift ? 'Shift aktif.' : 'Tidak ada shift aktif.'
        );
    }

    public function open(OpenShiftRequest $request): JsonResponse
    {
        $this->authorize('open', Shift::class);

        $shift = $this->shiftService->openShift($request->validated('opening_cash'));

        return $this->successResponse($shift->load('openedBy'), 'Shift dibuka.', 201);
    }

    public function close(CloseShiftRequest $request): JsonResponse
    {
        $shift = $this->shiftService->getCurrentShift();
        abort_unless($shift, 422, 'Tidak ada shift aktif.');

        $this->authorize('close', $shift);

        $closed = $this->shiftService->closeShift(
            $request->validated('closing_cash'),
            $request->validated('notes') ?? ''
        );

        return $this->successResponse($closed->load(['openedBy', 'closedBy']), 'Shift ditutup.');
    }

    public function summary(Shift $shift): JsonResponse
    {
        $this->authorize('viewReports', $shift);

        $orders = $shift->orders()->with('payments')->where('status', '!=', 'cancelled')->get();

        $summary = [
            'shift_id'       => $shift->id,
            'opened_by'      => $shift->openedBy->name,
            'opened_at'      => $shift->opened_at,
            'closed_at'      => $shift->closed_at,
            'opening_cash'   => $shift->opening_cash,
            'closing_cash'   => $shift->closing_cash,
            'expected_cash'  => $shift->expected_cash,
            'cash_difference'=> $shift->cash_difference,
            'total_orders'   => $orders->count(),
            'total_sales'    => $orders->sum('total'),
            'by_payment'     => $orders->flatMap->payments->groupBy('method')
                ->map->sum('amount'),
        ];

        return $this->successResponse($summary);
    }
}
