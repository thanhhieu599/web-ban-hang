<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];

    public const STATUSES = ['issued' => 'Đã xuất', 'paid' => 'Đã thanh toán', 'cancelled' => 'Đã hủy'];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function newNumber(): string
    {
        return 'HD'.now()->format('ym').'-'.str_pad((string) ((self::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }
}
