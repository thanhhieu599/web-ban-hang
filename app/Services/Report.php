<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Visit;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Số liệu kinh doanh cho bảng điều khiển.
 * Doanh thu chỉ tính các đơn đã xác nhận, đang giao hoặc hoàn thành.
 */
class Report
{
    public const PERIODS = ['7' => '7 ngày qua', '30' => '30 ngày qua', '90' => '90 ngày qua', '365' => '12 tháng qua'];

    protected static array $memo = [];

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    public static function range(?string $period, int $shift = 0): array
    {
        $days = (int) ($period ?: 30);
        $end = CarbonImmutable::now()->endOfDay()->subDays($days * $shift);

        return [$end->subDays($days - 1)->startOfDay(), $end];
    }

    public static function orders(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return self::$memo[$from->timestamp.'-'.$to->timestamp] ??= Order::revenue()
            ->whereBetween('created_at', [$from, $to])
            ->get(['id', 'phone', 'total', 'cost_total', 'shipping_fee', 'created_at']);
    }

    public static function summary(?string $period, int $shift = 0): array
    {
        [$from, $to] = self::range($period, $shift);
        $orders = self::orders($from, $to);
        $revenue = (int) $orders->sum('total');
        $gross = $revenue - (int) $orders->sum('cost_total');
        $expenses = (int) Expense::whereDate('spent_at', '>=', $from->toDateString())->whereDate('spent_at', '<=', $to->toDateString())->sum('amount');
        $visits = (int) Visit::whereDate('date', '>=', $from->toDateString())->whereDate('date', '<=', $to->toDateString())->sum('count');
        $count = $orders->count();

        return [
            'revenue' => $revenue,
            'profit' => $gross - $expenses,
            'expenses' => $expenses,
            'aov' => $count ? (int) round($revenue / $count) : 0,
            'orders' => $count,
            'customers' => $orders->unique('phone')->count(),
            'conversion' => $visits ? round($count / $visits * 100, 2) : 0.0,
            'visits' => $visits,
        ];
    }

    /** Chuỗi số liệu theo ngày (hoặc theo tháng nếu kỳ dài hơn 90 ngày). */
    public static function series(?string $period): array
    {
        [$from, $to] = self::range($period);
        $monthly = (int) ($period ?: 30) > 90;
        $key = fn ($date) => $date->format($monthly ? 'Y-m' : 'Y-m-d');
        $buckets = [];
        for ($d = $from; $d <= $to; $d = $monthly ? $d->addMonthNoOverflow()->startOfMonth() : $d->addDay()) {
            $buckets[$key($d)] = ['label' => $d->format($monthly ? 'm/Y' : 'd/m'), 'revenue' => 0, 'profit' => 0, 'orders' => 0];
        }
        foreach (self::orders($from, $to) as $o) {
            $k = $key($o->created_at);
            if (isset($buckets[$k])) {
                $buckets[$k]['revenue'] += $o->total;
                $buckets[$k]['profit'] += $o->total - $o->cost_total;
                $buckets[$k]['orders']++;
            }
        }

        return array_values($buckets);
    }

    /** Khách mới theo ngày: khách (theo số điện thoại) có đơn đầu tiên rơi vào kỳ. */
    public static function newCustomers(?string $period): array
    {
        [$from, $to] = self::range($period);
        $monthly = (int) ($period ?: 30) > 90;
        $fmt = $monthly ? 'Y-m' : 'Y-m-d';
        $buckets = [];
        for ($d = $from; $d <= $to; $d = $monthly ? $d->addMonthNoOverflow()->startOfMonth() : $d->addDay()) {
            $buckets[$d->format($fmt)] = ['label' => $d->format($monthly ? 'm/Y' : 'd/m'), 'count' => 0];
        }
        $first = Order::revenue()->selectRaw('phone, MIN(created_at) as first_at')->groupBy('phone')->get();
        foreach ($first as $row) {
            $k = CarbonImmutable::parse($row->first_at)->format($fmt);
            if (isset($buckets[$k])) {
                $buckets[$k]['count']++;
            }
        }

        return array_values($buckets);
    }

    /** Tỷ lệ giữ chân: % khách của mỗi tháng đã từng mua trước đó. */
    public static function retention(int $months = 6): array
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths($months - 1);
        $all = Order::revenue()->orderBy('created_at')->get(['phone', 'created_at']);
        $firstSeen = [];
        $perMonth = [];
        foreach ($all as $o) {
            $m = $o->created_at->format('Y-m');
            $firstSeen[$o->phone] ??= $m;
            $perMonth[$m][$o->phone] = $firstSeen[$o->phone] !== $m;
        }
        $out = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $start->addMonths($i);
            $customers = $perMonth[$m->format('Y-m')] ?? [];
            $returning = count(array_filter($customers));
            $out[] = ['label' => $m->format('m/Y'), 'rate' => $customers ? round($returning / count($customers) * 100, 1) : 0];
        }

        return $out;
    }

    public static function trend(float|int $now, float|int $before): array
    {
        if (! $before) {
            return ['text' => $now ? 'Mới phát sinh trong kỳ' : 'Chưa có dữ liệu', 'up' => $now > 0, 'flat' => ! $now];
        }
        $pct = round(($now - $before) / $before * 100, 1);

        return ['text' => ($pct >= 0 ? 'Tăng ' : 'Giảm ').abs($pct).'% so với kỳ trước', 'up' => $pct >= 0, 'flat' => false];
    }
}
