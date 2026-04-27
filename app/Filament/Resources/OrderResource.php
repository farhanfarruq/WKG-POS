<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static string | \UnitEnum | null $navigationGroup = 'Operations';
    protected static ?string $label = 'Pesanan';
    protected static ?string $pluralLabel = 'Pesanan';
    protected static ?string $navigationLabel = 'Pesanan';

    public static function canCreate(): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('cashier.name')->label('Kasir'),
                Tables\Columns\TextColumn::make('order_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($record) => $record->status->color()),
                Tables\Columns\TextColumn::make('total')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(\App\Enums\OrderStatus::class),
                Tables\Filters\Filter::make('today')
                    ->query(fn ($q) => $q->whereDate('created_at', today())),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pesanan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('order_number')->label('Nomor Pesanan'),
                                TextEntry::make('created_at')->label('Waktu Pesanan')->dateTime(),
                                TextEntry::make('status')->badge()->color(fn ($state) => $state->color()),
                                TextEntry::make('cashier.name')->label('Kasir'),
                                TextEntry::make('customer_name')->label('Nama Pelanggan')->placeholder('-'),
                                TextEntry::make('order_type')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state->label()),
                            ]),
                    ]),
                
                Section::make('Daftar Item')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('product_name')->label('Produk'),
                                        TextEntry::make('quantity')->label('Jumlah'),
                                        TextEntry::make('unit_price')->label('Harga Satuan')->money('IDR'),
                                        TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                                    ]),
                            ])
                            ->columns(1),
                    ]),

                Section::make('Ringkasan Pembayaran')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subtotal')->money('IDR'),
                                TextEntry::make('tax_amount')->label('Pajak')->money('IDR'),
                                TextEntry::make('total')->money('IDR')->weight('bold'),
                                TextEntry::make('paid_amount')->label('Dibayar')->money('IDR'),
                                TextEntry::make('change_amount')->label('Kembalian')->money('IDR'),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }
}
