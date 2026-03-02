<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderNote;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\OrderNoteNotification;

class OrderNoteController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'note'       => 'required|string',
            'visibility' => 'required|in:private,customer',
        ]);

        $isVisibleToCustomer = $request->visibility === 'customer';

        $order->notes()->create([
            'user_id' => auth()->id(),
            'note'    => $request->note,
            'type'    => 'admin',
            'is_visible_to_customer' => $isVisibleToCustomer,
        ]);

        if ($isVisibleToCustomer && $order->user) {
            try {
                $order->user->notify(new OrderNoteNotification(
                    order: $order,
                    noteText: $request->note,
                    adminName: auth()->user()->name ?? 'Support Team',
                ));
            } catch (\Throwable $e) {
                report($e);
                return back()->with('warning', 'Note added but email notification failed to send.');
            }
        }

        $message = $isVisibleToCustomer
            ? 'Note added and email sent to customer.'
            : 'Order note added.';

        return back()->with('success', $message);
    }

    public function destroy(OrderNote $note)
    {
        $note->delete();

        return back()->with('success', 'Order note deleted.');
    }
}
