<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['status' => ['nullable', 'in:placed,confirmed,packing,shipped,delivered,cancelled']]);
        $orders = Order::with('user')->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load(['user', 'items'])]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:placed,confirmed,packing,shipped,delivered,cancelled'],
            'payment_status' => ['required', 'in:pending,unpaid,pay_on_delivery,paid,failed,refunded'],
        ]);
        $transitions = [
            'placed' => ['confirmed', 'cancelled'],
            'confirmed' => ['packing', 'cancelled'],
            'packing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        $old = [];
        try {
            DB::transaction(function () use ($data, $order, $transitions, &$old) {
                $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                $old = $lockedOrder->only(array_keys($data));

                if ($data['status'] !== $lockedOrder->status
                    && ! in_array($data['status'], $transitions[$lockedOrder->status], true)) {
                    throw ValidationException::withMessages([
                        'status' => 'This order cannot move from '.$lockedOrder->status.' to '.$data['status'].'.',
                    ]);
                }

                if ($data['status'] === 'cancelled' && $old['status'] !== 'cancelled') {
                    foreach ($lockedOrder->items as $item) {
                        if ($item->product_id) {
                            Product::whereKey($item->product_id)
                                ->lockForUpdate()
                                ->first()
                                ?->increment('stock', $item->quantity);
                        }
                    }
                }

                $lockedOrder->update($data);
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'order.updated',
            'subject_type' => Order::class,
            'subject_id' => $order->id,
            'properties' => ['before' => $old, 'after' => $data],
        ]);

        return back()->with('success', 'Order status updated.');
    }
}
