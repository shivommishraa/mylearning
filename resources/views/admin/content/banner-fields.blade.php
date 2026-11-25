<div class="form-grid">
    <div class="admin-field"><label>Small heading (optional)</label><input name="eyebrow" value="{{ old('eyebrow',$banner?->eyebrow) }}"></div>
    <div class="admin-field"><label>Headline (optional)</label><input name="title" value="{{ old('title',$banner?->title) }}"></div>
    <div class="admin-field"><label>Short description (optional)</label><input name="subtitle" value="{{ old('subtitle',$banner?->subtitle) }}"></div>
    <div class="admin-field"><label>Upload banner image <span aria-hidden="true">*</span></label><input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"><small>Required for new banners (upload an image or enter an image URL). JPG, PNG or WebP, up to 10 MB.</small></div>
    <div class="admin-field"><label>Or use an image URL</label><input type="url" name="image_url" value="{{ old('image_url', \Illuminate\Support\Str::startsWith($banner?->image ?? '', ['https://', 'http://']) ? $banner?->image : '') }}"></div>
    @if($banner?->image)<div class="image-management"><img src="{{ $banner->image_url }}" alt="{{ $banner->title ?: 'Current banner' }}"></div>@endif
    <div class="admin-field"><label>Banner link (optional)</label><input name="button_url" value="{{ old('button_url',$banner?->button_url) }}" placeholder="/shop or https://example.com"></div>
    <div class="admin-field"><label>Link opens in</label><select name="link_target"><option value="_self" @selected(old('link_target',$banner?->link_target ?? '_self') === '_self')>Same tab</option><option value="_blank" @selected(old('link_target',$banner?->link_target ?? '_self') === '_blank')>New tab</option></select></div>
    <div class="admin-field"><label>Link label (optional)</label><input name="button_text" value="{{ old('button_text',$banner?->button_text) }}"></div>
    <div class="admin-field"><label>Sort order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order',$banner?->sort_order ?? 0) }}"></div>
    <label class="permission-item"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$banner?->is_active ?? true))> Show on homepage</label>
</div>
