<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id', 'store_name', 'slug', 'logo', 'banner', 'description',
        'email', 'phone', 'website',
        'company_name', 'registration_number', 'vat_number', 'tax_id',
        'country', 'state', 'city', 'address', 'postal_code',
        'bank_account_name', 'bank_account_number', 'bank_name', 'bank_swift_code',
        'rating', 'total_sales', 'total_products', 'status', 'is_verified'
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'rating'      => 'float',
        'total_sales' => 'integer',
        'is_verified' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Each seller belongs to one user account
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentInfo()
    {
        return $this->hasOne(SellerPaymentInfo::class);
    }


}
