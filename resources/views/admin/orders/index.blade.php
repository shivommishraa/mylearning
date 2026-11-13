@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<div class="admin-title"><div><h1>Orders from the neighbourhood</h1><p>Keep good things moving all the way to the front door.</p></div><form method="GET"><select class="field" name="status" onchange="this.form.submit()"><option value="">All statuses</option>@foreach(['placed','confirmed','packing','shipped','delivered','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></form></div>
<section class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>Order</th><th>Neighbour</th><th>Payment</th><th>Placed</th><th>Status</th><th>Total</th><th></th></tr></thead><tbody>
@forelse($orders as $order)<tr><td><strong>{{ $order->order_number }}</strong></td><td>{{ $order->user->name }}<br><span class="muted">{{ $order->user->email }}</span></td><td>{{ strtoupper($order->payment_method) }}<br><span class="muted">{{ str_replace('_', ' ', $order->payment_status) }}</span></td><td>{{ $order->created_at->format('d M Y, h:i A') }}</td><td><span class="status-pill">{{ $order->status }}</span></td><td>₹{{ number_format((float) $order->total, 2) }}</td><td><a class="text-link" href="{{ route('admin.orders.show', $order) }}">Open →</a></td></tr>@empty<tr><td colspan="7">No orders found.</td></tr>@endforelse
</tbody></table></div>{{ $orders->links('components.pagination') }}</section>
@endsection
