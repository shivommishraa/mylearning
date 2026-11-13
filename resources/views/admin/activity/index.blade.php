@extends('layouts.admin')
@section('title', 'Activity log')
@section('content')
<div class="admin-title"><div><h1>A little store history</h1><p>Recent changes made in the admin panel.</p></div></div>
<section class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>When</th><th>Who</th><th>What happened</th><th>Record</th></tr></thead><tbody>@forelse($logs as $log)<tr><td>{{ $log->created_at->format('d M Y, h:i A') }}</td><td>{{ $log->user?->name ?? 'Account removed' }}</td><td>{{ Str::headline(str_replace('.',' ',$log->action)) }}</td><td>{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : 'Store settings' }}</td></tr>@empty<tr><td colspan="4">Nothing in the activity log just yet.</td></tr>@endforelse</tbody></table></div>{{ $logs->links('components.pagination') }}</section>
@endsection
