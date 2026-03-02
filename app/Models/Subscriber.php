<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Subscriber extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'name',
        'status',
        'ip_address',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'subscribed_at'   => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUnsubscribed($query)
    {
        return $query->where('status', 'unsubscribed');
    }
}
