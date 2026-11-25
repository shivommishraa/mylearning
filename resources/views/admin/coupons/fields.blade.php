<div class="form-grid">
    <div class="admin-field"><label>Offer code</label><input name="code" value="{{ old('code', $coupon?->code) }}" required maxlength="40"></div>
    <div class="admin-field"><label>Customer-facing description</label><input name="description" value="{{ old('description', $coupon?->description) }}" maxlength="180"></div>
    <div class="admin-field"><label>Offer type</label><select name="type" required><option value="percent" @selected(old('type',$coupon?->type)==='percent')>Percentage</option><option value="fixed" @selected(old('type',$coupon?->type)==='fixed')>Fixed amount</option></select></div>
    <div class="admin-field"><label>Offer value</label><input type="number" step="0.01" min="0.01" name="value" value="{{ old('value',$coupon?->value) }}" required></div>
    <div class="admin-field"><label>Minimum basket (₹)</label><input type="number" step="0.01" min="0" name="minimum_order" value="{{ old('minimum_order',$coupon?->minimum_order ?? 0) }}"></div>
    <div class="admin-field"><label>Maximum discount (₹)</label><input type="number" step="0.01" min="0.01" name="maximum_discount" value="{{ old('maximum_discount',$coupon?->maximum_discount) }}"></div>
    <div class="admin-field"><label>Usage limit</label><input type="number" min="1" name="usage_limit" value="{{ old('usage_limit',$coupon?->usage_limit) }}"></div>
    <div class="admin-field"><label>Valid from</label><input type="datetime-local" name="starts_at" value="{{ old('starts_at',$coupon?->starts_at?->format('Y-m-d\TH:i')) }}"></div>
    <div class="admin-field"><label>Expires</label><input type="datetime-local" name="expires_at" value="{{ old('expires_at',$coupon?->expires_at?->format('Y-m-d\TH:i')) }}"></div>
    <div class="admin-field"><label>Offer image</label><input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP, up to 10 MB.</small></div>
    <div class="admin-field"><label>Or use an image URL</label><input type="url" name="image_url" value="{{ old('image_url', \Illuminate\Support\Str::startsWith($coupon?->image ?? '', ['https://', 'http://']) ? $coupon?->image : '') }}" maxlength="2048"></div>
    @if($coupon?->image)<div class="image-management"><img src="{{ $coupon->image_url }}" alt="{{ $coupon->code }} current offer image"><label class="permission-item"><input type="checkbox" name="remove_image" value="1"> Remove image</label></div>@endif
    <label class="permission-item"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$coupon?->is_active ?? true))> Live in the shop</label>
</div>
