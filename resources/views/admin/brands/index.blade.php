@extends('layouts.admin')
@section('title', 'Brands')
@section('content')
<div class="admin-title"><div><h1>Brands you bring to the table</h1><p>Keep the makers and favourites customers can browse and recognize.</p></div></div>
@can('brands.create')<form class="admin-panel" method="POST" action="{{ route('admin.brands.store') }}">@csrf<h2>Add a brand</h2>@include('admin.brands.fields',['brand'=>null])<button class="button button-small">Create brand</button></form>@endcan
<div class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>Brand</th><th>Products</th><th>Visibility</th><th>Manage</th></tr></thead><tbody>
@forelse($brands as $brand)<tr><td><strong>{{ $brand->name }}</strong><br><span class="muted">{{ $brand->slug }}</span></td><td>{{ $brand->products_count }}</td><td><span class="status-pill">{{ $brand->is_active ? 'Live' : 'Hidden' }}</span></td><td>
    @can('brands.edit')<details><summary class="text-link">Edit</summary><form method="POST" action="{{ route('admin.brands.update',$brand) }}" style="padding:12px;min-width:250px">@csrf @method('PUT')@include('admin.brands.fields',['brand'=>$brand])<button class="button button-small">Save</button></form></details>@endcan
    @can('brands.delete')<form method="POST" action="{{ route('admin.brands.destroy',$brand) }}" onsubmit="return confirm('Delete this brand? Product records remain.')">@csrf @method('DELETE')<button class="remove-link" style="border:0;background:none;margin-top:8px">Delete</button></form>@endcan
</td></tr>@empty<tr><td colspan="4">No brands yet.</td></tr>@endforelse
</tbody></table></div>{{ $brands->links('components.pagination') }}</div>
@endsection
