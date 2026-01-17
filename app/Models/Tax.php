<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'seller_id',
        'country',
        'state',
        'city',
        'type',
        'rate',
        'priority',
        'is_compound',
        'is_active',
    ];

    protected $casts = [
        'rate'        => 'float',
        'priority'    => 'integer',
        'is_compound' => 'boolean',
        'is_active'   => 'boolean',
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

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isGlobal(): bool
    {
        return is_null($this->seller_id);
    }
}
