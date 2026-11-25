<?php

namespace App\Support;

use App\Models\Address;
use App\Models\User;

class CustomerAddressManager
{
    public function validationRules(): array
    {
        return [
            'address_line' => ['nullable', 'required_with:city,state,postal_code', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:160'],
            'city' => ['nullable', 'required_with:address_line,state,postal_code', 'string', 'max:100'],
            'state' => ['nullable', 'required_with:address_line,city,postal_code', 'string', 'max:100'],
            'postal_code' => ['nullable', 'required_with:address_line,city,state', 'string', 'max:12'],
            'address_phone' => ['nullable', 'string', 'max:20'],
            'remove_address' => ['nullable', 'boolean'],
        ];
    }

    public function primaryAddress(User $user): ?Address
    {
        return $user->addresses()->where('is_default', true)->first()
            ?? $user->addresses()->oldest('id')->first();
    }

    public function syncProfileAddress(User $user, array $data): void
    {
        $address = $this->primaryAddress($user);

        if (filter_var($data['remove_address'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            if ($address) {
                $wasDefault = $address->is_default;
                $address->delete();

                if ($wasDefault || ! $user->addresses()->where('is_default', true)->exists()) {
                    $user->addresses()->oldest('id')->first()?->update(['is_default' => true]);
                }
            }

            return;
        }

        if (! filled($data['address_line'] ?? null)) {
            return;
        }

        $values = [
            'label' => $address?->label ?? 'Home',
            'recipient_name' => $user->name,
            'phone' => filled($data['address_phone'] ?? null)
                ? $data['address_phone']
                : ($user->phone ?: ($address?->phone ?? '')),
            'address_line' => $data['address_line'],
            'landmark' => $data['landmark'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'postal_code' => $data['postal_code'],
            'is_default' => true,
        ];

        if ($address) {
            $user->addresses()
                ->where('is_default', true)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
            $address->update($values);

            return;
        }

        $user->addresses()->where('is_default', true)->update(['is_default' => false]);
        $user->addresses()->create($values);
    }
}
