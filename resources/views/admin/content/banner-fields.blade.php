<div class="form-grid">
    <div class="admin-field"><label>Small heading</label><input name="eyebrow" value="{{ old('eyebrow',$banner?->eyebrow) }}"></div>
    <div class="admin-field"><label>Headline</label><input name="title" value="{{ old('title',$banner?->title) }}" required></div>
    <div class="admin-field"><label>Short description</label><input name="subtitle" value="{{ old('subtitle',$banner?->subtitle) }}"></div>
    <div class="admin-field"><label>Upload banner image</label><input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP, up to 5 MB.</small></div>
    <div class="admin-field"><label>Or use an image URL</label><input type="url" name="image_url" value="{{ old('image_url', \Illuminate\Support\Str::startsWith($banner?->image ?? '', ['https://', 'http://']) ? $banner?->image : '') }}"></div>
    @if($banner?->image)<div class="image-management"><img src="{{ $banner->image_url }}" alt="{{ $banner->title }} current banner"><label class="permission-item"><input type="checkbox" name="remove_image" value="1"> Remove current image</label></div>@endif
    <div class="admin-field"><label>Button label</label><input name="button_text" value="{{ old('button_text',$banner?->button_text) }}"></div>
    <div class="admin-field"><label>Button destination</label><input name="button_url" value="{{ old('button_url',$banner?->button_url) }}"></div>
    <div class="admin-field"><label>Sort order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order',$banner?->sort_order ?? 0) }}"></div>
    <label class="permission-item"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$banner?->is_active ?? true))> Show on homepage</label>
</div>
