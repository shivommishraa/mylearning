@extends('layouts.admin')
@section('title', 'Navigation menus')
@section('content')
<div class="admin-title"><div><h1>Navigation menus</h1><p>Create header and footer menus with submenus. Disabled links and links under disabled parents stay hidden.</p></div></div>
@can('menus.create')<form class="admin-panel" method="POST" action="{{ route('admin.menus.store') }}">@csrf<h2>Add a menu or submenu item</h2>@include('admin.navigation.fields',['item'=>null,'parentItems'=>$parentItems])<button class="button button-small">Add menu item</button></form>@endcan
<section class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>Label</th><th>Parent</th><th>Destination</th><th>Menu</th><th>Order</th><th>Visibility</th><th>Manage</th></tr></thead><tbody>
@forelse($items as $item)<tr><td><strong>{{ $item->label }}</strong></td><td>{{ $item->parent?->label ?? '—' }}</td><td><a href="{{ $item->url }}" class="text-link">{{ Str::limit($item->url,55) }}</a></td><td>{{ ucfirst($item->location) }}</td><td>{{ $item->sort_order }}</td><td><span class="status-pill">{{ $item->is_active ? 'Enabled' : 'Disabled' }}</span></td><td>
    @can('menus.edit')<details><summary class="text-link">Edit</summary><form method="POST" action="{{ route('admin.menus.update',$item) }}" style="padding:12px;min-width:250px">@csrf @method('PUT')@include('admin.navigation.fields',['item'=>$item,'parentItems'=>$parentItems])<button class="button button-small">Save menu item</button></form></details>@endcan
    @can('menus.delete')<form method="POST" action="{{ route('admin.menus.destroy',$item) }}">@csrf @method('DELETE')<button class="remove-link" style="border:0;background:none;margin-top:8px">Remove</button></form>@endcan
</td></tr>@empty<tr><td colspan="7">There are no navigation links yet.</td></tr>@endforelse
</tbody></table></div>{{ $items->links('components.pagination') }}</section>
@endsection
