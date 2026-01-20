<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'invoice_number',
        'subtotal',
        'tax_total',
        'discount_total',
        'grand_total',
        'currency',
        'status',
        'issued_at',
        'paid_at',
        'meta',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'paid_at'   => 'date',
        'meta'      => 'array',
    ];

    /* ---------------------------
     | Relationships
     ---------------------------*/

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /* ---------------------------
     | Helpers
     ---------------------------*/

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
