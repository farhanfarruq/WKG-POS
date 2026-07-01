<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?string $label = 'Shift Kasir';
    protected static ?string $pluralLabel = 'Shift Kasir';
    protected static ?string $navigationLabel = 'Shift Kasir';

    public static function canCreate(): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('openedBy.name')->label('Cashier'),
                Tables\Columns\TextColumn::make('opened_at')->label('Start time')->dateTime(),
                Tables\Columns\TextColumn::make('closed_at')->label('End time')->dateTime(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('opening_cash')->money('IDR'),
                Tables\Columns\TextColumn::make('closing_cash')->money('IDR'),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Total Sales')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->totalSales()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ]),
            ])
            ->defaultSort('opened_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShifts::route('/'),
        ];
    }
}
