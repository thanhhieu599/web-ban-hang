<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['values' => 'array', 'is_required' => 'boolean'];
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
