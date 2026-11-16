<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    protected $guarded = [];

    protected static ?self $currentSale = null;

    protected static bool $resolved = false;

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function items()
    {
        return $this->hasMany(FlashSaleItem::class);
    }

    /** Chiến dịch đang chạy (nếu có), chỉ truy vấn một lần mỗi request. */
    public static function current(): ?self
    {
        if (! self::$resolved) {
            self::$resolved = true;
            self::$currentSale = self::where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where('ends_at', '>', now())->orderBy('ends_at')->with('items')->first();
        }

        return self::$currentSale;
    }

    public static function forgetCurrent(): void
    {
        self::$resolved = false;
        self::$currentSale = null;
    }
}
