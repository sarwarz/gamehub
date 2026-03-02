<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $fillable = [
        'gateway',
        'event_id',
        'event_type',
        'payload',
        'headers',
        'status',
        'error',
        'processed_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'headers'      => 'array',
        'processed_at' => 'datetime',
    ];

    public function markProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markProcessed(): void
    {
        $this->update([
            'status'       => 'processed',
            'processed_at' => now(),
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error'  => $error,
        ]);
    }
}
