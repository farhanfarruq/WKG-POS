<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected ?string $heading = 'Penjualan 7 Hari Terakhir';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected function getData(): array
    {
        $dateRange = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            return [
                'date'  => $date->format('d/m'),
                'total' => Order::where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('total'),
            ];
        });

        return [
            'datasets' => [[
                'label'           => 'Total Penjualan (Rp)',
                'data'            => $dateRange->pluck('total')->toArray(),
                'backgroundColor' => 'rgba(93, 64, 55, 0.18)',
                'borderColor'     => '#5d4037',
                'borderWidth'     => 2,
                'borderRadius'    => 10,
            ]],
            'labels' => $dateRange->pluck('date')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(130, 116, 112, 0.12)',
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
