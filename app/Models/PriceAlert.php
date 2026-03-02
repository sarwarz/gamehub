<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PriceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'type',
        'target_price',
        'last_notified_price',
        'is_active',
        'notified_at',
    ];

    protected $casts = [
        'target_price'        => 'decimal:2',
        'last_notified_price' => 'decimal:2',
        'is_active'           => 'boolean',
        'notified_at'         => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
