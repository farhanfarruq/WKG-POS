<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Layout;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static string | \UnitEnum | null $navigationGroup = 'Inventory';
    protected static ?string $label = 'Purchase Order';
    protected static ?string $pluralLabel = 'Purchase Order';
    protected static ?string $navigationLabel = 'Purchase Order';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('supplier_id')
                ->relationship('supplier', 'name')
                ->required(),
            Forms\Components\TextInput::make('po_number')
                ->label('PO Number')
                ->placeholder('Otomatis')
                ->disabled()
                ->dehydrated(false)
                ->helperText('Nomor PO akan dibuat otomatis setelah disimpan.'),
            Forms\Components\Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'ordered' => 'Ordered',
                    'received' => 'Received',
                    'cancelled' => 'Cancelled',
                ])
                ->default('draft')
                ->required(),
            Forms\Components\TextInput::make('total_amount')
                ->numeric()
                ->prefix('Rp')
                ->default(0)
                ->readOnly(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),

            Layout\Section::make('Items')
                ->description('Daftar bahan baku yang akan dibeli dari supplier.')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('raw_material_id')
                                ->relationship('rawMaterial', 'name')
                                ->required()
                                ->searchable()
                                ->columnSpan(3),
                            Forms\Components\TextInput::make('quantity_ordered')
                                ->label('Qty Dipesan')
                                ->numeric()
                                ->inputMode('decimal')
                                ->step('0.001')
                                ->default(1)
                                ->required()
                                ->live()
                                ->dehydrateStateUsing(fn ($state) => is_string($state) ? str_replace(',', '.', $state) : $state)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $quantity = (float) str_replace(',', '.', (string) ($get('quantity_ordered') ?? 0));
                                    $cost = (float) str_replace(',', '.', (string) ($get('unit_cost') ?? 0));
                                    $set('subtotal', $quantity * $cost);
                                })
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('unit_cost')
                                ->label('Harga Satuan')
                                ->numeric()
                                ->inputMode('decimal')
                                ->step('0.01')
                                ->prefix('Rp')
                                ->default(0)
                                ->required()
                                ->live()
                                ->dehydrateStateUsing(fn ($state) => is_string($state) ? str_replace(',', '.', $state) : $state)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $quantity = (float) str_replace(',', '.', (string) ($get('quantity_ordered') ?? 0));
                                    $cost = (float) str_replace(',', '.', (string) ($get('unit_cost') ?? 0));
                                    $set('subtotal', $quantity * $cost);
                                })
                                ->columnSpan(3),
                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly()
                                ->columnSpan(2),
                        ])
                        ->columns(10)
                        ->defaultItems(1)
                        ->reorderableWithButtons()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $items = $get('items');
                            $total = 0;
                            foreach ($items as $item) {
                                $quantity = (float) str_replace(',', '.', (string) ($item['quantity_ordered'] ?? 0));
                                $cost = (float) str_replace(',', '.', (string) ($item['unit_cost'] ?? 0));
                                $total += $quantity * $cost;
                            }
                            $set('total_amount', $total);
                        })
                        ->itemLabel(fn (array $state): ?string => (\App\Models\RawMaterial::find($state['raw_material_id'] ?? null)?->name ?? 'Item')),
                ]),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')->searchable(),
                Tables\Columns\TextColumn::make('supplier.name'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('total_amount')->money('IDR'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
