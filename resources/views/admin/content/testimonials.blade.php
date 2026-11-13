@extends('layouts.admin')
@section('title', 'Testimonials')
@section('content')
<div class="admin-title"><div><h1>Kind words, close to home</h1><p>Share the real notes your neighbours have sent along.</p></div></div>
@can('content.create')<form class="admin-panel" method="POST" action="{{ route('admin.testimonials.store') }}">@csrf<h2>Add a testimonial</h2>@include('admin.content.testimonial-fields',['testimonial'=>null])<button class="button button-small">Add kind words</button></form>@endcan
<div class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>Neighbour</th><th>What they said</th><th>Rating</th><th>Manage</th></tr></thead><tbody>
@forelse($testimonials as $testimonial)<tr><td><strong>{{ $testimonial->name }}</strong><br><span class="muted">{{ $testimonial->location }}</span></td><td>{{ Str::limit($testimonial->quote,100) }}</td><td>{{ str_repeat('★',$testimonial->rating) }}</td><td>
    @can('content.edit')<details><summary class="text-link">Edit</summary><form method="POST" action="{{ route('admin.testimonials.update',$testimonial) }}" style="min-width:280px;padding:12px">@csrf @method('PUT')@include('admin.content.testimonial-fields',['testimonial'=>$testimonial])<button class="button button-small">Save</button></form></details>@endcan
    @can('content.delete')<form method="POST" action="{{ route('admin.testimonials.destroy',$testimonial) }}">@csrf @method('DELETE')<button class="remove-link" style="border:0;background:none;margin-top:7px">Delete</button></form>@endcan
</td></tr>@empty<tr><td colspan="4">No testimonials yet.</td></tr>@endforelse
</tbody></table></div>{{ $testimonials->links('components.pagination') }}</div>
@endsection
