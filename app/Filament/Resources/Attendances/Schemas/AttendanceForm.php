<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('shift_id')
                    ->relationship('shift', 'id'),
                Select::make('work_shift_id')
                    ->relationship('workShift', 'name'),
                DatePicker::make('date')
                    ->required(),
                DateTimePicker::make('clock_in'),
                DateTimePicker::make('clock_out'),
                TextInput::make('clock_in_method')
                    ->required()
                    ->default('pin'),
                Toggle::make('is_late')
                    ->required(),
                TextInput::make('late_minutes')
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
