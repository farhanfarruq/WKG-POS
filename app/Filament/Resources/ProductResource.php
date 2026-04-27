<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Layout;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static string | \UnitEnum | null $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Produk';
    protected static ?string $pluralLabel = 'Produk';
    protected static ?string $navigationLabel = 'Produk';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Layout\Section::make('Informasi Produk')
                ->description('Atur identitas, kategori, dan deskripsi produk yang tampil di POS.')
                ->schema([
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, $state) => $set('slug', Str::slug($state))),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true)
                    ->nullable(),

                Forms\Components\Textarea::make('description')
                    ->nullable()
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('products')
                    ->nullable()
                    ->columnSpanFull(),
            ])->columns(2),

            Layout\Section::make('Harga')
                ->description('Tentukan harga jual, harga modal, dan urutan tampil produk.')
                ->schema([
                Forms\Components\TextInput::make('price')
                    ->label('Harga Jual')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
 
                Forms\Components\TextInput::make('cost_price')
                    ->label('Harga Modal')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
 
                Forms\Components\TextInput::make('sort_order')
                    ->integer()
                    ->default(0),
            ])->columns(3),
 
            Layout\Section::make('Status')
                ->description('Kontrol apakah produk aktif dan bisa dijual di kasir.')
                ->schema([
                Forms\Components\Toggle::make('is_available')
                    ->label('Tersedia di POS')
                    ->default(true),
 
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])->columns(2),
 
            Layout\Section::make('Modifier Groups')
                ->description('Hubungkan produk dengan grup modifier yang sudah tersedia.')
                ->schema([
                Forms\Components\Select::make('modifierGroups')
                    ->relationship('modifierGroups', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]),

            Layout\Section::make('Resep (BOM)')
                ->description('Tentukan bahan baku yang dikurangi stoknya setiap kali produk ini terjual.')
                ->schema([
                    Forms\Components\Repeater::make('bomRecipes')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('raw_material_id')
                                ->label('Bahan Baku')
                                ->relationship('rawMaterial', 'name')
                                ->required()
                                ->searchable()
                                ->columnSpan(3),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Jumlah')
                                ->numeric()
                                ->required()
                                ->columnSpan(2),
                            Forms\Components\Select::make('unit_id')
                                ->label('Satuan')
                                ->relationship('unit', 'name')
                                ->required()
                                ->columnSpan(2),
                        ])
                        ->columns(7)
                        ->defaultItems(0)
                        ->reorderableWithButtons()
                        ->itemLabel(fn (array $state): ?string => (\App\Models\RawMaterial::find($state['raw_material_id'] ?? null)?->name ?? 'Bahan Baku')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->badge()->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bom_recipes_count')
                    ->label('Bahan')
                    ->counts('bomRecipes')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_available')->boolean()->label('Tersedia'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_available'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
