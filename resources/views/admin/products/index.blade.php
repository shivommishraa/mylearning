@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<div class="admin-title"><div><h1>Good things on your shelves</h1><p>Keep your products, prices and stock feeling fresh.</p></div><div style="display:flex;gap:8px">@can('products.settings')<a class="button button-light button-small" href="{{ route('admin.products.settings') }}">Storefront settings</a>@endcan @can('products.create')<a class="button button-small" href="{{ route('admin.products.create') }}">+ Add a product</a>@endcan</div></div>
<section class="admin-panel">
    <form class="admin-product-search" method="GET" action="{{ route('admin.products.index') }}" role="search">
        <input class="field" type="search" name="q" value="{{ $search }}" placeholder="Search by product name or SKU" aria-label="Search products by name or SKU" maxlength="120">
        <button class="button button-small" type="submit">Search</button>
        @if($search !== '')<a class="text-link" href="{{ route('admin.products.index') }}">Clear</a>@endif
    </form>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Visibility</th><th>Actions</th></tr></thead><tbody>
    @forelse($products as $product)<tr><td><div style="display:flex;align-items:center;gap:10px">@if($product->image_url)<img src="{{ $product->image_url }}" alt="" style="width:39px;height:39px;object-fit:cover;border-radius:4px">@endif<div><strong>{{ $product->name }}</strong><br><span class="muted">{{ $product->sku }}</span></div></div></td><td>{{ $product->category->name }}</td><td>₹{{ number_format((float)$product->price, 2) }}</td><td style="color:{{ $product->stock <= 10 ? '#a64b40' : 'inherit' }}">{{ $product->stock }} {{ $product->unit }}</td><td><span class="status-pill">{{ $product->is_active ? 'Live' : 'Archived' }}</span></td><td><div class="admin-actions">@can('products.edit')<a class="text-link" href="{{ route('admin.products.edit', $product) }}">Edit</a>@endcan @can('products.delete')@if($product->is_active)<form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Archive this product? Past order records will stay safe.')">@csrf @method('DELETE')<button class="remove-link" style="border:0;background:none">Archive</button></form>@endif @endcan</div></td></tr>
    @empty<tr><td colspan="6">{{ $search !== '' ? 'No products match your search.' : 'No products yet. Add your first product to bring the shop to life.' }}</td></tr>@endforelse
</tbody></table></div>{{ $products->links('components.pagination') }}</section>
@endsection
