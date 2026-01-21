<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderNoteController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'note' => 'required|string',
            'visibility' => 'required|in:private,customer',
        ]);

        $order->notes()->create([
            'user_id' => auth()->id(),
            'note'    => $request->note,
            'type'    => 'admin',
            'is_visible_to_customer' => $request->visibility === 'customer',
        ]);

        return back()->with('success', 'Order note added.');
    }
}
