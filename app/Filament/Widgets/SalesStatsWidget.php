<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $today = Order::where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('total');

        $yesterday = Order::where('status', 'completed')
            ->whereDate('created_at', today()->subDay())
            ->sum('total');

        $todayCount = Order::where('status', 'completed')
            ->whereDate('created_at', today())
            ->count();

        $growthPercent = $yesterday > 0
            ? round((($today - $yesterday) / $yesterday) * 100, 1)
            : 100;

        return [
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($today, 0, ',', '.'))
                ->description($growthPercent >= 0 ? "+{$growthPercent}% dari kemarin" : "{$growthPercent}% dari kemarin")
                ->descriptionIcon($growthPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthPercent >= 0 ? 'success' : 'danger')
                ->chart([$yesterday, $today]),

            Stat::make('Transaksi Hari Ini', $todayCount . ' transaksi')
                ->description('Total order selesai')
                ->color('info')
                ->chart([$todayCount > 0 ? max($todayCount - 2, 0) : 0, $todayCount]),

            Stat::make('Rata-rata per Transaksi', $todayCount > 0 ? 'Rp ' . number_format($today / $todayCount, 0, ',', '.') : 'Rp 0')
                ->description('Nilai rata-rata tiap order selesai')
                ->color('warning')
                ->chart([
                    $todayCount > 0 ? round(($today / $todayCount) * 0.85) : 0,
                    $todayCount > 0 ? round($today / $todayCount) : 0,
                ]),
        ];
    }
}
