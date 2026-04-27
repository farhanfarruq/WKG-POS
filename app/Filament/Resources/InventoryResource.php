<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\RawMaterial;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Layout;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryResource extends Resource
{
    protected static ?string $model = RawMaterial::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';
    protected static ?string $label = 'Bahan Baku';
    protected static ?string $pluralLabel = 'Bahan Baku';
    protected static ?string $navigationLabel = 'Bahan Baku';

    protected static function formatStockValue(mixed $value): string
    {
        $number = (float) $value;

        if (fmod($number, 1.0) === 0.0) {
            return number_format($number, 0, ',', '.');
        }

        return rtrim(rtrim(number_format($number, 3, ',', '.'), '0'), ',');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')->required(),

            Forms\Components\Select::make('unit_id')
                ->relationship('unit', 'name')
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('symbol')->required(),
                ]),

            Forms\Components\TextInput::make('sku')->nullable()->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('current_stock')
                ->label('Stok Saat Ini')
                ->numeric()
                ->default(0)
                ->required(),

            Forms\Components\TextInput::make('min_stock')
                ->label('Stok Minimum (Reorder Point)')
                ->numeric()
                ->default(0)
                ->required(),

            Forms\Components\TextInput::make('cost_per_unit')
                ->label('Harga per Unit')
                ->numeric()
                ->prefix('Rp')
                ->default(0),

            Forms\Components\Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('unit.symbol')->label('Satuan'),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok')
                    ->formatStateUsing(fn ($state) => static::formatStockValue($state)),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Stok Min')
                    ->formatStateUsing(fn ($state) => static::formatStockValue($state)),
                Tables\Columns\IconColumn::make('is_low_stock')
                    ->label('Low Stock')
                    ->state(fn ($record) => $record->isLowStock())
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('cost_per_unit')->money('IDR'),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn ($q) => $q->whereColumn('current_stock', '<=', 'min_stock'))
                    ->label('Low Stock Only'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('adjust_stock')
                    ->label('Adjust Stok')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->form([
                        Forms\Components\TextInput::make('actual_stock')
                            ->label('Stok Aktual')
                            ->required()
                            ->numeric()
                            ->inputMode('decimal')
                            ->step('0.001')
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? str_replace(',', '.', $state) : $state),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan'),
                    ])
                    ->action(function (RawMaterial $record, array $data) {
                        app(\App\Services\InventoryService::class)
                            ->stockOpname($record->id, (float) $data['actual_stock'], $data['notes'] ?? '');
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventory::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit'   => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
