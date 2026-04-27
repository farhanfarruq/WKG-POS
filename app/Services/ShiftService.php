<?php

namespace App\Services;

use App\Models\Shift;
use App\Repositories\Interfaces\ShiftRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ShiftService
{
    public function __construct(
        private readonly ShiftRepositoryInterface $shiftRepository,
    ) {}

    public function openShift(float $openingCash): Shift
    {
        $existing = $this->shiftRepository->findOpenShift();
        abort_if($existing, 422, 'Shift sedang aktif. Tutup shift terlebih dahulu.');

        return $this->shiftRepository->create([
            'opened_by'    => Auth::id(),
            'opening_cash' => $openingCash,
            'opened_at'    => now(),
            'status'       => 'open',
        ]);
    }

    public function closeShift(float $closingCash, string $notes = ''): Shift
    {
        $shift = $this->shiftRepository->findOpenShift();
        abort_unless($shift, 422, 'Tidak ada shift yang aktif.');

        // Calculate expected cash
        $cashSales    = $shift->totalCashSales();
        $expectedCash = $shift->opening_cash + $cashSales;
        $difference   = $closingCash - $expectedCash;

        return $this->shiftRepository->close($shift->id, [
            'closing_cash'    => $closingCash,
            'expected_cash'   => $expectedCash,
            'cash_difference' => $difference,
            'closed_by'       => Auth::id(),
            'closed_at'       => now(),
            'notes'           => $notes,
            'status'          => 'closed',
        ]);
    }

    public function getCurrentShift(): ?Shift
    {
        return $this->shiftRepository->findOpenShift();
    }
}
