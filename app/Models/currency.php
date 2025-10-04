<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_default',
        'is_active',
        'rate',
        'fetched_at',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    protected static function booted()
    {
        static::updated(function ($currency) {
            //  if "is_default" changed to true → clear cache
            if ($currency->isDirty('is_default') && $currency->is_default) {
                app(\App\Services\CurrencyService::class)->clearCache();
            }
        });

        static::created(function ($currency) {
            // also clear cache in case a new default is inserted
            if ($currency->is_default) {
                app(\App\Services\CurrencyService::class)->clearCache();
            }
        });
    }
}
