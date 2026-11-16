<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];
    }

    /** Trả về lý do không dùng được mã, hoặc null nếu hợp lệ. */
    public function invalidReason(int $subtotal): ?string
    {
        return match (true) {
            ! $this->is_active => 'Mã giảm giá đã bị tắt.',
            $this->starts_at && $this->starts_at->isFuture() => 'Mã giảm giá chưa đến ngày áp dụng.',
            $this->ends_at && $this->ends_at->isPast() => 'Mã giảm giá đã hết hạn.',
            $this->usage_limit !== null && $this->used >= $this->usage_limit => 'Mã giảm giá đã hết lượt sử dụng.',
            $subtotal < $this->min_order => 'Đơn hàng cần tối thiểu '.number_format($this->min_order, 0, ',', '.').'đ để dùng mã này.',
            default => null,
        };
    }

    public function amountFor(int $subtotal): int
    {
        $amount = $this->type === 'percent' ? (int) floor($subtotal * $this->value / 100) : (int) $this->value;
        if ($this->max_discount) {
            $amount = min($amount, (int) $this->max_discount);
        }

        return min($amount, $subtotal);
    }
}
