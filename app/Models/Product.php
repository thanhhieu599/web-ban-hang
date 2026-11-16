<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public const STATUSES = ['draft' => 'Bản nháp', 'pending' => 'Chờ duyệt', 'published' => 'Đang bán'];

    protected function casts(): array
    {
        return ['images' => 'array', 'specs' => 'array', 'is_featured' => 'boolean', 'rating_avg' => 'float'];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class);
    }

    public function options()
    {
        return $this->belongsToMany(Option::class);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class);
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->reviews()->where('status', 'approved')->latest();
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    /** Giá đang áp dụng: ưu tiên giá flash sale nếu còn suất. */
    public function getFinalPriceAttribute(): int
    {
        $item = $this->flashItem();

        return $item ? (int) $item->price : (int) $this->price;
    }

    public function getOldPriceAttribute(): ?int
    {
        $old = $this->compare_price ?: ($this->flashItem() ? $this->price : null);

        return $old && $old > $this->final_price ? (int) $old : null;
    }

    public function getDiscountPercentAttribute(): int
    {
        return $this->old_price ? (int) round((1 - $this->final_price / $this->old_price) * 100) : 0;
    }

    public function flashItem(): ?FlashSaleItem
    {
        $sale = FlashSale::current();
        if (! $sale) {
            return null;
        }
        $item = $sale->items->firstWhere('product_id', $this->id);

        return $item && $item->sold < $item->quantity ? $item : null;
    }

    public function getImageUrlAttribute(): ?string
    {
        $first = $this->images[0] ?? null;

        return $first ? asset('storage/'.$first) : null;
    }

    public function getArtKeyAttribute(): string
    {
        return $this->art ?: ($this->category->icon ?? 'plug');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->low_stock_threshold;
    }

    /** Thay đổi tồn kho và ghi lịch sử. */
    public function adjustStock(int $quantity, string $type, ?string $note = null): void
    {
        $this->increment('stock', $quantity);
        $this->inventoryMovements()->create([
            'user_id' => auth()->id(), 'type' => $type, 'quantity' => $quantity,
            'stock_after' => $this->stock, 'note' => $note,
        ]);
    }

    public function refreshRating(): void
    {
        $q = $this->reviews()->where('status', 'approved');
        $this->update(['rating_avg' => round((float) $q->avg('rating'), 2), 'rating_count' => $q->count()]);
    }
}
