<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];

    public const CATEGORIES = ['Quảng cáo', 'Vận chuyển', 'Lương nhân viên', 'Mặt bằng', 'Phần mềm', 'Khác'];

    protected function casts(): array
    {
        return ['spent_at' => 'date'];
    }
}
