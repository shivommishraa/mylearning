@extends('layouts.admin')
@section('title', $admin->exists ? 'Manage Admin access' : 'Invite Admin')
@section('content')
<div class="admin-title"><div><h1>{{ $admin->exists ? 'Manage Admin access' : 'Invite a trusted Admin' }}</h1><p>Only Super Admins can create Admin users or change their access.</p></div><a class="text-link" href="{{ route('admin.admins.index') }}">← Admin accounts</a></div>
<form class="admin-panel" method="POST" action="{{ $admin->exists ? route('admin.admins.update',$admin) : route('admin.admins.store') }}">@csrf @if($admin->exists)@method('PUT')@endif
    <div class="form-grid"><div class="admin-field"><label>Name</label><input name="name" value="{{ old('name',$admin->name) }}" required></div><div class="admin-field"><label>Email</label><input type="email" name="email" value="{{ old('email',$admin->email) }}" required></div>
        <div class="admin-field"><label>{{ $admin->exists ? 'New password (optional)' : 'Temporary password' }}</label><input type="password" name="password" {{ $admin->exists ? '' : 'required' }} autocomplete="new-password"></div>
        <div class="admin-field"><label>Confirm password</label><input type="password" name="password_confirmation" {{ $admin->exists ? '' : 'required' }} autocomplete="new-password"></div>
    </div>
    <h2 style="font-size:17px;margin:20px 0 5px">Fine-tune their permissions</h2><p class="muted" style="font-size:12px">Choose a module and action. Unchecked actions are not available to this Admin.</p>
    @php($permissionsByModule = $permissions->groupBy(fn($permission) => Str::beforeLast($permission->name, '.')))
    @foreach($permissionsByModule as $module => $modulePermissions)
        <h3 style="font-size:13px;margin:16px 0 7px;text-transform:capitalize">{{ Str::replace('_',' ',$module) }}</h3>
        <div class="permission-grid">
            @foreach($modulePermissions as $permission)
                <label class="permission-item"><input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', $admin->exists ? $admin->permissions->pluck('name')->all() : []), true))>{{ Str::afterLast($permission->name,'.') }}</label>
            @endforeach
        </div>
    @endforeach
    <div style="margin-top:23px"><button class="button">{{ $admin->exists ? 'Save access changes' : 'Create Admin account' }}</button></div>
</form>
@endsection
