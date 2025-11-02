<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class SalesPurchaseChart extends ChartWidget
{
    protected ?string $heading = 'Sales Overview Chart';

    protected ?string $pollingInterval = null;
    protected ?string $maxHeight = '300px';
    protected ?string $minHeight = '200px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            '1d' => '1D',
            '1w' => '1W',
            '1m' => '1M',
            '3m' => '3M',
            '6m' => '6M',
            '1y' => '1Y',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? '1y';

        switch ($filter) {
            case '1d':
                $labels = ['2 am','4 am','6 am','8 am','10 am','12 pm','14 pm','16 pm','18 pm','20 pm','22 pm','24 pm'];
                $purchase = [18, 15, 8, 17, 23, 21, 7, 12, 29, 11, 19, 14];
                $sales    = [10, 11, 5, 9, 13, 10, 4, 9, 18, 8, 12, 10];
                break;
            case '1w':
                $labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                $purchase = [120, 98, 140, 110, 160, 130, 100];
                $sales    = [80, 75, 95, 85, 120, 90, 70];
                break;
            case '1m':
                $labels = ['W1','W2','W3','W4'];
                $purchase = [420, 380, 460, 410];
                $sales    = [260, 240, 300, 270];
                break;
            case '3m':
                $labels = ['M1','M2','M3'];
                $purchase = [1200, 980, 1430];
                $sales    = [760, 680, 910];
                break;
            case '6m':
                $labels = ['M1','M2','M3','M4','M5','M6'];
                $purchase = [2100, 1980, 1760, 1890, 2200, 2050];
                $sales    = [1300, 1190, 990, 1080, 1420, 1260];
                break;
            default: // 1y
                $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                $purchase = [5600, 4800, 5200, 6100, 5900, 6300, 6500, 6200, 5800, 6000, 6400, 6700];
                $sales    = [3100, 2800, 3000, 3500, 3400, 3600, 3900, 3700, 3300, 3450, 3800, 4100];
                break;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Purchase',
                    'data' => $purchase,
                    'backgroundColor' => 'rgba(251, 146, 60, 0.35)',
                    'borderColor' => 'rgba(251, 146, 60, 1)',
                    'borderWidth' => 1,
                    'stack' => 'stack-0',
                ],
                [
                    'label' => 'Total Sales',
                    'data' => $sales,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.85)',
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'borderWidth' => 1,
                    'stack' => 'stack-0',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => [ 'stacked' => true ],
                'y' => [ 'stacked' => true, 'beginAtZero' => true ],
            ],
            'plugins' => [
                'legend' => [ 'display' => true ],
                'tooltip' => [ 'enabled' => true ],
            ],
        ];
    }
}

