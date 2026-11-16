<?php

namespace App\Filament\Widgets;

use App\Services\Report;
use Filament\Widgets\ChartWidget;

class RetentionChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Tỷ lệ giữ chân khách hàng';

    protected ?string $description = 'Phần trăm khách của mỗi tháng đã từng mua trước đó';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $s = Report::retention(6);

        return [
            'datasets' => [['label' => 'Khách quay lại (%)', 'data' => array_column($s, 'rate'), 'borderColor' => '#d97706', 'backgroundColor' => 'rgba(217,119,6,.1)', 'fill' => true, 'tension' => .35]],
            'labels' => array_column($s, 'label'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
