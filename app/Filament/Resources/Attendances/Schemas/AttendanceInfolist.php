<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class AttendanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Karyawan')
                    ->description('Ringkasan utama record absensi.')
                    ->icon(Heroicon::OutlinedUser)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Karyawan')
                            ->placeholder('-'),
                        TextEntry::make('date')
                            ->label('Tanggal Absensi')
                            ->date('d M Y'),
                        TextEntry::make('workShift.name')
                            ->label('Jadwal Kerja')
                            ->placeholder('-')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('shift.id')
                            ->label('Shift POS')
                            ->state(fn ($record): ?string => $record->shift_id ? 'Shift #' . $record->shift_id : null)
                            ->placeholder('-')
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make('Detail Kehadiran')
                    ->description('Jam masuk, jam keluar, dan status kedisiplinan.')
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        TextEntry::make('clock_in')
                            ->label('Jam Masuk')
                            ->time('H:i')
                            ->timeTooltip('H:i:s')
                            ->placeholder('-'),
                        TextEntry::make('clock_out')
                            ->label('Jam Pulang')
                            ->time('H:i')
                            ->timeTooltip('H:i:s')
                            ->placeholder('-'),
                        TextEntry::make('clock_in_method')
                            ->label('Metode Absen')
                            ->formatStateUsing(fn (?string $state): string => filled($state)
                                ? str($state)->replace(['-', '_'], ' ')->title()->toString()
                                : '-')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('attendance_status')
                            ->label('Status Kehadiran')
                            ->state(fn ($record): string => $record->is_late ? 'Terlambat' : 'Tepat Waktu')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'Terlambat' ? 'danger' : 'success')
                            ->icon(fn (string $state): Heroicon => $state === 'Terlambat'
                                ? Heroicon::OutlinedExclamationTriangle
                                : Heroicon::OutlinedCheckCircle),
                        TextEntry::make('late_minutes')
                            ->label('Keterlambatan')
                            ->state(fn ($record): string => $record->is_late
                                ? (($record->late_minutes ?? 0) . ' menit')
                                : '0 menit')
                            ->badge()
                            ->color(fn ($record): string => $record->is_late ? 'warning' : 'success'),
                        TextEntry::make('work_duration')
                            ->label('Durasi Kerja')
                            ->state(fn ($record): ?string => $record->workDurationMinutes()
                                ? $record->workDurationMinutes() . ' menit'
                                : null)
                            ->placeholder('-'),
                    ])
                    ->columns(3),

                Section::make('Catatan & Metadata')
                    ->description('Informasi tambahan dan riwayat perubahan data.')
                    ->icon(Heroicon::OutlinedInformationCircle)
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('Tidak ada catatan.')
                            ->columnSpanFull(),
                        IconEntry::make('is_late')
                            ->label('Terlambat')
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->since()
                            ->dateTimeTooltip('d M Y H:i:s')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->since()
                            ->dateTimeTooltip('d M Y H:i:s')
                            ->placeholder('-'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }
}
