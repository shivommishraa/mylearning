<div class="form-grid">
    <div class="admin-field"><label>Brand name</label><input name="name" value="{{ old('name',$brand?->name) }}" required maxlength="120"></div>
    <div class="admin-field"><label>Logo URL</label><input type="url" name="logo" value="{{ old('logo',$brand?->logo) }}" maxlength="2048"></div>
    <div class="admin-field"><label>Description</label><input name="description" value="{{ old('description',$brand?->description) }}" maxlength="1000"></div>
    <label class="permission-item"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$brand?->is_active ?? true))> Show in the store</label>
</div>
