<?php

namespace App\Filament\Widgets;

use App\Services\Report;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $period = $this->pageFilters['period'] ?? '30';
        $now = Report::summary($period);
        $before = Report::summary($period, 1);
        $series = Report::series($period);

        $stat = function (string $label, string $value, string $key, ?array $chart = null, bool $inverse = false) use ($now, $before) {
            $t = Report::trend($now[$key], $before[$key]);
            $good = $inverse ? ! $t['up'] : $t['up'];
            $stat = Stat::make($label, $value)->description($t['text'])
                ->descriptionIcon($t['flat'] ? 'heroicon-m-minus' : ($t['up'] ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'))
                ->color($t['flat'] ? 'gray' : ($good ? 'success' : 'danger'));

            return $chart ? $stat->chart($chart) : $stat;
        };

        return [
            $stat('Doanh thu', vnd($now['revenue']), 'revenue', array_column($series, 'revenue')),
            $stat('Lợi nhuận (sau giá vốn và chi phí)', vnd($now['profit']), 'profit', array_column($series, 'profit')),
            $stat('Chi phí', vnd($now['expenses']), 'expenses', null, true),
            $stat('Giá trị đơn trung bình', vnd($now['aov']), 'aov'),
            $stat('Tổng đơn hàng', number_format($now['orders'], 0, ',', '.'), 'orders', array_column($series, 'orders')),
            $stat('Khách mua hàng', number_format($now['customers'], 0, ',', '.'), 'customers'),
            $stat('Tỷ lệ chuyển đổi', str_replace('.', ',', (string) $now['conversion']).'%', 'conversion')
                ->description(number_format($now['visits'], 0, ',', '.').' lượt truy cập trong kỳ'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
