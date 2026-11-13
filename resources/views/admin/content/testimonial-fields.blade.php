<div class="form-grid">
    <div class="admin-field"><label>Name</label><input name="name" value="{{ old('name',$testimonial?->name) }}" required></div>
    <div class="admin-field"><label>Neighbourhood</label><input name="location" value="{{ old('location',$testimonial?->location) }}"></div>
    <div class="admin-field"><label>Rating (1–5)</label><input type="number" name="rating" min="1" max="5" value="{{ old('rating',$testimonial?->rating ?? 5) }}" required></div>
    <div class="admin-field"><label>Quote</label><textarea name="quote" rows="3" required>{{ old('quote',$testimonial?->quote) }}</textarea></div>
    <label class="permission-item"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$testimonial?->is_active ?? true))> Show on homepage</label>
</div>
