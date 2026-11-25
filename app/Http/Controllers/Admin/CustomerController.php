<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Enquiry;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\CustomerAddressManager;
use App\Support\ProfilePhotoManager;
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
        return view('admin.customers.form', ['customer' => new User, 'address' => null]);
    }

    public function store(
        Request $request,
        ProfilePhotoManager $photos,
        CustomerAddressManager $addresses,
    ): RedirectResponse {
        $data = $this->validatedCustomer($request, $addresses, creating: true);
        $photoPath = $photos->save($data['profile_photo'] ?? null, null);
        $customer = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'father_husband_name' => $data['father_husband_name'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'profile_photo_path' => $photoPath,
            'password' => Hash::make($data['password']),
        ]);
        $addresses->syncProfileAddress($customer, $data);

        if (SiteSetting::getValue('require_email_verification', '0') === '1' && filled($customer->email)) {
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
            'address' => app(CustomerAddressManager::class)->primaryAddress($customer),
        ]);
    }

    public function edit(User $customer): View
    {
        $this->ensureCustomer($customer);

        return view('admin.customers.form', [
            'customer' => $customer,
            'address' => app(CustomerAddressManager::class)->primaryAddress($customer),
        ]);
    }

    public function update(
        Request $request,
        User $customer,
        ProfilePhotoManager $photos,
        CustomerAddressManager $addresses,
    ): RedirectResponse {
        $this->ensureCustomer($customer);
        $data = $this->validatedCustomer($request, $addresses, $customer);
        $emailChanged = $customer->email !== $data['email'];
        $previousPhoto = $customer->profile_photo_path;
        $newPhoto = $photos->save(
            $data['profile_photo'] ?? null,
            $previousPhoto,
            $request->boolean('remove_profile_photo'),
        );

        $customer->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'father_husband_name' => $data['father_husband_name'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'profile_photo_path' => $newPhoto,
        ]);

        if ($emailChanged) {
            $customer->email_verified_at = null;
        }

        if (filled($data['password'] ?? null)) {
            $customer->password = Hash::make($data['password']);
        }

        $customer->save();
        $photos->deleteReplaced($previousPhoto, $newPhoto);
        $addresses->syncProfileAddress($customer, $data);

        if ($emailChanged && SiteSetting::getValue('require_email_verification', '0') === '1' && filled($customer->email)) {
            $customer->sendEmailVerificationNotification();
        }

        $this->log('customer.updated', $customer);

        $destination = auth()->user()->can('customers.view')
            ? route('admin.customers.show', $customer)
            : route('admin.customers.edit', $customer);

        return redirect($destination)->with('success', 'Customer account updated.');
    }

    public function destroy(User $customer, ProfilePhotoManager $photos): RedirectResponse
    {
        $this->ensureCustomer($customer);

        if ($customer->orders()->exists()) {
            return back()->with('error', 'This customer has order history and cannot be deleted.');
        }

        $profilePhoto = $customer->profile_photo_path;
        $this->log('customer.deleted', $customer);
        $customer->delete();
        $photos->deleteReplaced($profilePhoto, null);

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

    private function validatedCustomer(
        Request $request,
        CustomerAddressManager $addresses,
        ?User $customer = null,
        bool $creating = false,
    ): array {
        $phoneRules = ['nullable', 'string', 'max:20'];
        if ($request->filled('phone') && (! $customer || $request->input('phone') !== $customer->phone)) {
            $phoneRules[] = Rule::unique('users', 'phone')->ignore($customer?->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($customer?->id)],
            'phone' => $phoneRules,
            'father_husband_name' => ['nullable', 'string', 'max:120'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', Rule::in(['female', 'male', 'other', 'prefer_not_to_say'])],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_profile_photo' => ['nullable', 'boolean'],
            'password' => [$creating ? 'required' : 'nullable', 'confirmed', Rules\Password::defaults()],
        ] + $addresses->validationRules());
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
