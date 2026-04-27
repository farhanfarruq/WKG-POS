<?php

namespace App\Filament\Widgets;

use App\Models\RawMaterial;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStockWidget extends TableWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Bahan Baku Perlu Restock';

    protected function formatStock(mixed $value): string
    {
        $number = (float) $value;

        if (fmod($number, 1.0) === 0.0) {
            return number_format($number, 0, ',', '.');
        }

        return rtrim(rtrim(number_format($number, 3, ',', '.'), '0'), ',');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RawMaterial::query()
                    ->whereColumn('current_stock', '<=', 'min_stock')
                    ->where('is_active', true)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Bahan'),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->formatStateUsing(fn ($state) => $this->formatStock($state)),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Stok Minimum')
                    ->formatStateUsing(fn ($state) => $this->formatStock($state)),
                Tables\Columns\TextColumn::make('unit.symbol')->label('Satuan'),
            ]);
    }
}
