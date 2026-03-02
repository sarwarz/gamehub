<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketEscalatedNotification;
use App\Notifications\TicketReplyNotification;
use App\Notifications\TicketStatusNotification;
use Illuminate\Support\Facades\Log;

class TicketNotificationService
{
    protected static function enabled(string $key): bool
    {
        return (bool) Setting::get('ticket_notifications', $key, false);
    }

    protected static function getAdmins()
    {
        return User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'superadmin']))->get();
    }

    public static function ticketCreated(SupportTicket $ticket): void
    {
        if (!self::enabled('on_ticket_created')) return;

        $ticket->loadMissing(['user', 'seller.user']);

        try {
            if (self::enabled('admin_on_new_ticket')) {
                self::getAdmins()->each(fn ($admin) => $admin->notify(new TicketCreatedNotification($ticket)));
            }

            if ($ticket->seller && $ticket->seller->user) {
                $ticket->seller->user->notify(new TicketCreatedNotification($ticket));
            }
        } catch (\Throwable $e) {
            Log::warning('Ticket notification failed: ' . $e->getMessage());
        }
    }

    public static function staffReplied(SupportTicket $ticket, SupportTicketMessage $message): void
    {
        if (!self::enabled('on_staff_reply')) return;

        $ticket->loadMissing('user');
        if (!$ticket->user) return;

        try {
            $ticket->user->notify(new TicketReplyNotification($ticket, $message, 'to_customer'));
        } catch (\Throwable $e) {
            Log::warning('Ticket reply notification failed: ' . $e->getMessage());
        }
    }

    public static function customerReplied(SupportTicket $ticket, SupportTicketMessage $message): void
    {
        if (!self::enabled('on_customer_reply')) return;

        $ticket->loadMissing(['user', 'assignedAdmin', 'seller.user']);

        try {
            if ($ticket->seller_id && !$ticket->is_escalated && $ticket->seller?->user) {
                $ticket->seller->user->notify(new TicketReplyNotification($ticket, $message, 'to_staff'));
            } elseif ($ticket->assignedAdmin) {
                $ticket->assignedAdmin->notify(new TicketReplyNotification($ticket, $message, 'to_staff'));
            } else {
                self::getAdmins()->each(fn ($a) => $a->notify(new TicketReplyNotification($ticket, $message, 'to_staff')));
            }
        } catch (\Throwable $e) {
            Log::warning('Ticket customer reply notification failed: ' . $e->getMessage());
        }
    }

    public static function statusChanged(SupportTicket $ticket, string $newStatus, string $oldStatus = null): void
    {
        if ($newStatus === 'closed' && !self::enabled('on_ticket_closed')) return;
        if ($newStatus !== 'closed' && !self::enabled('on_status_change')) return;

        $notifyCustomer = in_array($newStatus, ['resolved', 'closed', 'awaiting_customer']);
        if (!$notifyCustomer) return;

        $ticket->loadMissing('user');
        if (!$ticket->user) return;

        try {
            $ticket->user->notify(new TicketStatusNotification($ticket, $newStatus, $oldStatus));
        } catch (\Throwable $e) {
            Log::warning('Ticket status notification failed: ' . $e->getMessage());
        }
    }

    public static function assigned(SupportTicket $ticket): void
    {
        if (!self::enabled('on_assigned')) return;

        $ticket->loadMissing('assignedAdmin');
        if (!$ticket->assignedAdmin) return;

        try {
            $ticket->assignedAdmin->notify(new TicketAssignedNotification($ticket));
        } catch (\Throwable $e) {
            Log::warning('Ticket assigned notification failed: ' . $e->getMessage());
        }
    }

    public static function escalated(SupportTicket $ticket): void
    {
        if (!self::enabled('on_escalated')) return;

        $ticket->loadMissing(['user', 'seller']);

        try {
            self::getAdmins()->each(fn ($a) => $a->notify(new TicketEscalatedNotification($ticket)));
        } catch (\Throwable $e) {
            Log::warning('Ticket escalated notification failed: ' . $e->getMessage());
        }
    }
}
