<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class CustomersOverviewChart extends ChartWidget
{
    protected ?string $heading = 'Customers Overview';


    protected ?string $pollingInterval = null;
    protected ?string $maxHeight = '200px';
    protected ?string $minHeight = '200px';
    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            '7d' => '7D',
            '30d' => '30D',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? 'today';

        // Dummy values for now; wire to real metrics later
        [$firstTime, $returning] = match ($filter) {
            '7d' => [5500, 3500],
            '30d' => [22000, 14500],
            default => [120, 80],
        };

        return [
            'datasets' => [[
                'label' => 'Customers',
                'data' => [$firstTime, $returning],
                'backgroundColor' => [
                    'rgba(34, 197, 94, 0.9)',   // success-500
                    'rgba(249, 115, 22, 0.9)',  // orange-500
                ],
                'borderWidth' => 0,
            ]],
            'labels' => ['First Time', 'Return'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [ 'display' => true, 'position' => 'bottom' ],
            ],
            'cutout' => '70%',
            'maintainAspectRatio' => false,
        ];
    }
}

