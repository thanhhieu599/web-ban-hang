<?php

namespace App\Filament\Widgets;

use App\Services\Report;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SalesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Doanh số và lợi nhuận gộp';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $s = Report::series($this->pageFilters['period'] ?? '30');

        return [
            'datasets' => [
                ['label' => 'Doanh thu', 'data' => array_column($s, 'revenue'), 'borderColor' => '#e0121f', 'backgroundColor' => 'rgba(224,18,31,.08)', 'fill' => true, 'tension' => .35],
                ['label' => 'Lợi nhuận gộp', 'data' => array_column($s, 'profit'), 'borderColor' => '#0f766e', 'backgroundColor' => 'rgba(15,118,110,.08)', 'fill' => true, 'tension' => .35],
            ],
            'labels' => array_column($s, 'label'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
