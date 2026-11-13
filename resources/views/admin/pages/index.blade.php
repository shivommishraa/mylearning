@extends('layouts.admin')
@section('title', 'Content pages')
@section('content')
<div class="admin-title"><div><h1>Your store's story, in your words</h1><p>Edit the helpful pages customers can find in the footer.</p></div></div>
@can('pages.create')<form class="admin-panel" method="POST" action="{{ route('admin.pages.store') }}">@csrf<h2>Add a page</h2>@include('admin.pages.fields',['page'=>null])<button class="button button-small">Create page</button></form>@endcan
<div class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>Page</th><th>URL</th><th>Last updated</th><th>Visibility</th><th>Manage</th></tr></thead><tbody>
@forelse($pages as $page)<tr><td><strong>{{ $page->title }}</strong></td><td><a class="text-link" href="{{ route('pages.show',$page->slug) }}" target="_blank" rel="noopener">/pages/{{ $page->slug }} ↗</a></td><td>{{ $page->updated_at->format('d M Y') }}</td><td><span class="status-pill">{{ $page->is_published ? 'Published' : 'Draft' }}</span></td><td>
    @can('pages.edit')<details><summary class="text-link">Edit</summary><form method="POST" action="{{ route('admin.pages.update',$page) }}" style="padding:12px;min-width:300px">@csrf @method('PUT')@include('admin.pages.fields',['page'=>$page])<button class="button button-small">Save page</button></form></details>@endcan
    @can('pages.delete')<form method="POST" action="{{ route('admin.pages.destroy',$page) }}" onsubmit="return confirm('Delete this page?')">@csrf @method('DELETE')<button class="remove-link" style="border:0;background:none;margin-top:7px">Delete</button></form>@endcan
</td></tr>@empty<tr><td colspan="5">No pages yet.</td></tr>@endforelse
</tbody></table></div>{{ $pages->links('components.pagination') }}</div>
@endsection
