@extends('layouts.admin')
@section('title', 'Customers')
@section('content')
<div class="admin-title"><div><h1>Your neighbours</h1><p>People who make SSG MART part of their day.</p></div></div>
<section class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Joined</th><th>Verification</th></tr></thead><tbody>@forelse($customers as $customer)<tr><td><strong>{{ $customer->name }}</strong></td><td><a class="text-link" href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></td><td>{{ $customer->phone ?: '—' }}</td><td>{{ $customer->orders_count }}</td><td>{{ $customer->created_at->format('d M Y') }}</td><td><span class="status-pill">{{ $customer->email_verified_at ? 'Verified' : 'Pending' }}</span></td></tr>@empty<tr><td colspan="6">No customers yet.</td></tr>@endforelse</tbody></table></div>{{ $customers->links('components.pagination') }}</section>
@endsection
