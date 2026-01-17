<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerWithdraw extends Model
{
    protected $fillable = [
        'seller_id',
        'amount',
        'method',
        'status',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
