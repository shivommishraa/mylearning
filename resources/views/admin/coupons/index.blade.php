@extends('layouts.admin')
@section('title', 'Offers & coupons')
@section('content')
<div class="admin-title"><div><h1>A little something extra</h1><p>Give neighbours a reason to add one more good thing.</p></div></div>
@can('coupons.create')<form class="admin-panel" method="POST" action="{{ route('admin.coupons.store') }}">@csrf<h2>Create an offer</h2>@include('admin.coupons.fields', ['coupon' => null])<button class="button button-small">Create offer</button></form>@endcan
<section class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>Code</th><th>Offer</th><th>Requirements</th><th>Usage</th><th>Availability</th><th>Manage</th></tr></thead><tbody>
@forelse($coupons as $coupon)<tr><td><strong>{{ $coupon->code }}</strong><br><span class="muted">{{ $coupon->description }}</span></td><td>{{ $coupon->type==='percent' ? number_format((float)$coupon->value,0).'%' : '₹'.number_format((float)$coupon->value,2) }} off</td><td>Min ₹{{ number_format((float)$coupon->minimum_order,0) }}</td><td>{{ $coupon->usage_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td><td><span class="status-pill">{{ $coupon->is_active ? 'Live' : 'Paused' }}</span></td><td>
    @can('coupons.edit')<details><summary class="text-link">Edit</summary><form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" style="min-width:290px;padding:12px">@csrf @method('PUT')@include('admin.coupons.fields', ['coupon' => $coupon])<button class="button button-small">Save offer</button></form></details>@endcan
    @can('coupons.delete')<form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Deactivate this offer?')">@csrf @method('DELETE')<button class="remove-link" style="border:0;background:none;margin-top:7px">Pause offer</button></form>@endcan
</td></tr>@empty<tr><td colspan="6">No offers yet.</td></tr>@endforelse
</tbody></table></div>{{ $coupons->links('components.pagination') }}</section>
@endsection
