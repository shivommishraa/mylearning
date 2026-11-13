<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.admins.index', ['admins' => User::role('Admin')->with('permissions')->latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.admins.form', ['admin' => new User, 'permissions' => Permission::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, true);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->assignRole('Admin');
        $user->syncPermissions($data['permissions'] ?? []);
        $this->log('admin.created', $user);

        return redirect()->route('admin.admins.index')->with('success', 'Admin account created.');
    }

    public function edit(User $admin): View
    {
        abort_unless($admin->hasRole('Admin'), 404);

        return view('admin.admins.form', ['admin' => $admin->load('permissions'), 'permissions' => Permission::orderBy('name')->get()]);
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        abort_unless($admin->hasRole('Admin') && $admin->id !== auth()->id(), 404);
        $data = $this->validated($request);
        $admin->fill(['name' => $data['name'], 'email' => $data['email']]);

        if (! empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }

        $admin->save();
        $admin->syncPermissions($data['permissions'] ?? []);
        $this->log('admin.updated', $admin);

        return redirect()->route('admin.admins.index')->with('success', 'Admin account updated.');
    }

    public function destroy(User $admin): RedirectResponse
    {
        abort_unless($admin->hasRole('Admin') && $admin->id !== auth()->id(), 404);
        $this->log('admin.deleted', $admin);
        $admin->delete();

        return back()->with('success', 'Admin account deleted.');
    }

    private function validated(Request $request, bool $creating = false): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'.($request->route('admin') ? ','.$request->route('admin')->id : '')],
            'password' => [$creating ? 'required' : 'nullable', 'confirmed', Rules\Password::defaults()],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);
    }

    private function log(string $action, User $admin): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => User::class,
            'subject_id' => $admin->id,
        ]);
    }
}
