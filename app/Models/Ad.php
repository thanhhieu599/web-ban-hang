<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $guarded = [];

    public const POSITIONS = [
        'home_hero' => 'Trang chủ: banner chính (slider)', 'home_side' => 'Trang chủ: banner phụ bên phải',
        'home_deal' => 'Trang chủ: ô deal nổi bật', 'category_top' => 'Trang danh mục: banner đầu trang',
    ];

    public const THEMES = ['red' => 'Đỏ', 'dark' => 'Đen', 'ice' => 'Xanh dương', 'warm' => 'Cam'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function scopeLive($q, string $position)
    {
        return $q->where('position', $position)->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->orderBy('sort');
    }
}
