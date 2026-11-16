<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_active' => 'boolean'];
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    /** Cây danh mục 2 cấp dùng cho menu (chỉ truy vấn một lần mỗi request). */
    public static function tree()
    {
        return once(fn () => self::active()->whereNull('parent_id')->orderBy('sort')
            ->with(['children' => fn ($q) => $q->where('is_active', true)])->get());
    }
}
