<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Layout;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?string $label = 'Karyawan';
    protected static ?string $pluralLabel = 'Karyawan';
    protected static ?string $navigationLabel = 'Karyawan';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Layout\Section::make('Profil Karyawan')
                ->description('Informasi dasar akun dan identitas staf operasional.')
                ->schema([
                    Forms\Components\FileUpload::make('avatar')
                        ->label('Foto Profil')
                        ->disk('local')
                        ->directory('avatars')
                        ->avatar()
                        ->imageEditor()
                        ->circleCropper()
                        ->imageEditorAspectRatioOptions(['1:1'])
                        ->preventFilePathTampering()
                        ->getUploadedFileUsing(function (BaseFileUpload $component, string $file): ?array {
                            $storage = $component->getDisk();

                            if (! $storage->exists($file)) {
                                return null;
                            }

                            return [
                                'name' => basename($file),
                                'size' => $storage->size($file),
                                'type' => $storage->mimeType($file),
                                'url' => route('user-avatars.show', ['path' => $file]),
                            ];
                        })
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->placeholder(fn (string $context): string => $context === 'edit' ? 'Password sudah tersimpan' : '')
                        ->helperText(fn (string $context): ?string => $context === 'edit'
                            ? 'Password lama tidak ditampilkan. Isi field ini hanya jika ingin mengganti password.'
                            : null)
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create'),
                    Forms\Components\TextInput::make('phone'),
                    Forms\Components\TextInput::make('pin')
                        ->label('Attendance PIN')
                        ->maxLength(6)
                        ->numeric()
                        ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
                        ->helperText('PIN hanya ditampilkan ke super admin dan bisa langsung diganti dari sini.'),
                ])
                ->columns(2),

            Layout\Section::make('Akses & Jadwal')
                ->description('Atur role, shift kerja, dan status aktif pengguna.')
                ->schema([
                    Forms\Components\Select::make('roles')
                        ->multiple()
                        ->relationship('roles', 'name')
                        ->preload(),
                    Forms\Components\Select::make('workShifts')
                        ->multiple()
                        ->relationship('workShifts', 'name')
                        ->preload()
                        ->label('Jam Shift Karyawan'),
                    Forms\Components\Toggle::make('is_active')->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('roles.name')->badge()->label('Role'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
