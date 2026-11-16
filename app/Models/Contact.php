<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $guarded = [];

    public const STATUSES = ['new' => 'Mới', 'read' => 'Đã xem', 'replied' => 'Đã phản hồi'];
}
