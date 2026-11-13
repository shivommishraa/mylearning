<div class="form-grid">
    <div class="admin-field"><label>Label</label><input class="field" name="label" maxlength="40" value="{{ old('label', $address?->label ?? 'Home') }}" required></div>
    <div class="admin-field"><label>Recipient name</label><input class="field" name="recipient_name" maxlength="120" value="{{ old('recipient_name', $address?->recipient_name) }}" required></div>
    <div class="admin-field"><label>Phone</label><input class="field" name="phone" maxlength="20" value="{{ old('phone', $address?->phone) }}" required></div>
    <div class="admin-field"><label>Postal code</label><input class="field" name="postal_code" maxlength="12" value="{{ old('postal_code', $address?->postal_code) }}" required></div>
    <div class="admin-field" style="grid-column:1/-1"><label>Address</label><input class="field" name="address_line" maxlength="255" value="{{ old('address_line', $address?->address_line) }}" required></div>
    <div class="admin-field"><label>Landmark</label><input class="field" name="landmark" maxlength="160" value="{{ old('landmark', $address?->landmark) }}"></div>
    <div class="admin-field"><label>City</label><input class="field" name="city" maxlength="100" value="{{ old('city', $address?->city ?? 'Greater Noida') }}" required></div>
    <div class="admin-field"><label>State</label><input class="field" name="state" maxlength="100" value="{{ old('state', $address?->state ?? 'Uttar Pradesh') }}" required></div>
    <label class="permission-item"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address?->is_default))> Make this my default address</label>
</div>
