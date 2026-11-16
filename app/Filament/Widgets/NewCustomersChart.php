<?php

namespace App\Filament\Widgets;

use App\Services\Report;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class NewCustomersChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Khách hàng mới';

    protected ?string $description = 'Khách có đơn hàng đầu tiên trong kỳ';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $s = Report::newCustomers($this->pageFilters['period'] ?? '30');

        return [
            'datasets' => [['label' => 'Khách mới', 'data' => array_column($s, 'count'), 'backgroundColor' => '#2563eb', 'borderRadius' => 4]],
            'labels' => array_column($s, 'label'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
