@extends('layouts.admin')
@section('title', 'Homepage sections')
@section('content')
<div class="admin-title"><div><h1>Homepage collections</h1><p>Choose which product collections appear on the storefront, and in what order.</p></div></div>
@can('content.create')
<form class="admin-panel" method="POST" action="{{ route('admin.sections.store') }}">@csrf
    <h2>Add a collection</h2>
    <div class="form-grid">
        <div class="admin-field"><label>Eyebrow</label><input name="eyebrow" maxlength="120" placeholder="EVERYDAY FAVOURITES"></div>
        <div class="admin-field"><label>Heading</label><input name="title" maxlength="180" required></div>
        <div class="admin-field"><label>Short description</label><input name="subtitle" maxlength="300"></div>
        <div class="admin-field"><label>Collection source</label><select name="type" required data-section-type>@foreach($types as $type)<option value="{{ $type }}">{{ str($type)->replace('_', ' ')->title() }}</option>@endforeach</select></div>
        <div class="admin-field" data-category-field><label>Category</label><select name="category_id"><option value="">Choose category</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
        <div class="admin-field"><label>Product count (2–12)</label><input name="product_limit" type="number" min="2" max="12" value="8" required></div>
        <div class="admin-field"><label>Sort order</label><input name="sort_order" type="number" min="0" value="0"></div>
    </div>
    <label class="permission-item"><input type="checkbox" name="is_active" value="1" checked> Show on homepage</label>
    <p style="margin:15px 0 0"><button class="button button-small">Create collection</button></p>
</form>
@endcan
<section class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>Order</th><th>Collection</th><th>Source</th><th>Visibility</th><th>Manage</th></tr></thead><tbody>
@forelse($sections as $section)
<tr><td>{{ $section->sort_order }}</td><td><strong>{{ $section->title }}</strong><br><span class="muted">{{ $section->eyebrow }}</span></td><td>{{ $section->type === 'category' ? $section->category?->name : str($section->type)->replace('_', ' ')->title() }}</td><td><span class="status-pill">{{ $section->is_active ? 'Live' : 'Hidden' }}</span></td><td>
    @can('content.edit')<details><summary class="text-link">Edit</summary><form method="POST" action="{{ route('admin.sections.update', $section) }}" style="padding:12px;min-width:290px">@csrf @method('PUT')
        <div class="admin-field"><label>Eyebrow</label><input name="eyebrow" value="{{ $section->eyebrow }}" maxlength="120"></div>
        <div class="admin-field"><label>Heading</label><input name="title" value="{{ $section->title }}" maxlength="180" required></div>
        <div class="admin-field"><label>Short description</label><input name="subtitle" value="{{ $section->subtitle }}" maxlength="300"></div>
        <div class="admin-field"><label>Collection source</label><select name="type" required>@foreach($types as $type)<option value="{{ $type }}" @selected($section->type === $type)>{{ str($type)->replace('_', ' ')->title() }}</option>@endforeach</select></div>
        <div class="admin-field"><label>Category (for category collections)</label><select name="category_id"><option value="">Choose category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($section->category_id === $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="admin-field"><label>Product count</label><input name="product_limit" type="number" min="2" max="12" value="{{ $section->product_limit }}" required></div>
        <div class="admin-field"><label>Sort order</label><input name="sort_order" type="number" min="0" value="{{ $section->sort_order }}"></div>
        <label class="permission-item"><input type="checkbox" name="is_active" value="1" @checked($section->is_active)> Show on homepage</label><p><button class="button button-small">Save changes</button></p>
    </form>@endcan
    @can('content.delete')<form method="POST" action="{{ route('admin.sections.destroy', $section) }}" onsubmit="return confirm('Remove this homepage collection?')">@csrf @method('DELETE')<button class="remove-link" style="border:0;background:none;margin-top:8px">Delete</button></form>@endcan
</td></tr>
@empty<tr><td colspan="5">No homepage collections configured.</td></tr>@endforelse
</tbody></table></div>{{ $sections->links('components.pagination') }}</section>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-section-type]').forEach((select) => {
    const form = select.closest('form');
    const category = form.querySelector('[data-category-field]');
    const refresh = () => {
        category.hidden = select.value !== 'category';
        category.querySelector('select').required = select.value === 'category';
    };
    select.addEventListener('change', refresh);
    refresh();
});
</script>
@endpush
