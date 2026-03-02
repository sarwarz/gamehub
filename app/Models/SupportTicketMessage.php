<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'user_id', 'sender_role',
        'message', 'attachments', 'is_internal_note',
    ];

    protected $casts = [
        'attachments'      => 'array',
        'is_internal_note' => 'boolean',
    ];

    public const ROLES = ['customer', 'seller', 'admin'];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublic($q)
    {
        return $q->where('is_internal_note', false);
    }

    public function scopeInternal($q)
    {
        return $q->where('is_internal_note', true);
    }

    public function scopeBySenderRole($q, string $role)
    {
        return $q->where('sender_role', $role);
    }
}
