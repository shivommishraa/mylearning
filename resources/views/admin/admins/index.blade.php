@extends('layouts.admin')
@section('title', 'Admin access')
@section('content')
<div class="admin-title"><div><h1>People behind the scenes</h1><p>Create Admin accounts and choose exactly which store actions they can take. Super Admin access stays yours alone.</p></div>@can('admins.create')<a class="button button-small" href="{{ route('admin.admins.create') }}">+ Invite an Admin</a>@endcan</div>
<div class="admin-panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>Admin</th><th>Access granted</th><th>Joined</th><th>Manage</th></tr></thead><tbody>
@forelse($admins as $admin)<tr><td><strong>{{ $admin->name }}</strong><br><span class="muted">{{ $admin->email }}</span></td><td>{{ $admin->permissions->count() }} permissions</td><td>{{ $admin->created_at->format('d M Y') }}</td><td><div class="admin-actions">@can('admins.edit')<a class="text-link" href="{{ route('admin.admins.edit',$admin) }}">Manage access</a>@endcan @can('admins.delete')<form method="POST" action="{{ route('admin.admins.destroy',$admin) }}" onsubmit="return confirm('Remove this Admin account?')">@csrf @method('DELETE')<button class="remove-link" style="border:0;background:none">Remove</button></form>@endcan</div></td></tr>@empty<tr><td colspan="4">No Admin accounts yet. Your Super Admin account is kept separate.</td></tr>@endforelse
</tbody></table></div>{{ $admins->links('components.pagination') }}</div>
@endsection
