@extends('layouts.admin')
@section('title', 'Enquiries')
@section('content')
<div class="admin-title"><div><h1>A note from a neighbour</h1><p>Every question deserves a thoughtful reply.</p></div></div>
<section class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>From</th><th>Subject</th><th>Message</th><th>Received</th><th>Follow-up</th></tr></thead><tbody>@forelse($enquiries as $enquiry)<tr><td><strong>{{ $enquiry->name }}</strong><br><a class="text-link" href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a><br>{{ $enquiry->phone }}</td><td>{{ $enquiry->subject ?: '—' }}</td><td>{{ Str::limit($enquiry->message,160) }}</td><td>{{ $enquiry->created_at->format('d M, h:i A') }}</td><td>@if($enquiry->read_at)<span class="status-pill">Seen</span>@else<form method="POST" action="{{ route('admin.enquiries.read',$enquiry) }}">@csrf @method('PATCH')<button class="button button-small">Mark seen</button></form>@endif</td></tr>@empty<tr><td colspan="5">No enquiries just yet.</td></tr>@endforelse</tbody></table></div>{{ $enquiries->links('components.pagination') }}</section>
@endsection
