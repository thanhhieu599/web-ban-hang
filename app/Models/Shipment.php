<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $guarded = [];

    public const STATUSES = ['pending' => 'Chờ lấy hàng', 'picked' => 'Đã lấy hàng', 'in_transit' => 'Đang vận chuyển', 'delivered' => 'Đã giao', 'failed' => 'Giao thất bại'];

    public const CARRIERS = ['Giao hàng nội bộ', 'Giao Hàng Nhanh', 'Giao Hàng Tiết Kiệm', 'Viettel Post', 'J&T Express', 'VNPost'];

    protected function casts(): array
    {
        return ['shipped_at' => 'datetime', 'delivered_at' => 'datetime'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** Đồng bộ trạng thái đơn hàng theo vận đơn. */
    protected static function booted(): void
    {
        static::created(function (self $s) {
            if (in_array($s->order?->status, ['pending', 'confirmed']) && $s->status !== 'failed') {
                $s->order->changeStatus('shipping');
            }
        });
        static::updated(function (self $s) {
            if ($s->wasChanged('status') && $s->status === 'delivered' && $s->order?->status === 'shipping') {
                $s->order->changeStatus('completed');
            }
        });
    }
}
