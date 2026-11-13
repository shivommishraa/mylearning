<div class="admin-field"><label>Page title</label><input name="title" value="{{ old('title',$page?->title) }}" required maxlength="180"></div>
<div class="admin-field"><label>URL slug (letters, numbers and dashes)</label><input name="slug" value="{{ old('slug',$page?->slug) }}" required maxlength="180"></div>
<div class="admin-field"><label>Page content</label><textarea name="body" rows="8" maxlength="50000">{{ old('body',$page?->body) }}</textarea><small>Plain text is safely displayed with your line breaks preserved.</small></div>
<div class="admin-field"><label>SEO title</label><input name="meta_title" value="{{ old('meta_title',$page?->meta_title) }}" maxlength="180"></div>
<div class="admin-field"><label>SEO description</label><textarea name="meta_description" rows="2" maxlength="320">{{ old('meta_description',$page?->meta_description) }}</textarea></div>
<label class="permission-item" style="margin-bottom:13px"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$page?->is_published ?? true))> Publish this page</label>
