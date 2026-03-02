<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CannedResponse extends Model
{
    protected $fillable = [
        'title', 'body', 'category', 'shortcut', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const CATEGORIES = [
        'greeting', 'order', 'payment', 'refund',
        'shipping', 'product', 'account', 'closing', 'general',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
