<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $guarded = [];

    public const TYPES = ['in' => 'Nhập kho', 'out' => 'Xuất kho', 'adjust' => 'Điều chỉnh', 'order' => 'Bán hàng', 'return' => 'Hoàn hàng'];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
