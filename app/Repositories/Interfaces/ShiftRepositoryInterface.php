<?php

namespace App\Repositories\Interfaces;

use App\Models\Shift;

interface ShiftRepositoryInterface
{
    public function findOpenShift(): ?Shift;
    public function findById(int $id): ?Shift;
    public function create(array $data): Shift;
    public function close(int $id, array $data): Shift;
}
