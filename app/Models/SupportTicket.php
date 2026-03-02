<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number', 'user_id', 'order_id', 'seller_id',
        'assigned_admin_id', 'department', 'subject', 'priority',
        'status', 'is_escalated', 'escalated_at', 'resolved_at',
        'closed_at', 'last_reply_at', 'ip_address',
    ];

    protected $casts = [
        'is_escalated'  => 'boolean',
        'escalated_at'  => 'datetime',
        'resolved_at'   => 'datetime',
        'closed_at'     => 'datetime',
        'last_reply_at' => 'datetime',
    ];

    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];
    public const STATUSES   = [
        'open', 'awaiting_seller', 'awaiting_admin',
        'awaiting_customer', 'on_hold', 'escalated',
        'resolved', 'closed',
    ];

    public static function departmentSlugs(): array
    {
        return TicketDepartment::active()
            ->orderBy('sort_order')
            ->pluck('slug')
            ->toArray();
    }

    public const ALLOWED_TRANSITIONS = [
        'open'              => ['awaiting_seller', 'awaiting_admin', 'awaiting_customer', 'on_hold', 'escalated', 'resolved', 'closed'],
        'awaiting_seller'   => ['open', 'awaiting_admin', 'awaiting_customer', 'on_hold', 'escalated', 'resolved', 'closed'],
        'awaiting_admin'    => ['open', 'awaiting_seller', 'awaiting_customer', 'on_hold', 'escalated', 'resolved', 'closed'],
        'awaiting_customer' => ['open', 'awaiting_seller', 'awaiting_admin', 'on_hold', 'escalated', 'resolved', 'closed'],
        'on_hold'           => ['open', 'awaiting_seller', 'awaiting_admin', 'awaiting_customer', 'escalated', 'resolved', 'closed'],
        'escalated'         => ['open', 'awaiting_admin', 'awaiting_customer', 'on_hold', 'resolved', 'closed'],
        'resolved'          => ['open', 'closed'],
        'closed'            => ['open'],
    ];

    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }

    protected static function booted(): void
    {
        static::creating(function (self $ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    // ── Relationships ─────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function messages()
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id');
    }

    public function departmentModel()
    {
        return $this->belongsTo(TicketDepartment::class, 'department', 'slug');
    }

    public function latestMessage()
    {
        return $this->hasOne(SupportTicketMessage::class, 'ticket_id')->latestOfMany();
    }

    // ── Scopes ────────────────────────────────────────────

    public function scopeOpen($q)              { return $q->where('status', 'open'); }
    public function scopeEscalated($q)         { return $q->where('status', 'escalated'); }
    public function scopeResolved($q)          { return $q->where('status', 'resolved'); }
    public function scopeClosed($q)            { return $q->where('status', 'closed'); }
    public function scopeAwaitingSeller($q)    { return $q->where('status', 'awaiting_seller'); }
    public function scopeAwaitingAdmin($q)     { return $q->where('status', 'awaiting_admin'); }
    public function scopeAwaitingCustomer($q)  { return $q->where('status', 'awaiting_customer'); }
    public function scopeOnHold($q)            { return $q->where('status', 'on_hold'); }

    public function scopeActive($q)
    {
        return $q->whereNotIn('status', ['resolved', 'closed']);
    }

    public function scopeForSeller($q, int $sellerId)
    {
        return $q->where('seller_id', $sellerId);
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function scopeUnassigned($q)
    {
        return $q->whereNull('assigned_admin_id');
    }

    // ── Helpers ───────────────────────────────────────────

    public function isAssignedToSeller(): bool
    {
        return $this->seller_id !== null && !$this->is_escalated;
    }

    public function isOpen(): bool
    {
        return !in_array($this->status, ['resolved', 'closed']);
    }
}
