<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'note',
        'type',
        'is_visible_to_customer',
    ];

    protected $casts = [
        'is_visible_to_customer' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
