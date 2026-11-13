@extends('layouts.admin')
@section('title', 'Homepage banners')
@section('content')
<div class="admin-title"><div><h1>Set the scene</h1><p>Homepage banners, in the order you want them seen.</p></div></div>
@can('content.create')<form class="admin-panel" method="POST" enctype="multipart/form-data" action="{{ route('admin.banners.store') }}">@csrf<h2>Add a banner</h2>@include('admin.content.banner-fields', ['banner' => null])<button class="button button-small">Create banner</button></form>@endcan
<div class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>Order</th><th>Banner</th><th>Visibility</th><th>Manage</th></tr></thead><tbody>
@forelse($banners as $banner)<tr><td>{{ $banner->sort_order }}</td><td>@if($banner->image_url)<img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" style="width:72px;height:44px;object-fit:cover;border-radius:4px;margin-bottom:6px">@endif<br><strong>{{ $banner->title }}</strong><br><span class="muted">{{ $banner->eyebrow }}</span></td><td><span class="status-pill">{{ $banner->is_active ? 'Live' : 'Hidden' }}</span></td><td>
    @can('content.edit')<details><summary class="text-link">Edit banner</summary><form method="POST" enctype="multipart/form-data" action="{{ route('admin.banners.update',$banner) }}" style="padding:12px;min-width:300px">@csrf @method('PUT')@include('admin.content.banner-fields',['banner'=>$banner])<button class="button button-small">Save changes</button></form></details>@endcan
    @can('content.delete')<form method="POST" action="{{ route('admin.banners.destroy',$banner) }}">@csrf @method('DELETE')<button class="remove-link" style="border:0;background:none;margin-top:8px">Delete</button></form>@endcan
</td></tr>@empty<tr><td colspan="4">No banners created yet.</td></tr>@endforelse
</tbody></table></div>{{ $banners->links('components.pagination') }}</div>
@endsection
