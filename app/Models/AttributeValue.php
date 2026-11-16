<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    protected $guarded = [];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function getLabelAttribute(): string
    {
        return ($this->attribute->name ?? '').': '.$this->value;
    }
}
