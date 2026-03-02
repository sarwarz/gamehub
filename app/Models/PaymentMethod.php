<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'is_enabled',
        'mode',
        'currency',
        'config',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'config'     => 'array',
    ];
}
