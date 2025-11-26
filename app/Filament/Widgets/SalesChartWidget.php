<?php

namespace App\Filament\Widgets;

use App\Services\Reports\SalesChartService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected ?string $heading = 'Net Sales';

    public ?string $startDate = null;
    public ?string $endDate = null;
    public string $granularity = 'daily';

    protected SalesChartService $salesChartService;

    public function __construct($id = null)
    {
        $this->salesChartService = app(SalesChartService::class);
    }

    public function mount(): void
    {
        $this->startDate ??= now()->startOfMonth()->toDateString();
        $this->endDate ??= now()->endOfMonth()->toDateString();
    }

    /**
     * Filament v4: define the chart type
     */
    public function getType(): string
    {
        return 'line'; // could be 'bar', 'pie', 'doughnut', etc.
    }

    /**
     * Filament v4: supply chart data (labels + datasets)
     */
    protected function getData(): array
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        $series = $this->salesChartService->getNetSalesSeries(
            $start, 
            $end, 
            $this->granularity
        );

        return [
            'labels' => $series['labels'],
            'datasets' => [
                [
                    'label' => 'Net Sales',
                    'data' => $series['data'],
                    'borderColor' => '#F97316', // orange (Tailwind "orange-500")
                    'backgroundColor' => 'rgba(249, 115, 22, 0.15)',
                    'tension' => 0.3, // smoother line (optional)
                ],
            ],
        ];
    }
}
