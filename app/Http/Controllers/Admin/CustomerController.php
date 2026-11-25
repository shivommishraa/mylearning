<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Enquiry;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $search = trim((string) $request->query('q', ''));
        $customers = User::whereDoesntHave('roles')
            ->withCount('orders')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.customers.form', ['customer' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedCustomer($request, creating: true);
        $customer = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        if (SiteSetting::getValue('require_email_verification', '0') === '1') {
            event(new Registered($customer));
        }

        $this->log('customer.created', $customer);

        $destination = auth()->user()->can('customers.view')
            ? route('admin.customers.show', $customer)
            : route('admin.customers.create');

        return redirect($destination)->with('success', 'Customer account created.');
    }

    public function show(User $customer): View
    {
        $this->ensureCustomer($customer);

        return view('admin.customers.show', [
            'customer' => $customer->loadCount('orders'),
            'orders' => $customer->orders()->latest()->take(10)->get(),
        ]);
    }

    public function edit(User $customer): View
    {
        $this->ensureCustomer($customer);

        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, User $customer): RedirectResponse
    {
        $this->ensureCustomer($customer);
        $data = $this->validatedCustomer($request, $customer);
        $emailChanged = $customer->email !== $data['email'];

        $customer->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if ($emailChanged) {
            $customer->email_verified_at = null;
        }

        if (filled($data['password'] ?? null)) {
            $customer->password = Hash::make($data['password']);
        }

        $customer->save();

        if ($emailChanged && SiteSetting::getValue('require_email_verification', '0') === '1') {
            $customer->sendEmailVerificationNotification();
        }

        $this->log('customer.updated', $customer);

        $destination = auth()->user()->can('customers.view')
            ? route('admin.customers.show', $customer)
            : route('admin.customers.edit', $customer);

        return redirect($destination)->with('success', 'Customer account updated.');
    }

    public function destroy(User $customer): RedirectResponse
    {
        $this->ensureCustomer($customer);

        if ($customer->orders()->exists()) {
            return back()->with('error', 'This customer has order history and cannot be deleted.');
        }

        $this->log('customer.deleted', $customer);
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer account deleted.');
    }

    public function enquiries(): View
    {
        return view('admin.enquiries.index', ['enquiries' => Enquiry::latest()->paginate(20)]);
    }

    public function markEnquiryRead(Enquiry $enquiry)
    {
        $enquiry->update(['read_at' => now()]);

        return back()->with('success', 'Enquiry marked as read.');
    }

    public function activity(): View
    {
        return view('admin.activity.index', ['logs' => ActivityLog::with('user')->latest()->paginate(25)]);
    }

    private function validatedCustomer(Request $request, ?User $customer = null, bool $creating = false): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($customer?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$creating ? 'required' : 'nullable', 'confirmed', Rules\Password::defaults()],
        ]);
    }

    private function ensureCustomer(User $customer): void
    {
        abort_if($customer->roles()->exists(), 404);
    }

    private function log(string $action, User $customer): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => User::class,
            'subject_id' => $customer->id,
        ]);
    }
}
