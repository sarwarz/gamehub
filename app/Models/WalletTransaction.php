<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'source',
        'reference_id',
        'reference_type',
        'description',
        'status',
        'balance_after',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /* --------------------------------
     | Relationships
     |-------------------------------- */

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /* --------------------------------
     | Scopes
     |-------------------------------- */

    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
