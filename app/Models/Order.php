<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public const STATUSES = [
        'pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy', 'returned' => 'Đã trả hàng',
    ];

    public const STATUS_COLORS = [
        'pending' => 'warning', 'confirmed' => 'info', 'shipping' => 'primary',
        'completed' => 'success', 'cancelled' => 'gray', 'returned' => 'danger',
    ];

    public const PAYMENT_METHODS = ['cod' => 'Thanh toán khi nhận hàng', 'bank' => 'Chuyển khoản ngân hàng', 'installment' => 'Trả góp 0%'];

    public const PAYMENT_STATUSES = ['unpaid' => 'Chưa thanh toán', 'paid' => 'Đã thanh toán', 'refunded' => 'Đã hoàn tiền'];

    /** Các trạng thái được tính vào doanh thu. */
    public const REVENUE_STATUSES = ['confirmed', 'shipping', 'completed'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function scopeRevenue($q)
    {
        return $q->whereIn('status', self::REVENUE_STATUSES);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getProfitAttribute(): int
    {
        return (int) $this->total - (int) $this->cost_total - (int) $this->shipping_fee;
    }

    /** Tính lại tạm tính, giá vốn và tổng tiền từ các dòng sản phẩm. */
    public function recalculate(): void
    {
        $items = $this->items()->get();
        $subtotal = (int) $items->sum(fn ($i) => $i->price * $i->quantity);
        $this->update([
            'subtotal' => $subtotal,
            'cost_total' => (int) $items->sum(fn ($i) => $i->cost * $i->quantity),
            'total' => max(0, $subtotal - (int) $this->discount_total + (int) $this->shipping_fee),
        ]);
    }

    public static function newCode(): string
    {
        do {
            $code = 'DH'.now()->format('ymd').strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /** Đổi trạng thái đơn và xử lý tồn kho khi hủy/trả. */
    public function changeStatus(string $status): void
    {
        $restock = in_array($status, ['cancelled', 'returned']) && ! in_array($this->status, ['cancelled', 'returned']);
        $this->update([
            'status' => $status,
            'completed_at' => $status === 'completed' ? now() : $this->completed_at,
            'payment_status' => $status === 'completed' ? 'paid' : ($status === 'returned' ? 'refunded' : $this->payment_status),
        ]);
        if ($restock) {
            foreach ($this->items()->with('product')->get() as $item) {
                $item->product?->adjustStock($item->quantity, 'return', 'Hoàn kho từ đơn '.$this->code);
                $item->product?->decrement('sold_count', min($item->quantity, $item->product->sold_count));
            }
        }
    }
}
