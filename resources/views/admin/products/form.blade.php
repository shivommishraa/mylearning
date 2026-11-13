@extends('layouts.admin')
@section('title', $product->exists ? 'Edit product' : 'New product')
@section('content')
<div class="admin-title"><div><h1>{{ $product->exists ? 'A little product refresh' : 'Add something lovely' }}</h1><p>Keep your neighbourhood shelves looking their best.</p></div><a class="text-link" href="{{ route('admin.products.index') }}">← Back to products</a></div>
<form class="admin-panel" method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">@csrf @if($product->exists)@method('PUT')@endif
    <div class="form-grid">
        <div class="admin-field"><label for="name">Product name</label><input id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="180" required></div>
        <div class="admin-field"><label for="category_id">Category</label><select id="category_id" name="category_id" required><option value="">Choose an aisle</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="admin-field"><label for="brand_id">Brand</label><select id="brand_id" name="brand_id"><option value="">No brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected(old('brand_id',$product->brand_id)==$brand->id)>{{ $brand->name }}</option>@endforeach</select></div>
        <div class="admin-field"><label for="sku">SKU</label><input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" maxlength="80" placeholder="Leave blank for an SSG SKU"><small>Unique product reference.</small></div>
        <div class="admin-field"><label for="unit">Unit / pack size</label><input id="unit" name="unit" value="{{ old('unit', $product->unit ?: 'each') }}" maxlength="30" required placeholder="e.g. 500 g"></div>
        <div class="admin-field"><label for="price">Selling price (₹)</label><input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', $product->price) }}" required></div>
        <div class="admin-field"><label for="compare_at_price">Compare-at price (₹)</label><input type="number" step="0.01" min="0" id="compare_at_price" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}"><small>Leave blank if there is no offer price.</small></div>
        <div class="admin-field"><label for="stock">Stock on hand</label><input type="number" min="0" id="stock" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required></div>
        <div class="admin-field"><label for="image_file">Product image</label><input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/webp"><small>Upload JPG, PNG or WebP (up to 5 MB).</small></div>
        <div class="admin-field"><label for="image_url">Or use an image URL</label><input type="url" id="image_url" name="image_url" value="{{ old('image_url', \Illuminate\Support\Str::startsWith($product->image ?? '', ['https://', 'http://']) ? $product->image : '') }}" maxlength="2048" placeholder="https://…"><small>A new upload takes precedence over this URL.</small></div>
    </div>
    @if($product->image)<div class="image-management"><img src="{{ $product->image_url }}" alt="{{ $product->name }} current image"><label class="permission-item"><input type="checkbox" name="remove_image" value="1"> Remove current image</label></div>@endif
    <div class="admin-field"><label for="description">Description</label><textarea id="description" name="description" rows="5" maxlength="5000">{{ old('description', $product->description) }}</textarea></div>
    <div style="display:flex;gap:18px;margin:12px 0 19px"><label class="permission-item"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))> Feature on the homepage</label><label class="permission-item"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true))> Visible in the shop</label></div>
    <button class="button">Save product</button>
</form>
@endsection
