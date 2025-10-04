<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerOfferKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_offer_id',
        'type',    // text or image
        'value',   // code string or image path
        'status',  // available, sold, reserved
    ];

    protected $casts = [
        'seller_offer_id' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function offer()
    {
        return $this->belongsTo(SellerOffer::class, 'seller_offer_id');
    }
}
