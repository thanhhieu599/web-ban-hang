<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = [];

    public const GROUPS = ['about' => 'Về chúng tôi', 'policy' => 'Chính sách', 'support' => 'Hỗ trợ khách hàng'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
