<?php

namespace App\Filament\Resources\WorkShifts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class WorkShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Shift Pagi'),
                TimePicker::make('start_time')
                    ->required()
                    ->label('Jam Mulai'),
                TimePicker::make('end_time')
                    ->required()
                    ->label('Jam Selesai'),
                Select::make('users')
                    ->multiple()
                    ->relationship('users', 'name')
                    ->preload()
                    ->label('Karyawan yang bertugas')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ]);
    }
}
