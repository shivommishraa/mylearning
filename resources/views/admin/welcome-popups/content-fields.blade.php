@php
    $buttonRows = collect(old('buttons', $popup->exists
        ? $popup->buttons->map(fn ($button) => ['id' => $button->id, 'text' => $button->text, 'url' => $button->url, 'target' => $button->target])->all()
        : []))->filter(fn ($button) => is_array($button))->values();
    $removedImageIds = old('remove_images', []);
    $removedImageIds = is_array($removedImageIds) ? $removedImageIds : [];
@endphp
<div class="form-grid">
    <div class="admin-field">
        <label for="display_mode">Display mode</label>
        <select id="display_mode" name="display_mode" required>
            <option value="image_only" @selected(old('display_mode', $popup->display_mode) === 'image_only')>Only image</option>
            <option value="image_content" @selected(old('display_mode', $popup->display_mode) === 'image_content')>Image + content</option>
            <option value="content_buttons" @selected(old('display_mode', $popup->display_mode) === 'content_buttons')>Only content + buttons</option>
            <option value="image_content_buttons" @selected(old('display_mode', $popup->display_mode) === 'image_content_buttons')>Image + content + buttons</option>
        </select>
    </div>
    <div class="admin-field"><label for="welcome_popup_title">Title (required when content is shown)</label><input id="welcome_popup_title" name="title" value="{{ old('title', $popup->title) }}" maxlength="180"></div>
    <div class="admin-field"><label for="welcome_popup_description">Description / content</label><textarea id="welcome_popup_description" name="description" rows="3" maxlength="1500">{{ old('description', $popup->description) }}</textarea></div>
    <div class="admin-field"><label for="welcome_popup_images">Upload images (up to 8, JPG/PNG/WebP, 10 MB each)</label><input id="welcome_popup_images" type="file" name="image_files[]" accept="image/jpeg,image/png,image/webp" multiple><small>When multiple images are added, visitors can browse them in the popup.</small></div>
    <div class="admin-field">
        <label for="image_position">Image position</label>
        <select id="image_position" name="image_position" required>
            <option value="left" @selected(old('image_position', $popup->image_position) === 'left')>Left</option>
            <option value="right" @selected(old('image_position', $popup->image_position) === 'right')>Right</option>
            <option value="top" @selected(old('image_position', $popup->image_position) === 'top')>Top</option>
        </select>
    </div>
    <div class="admin-field">
        <label for="content_alignment">Content alignment</label>
        <select id="content_alignment" name="content_alignment" required>
            <option value="left" @selected(old('content_alignment', $popup->content_alignment) === 'left')>Left</option>
            <option value="center" @selected(old('content_alignment', $popup->content_alignment) === 'center')>Center</option>
        </select>
    </div>
    <div class="admin-field">
        <label for="button_style">Button style</label>
        <select id="button_style" name="button_style" required>
            <option value="solid" @selected(old('button_style', $popup->button_style) === 'solid')>Solid</option>
            <option value="outline" @selected(old('button_style', $popup->button_style) === 'outline')>Outline</option>
        </select>
    </div>
    <div class="admin-field"><label for="accent_color">Button accent colour</label><input id="accent_color" type="color" name="accent_color" value="{{ old('accent_color', $popup->accent_color ?: '#246b45') }}" required></div>
</div>
<label class="permission-item"><input type="checkbox" name="show_close_button" value="1" @checked(old('show_close_button', $popup->show_close_button ?? true))> Show close button</label>

@if($popup->images->isNotEmpty())
    <div class="admin-field" style="margin-top:16px"><strong>Current images</strong><small>Check an image to remove it. New uploads are added to the end of the carousel.</small></div>
    <div class="welcome-popup-admin-images">
        @foreach($popup->images as $image)
            <label class="welcome-popup-admin-image">
                <img src="{{ $image->image_url }}" alt="Popup image {{ $loop->iteration }}">
                <span><input type="checkbox" name="remove_images[]" value="{{ $image->id }}" @checked(in_array($image->id, $removedImageIds))> Remove image</span>
            </label>
        @endforeach
    </div>
@endif

<div class="admin-field" style="margin-top:18px">
    <strong>Custom buttons</strong>
    <small>Add up to five buttons with their destination and whether to open it in a new tab.</small>
</div>
<div class="welcome-popup-admin-buttons" data-welcome-button-list data-next-index="{{ count($buttonRows) }}">
    @foreach($buttonRows as $index => $button)
        <fieldset class="welcome-popup-admin-button" data-welcome-button-row>
            @if(!empty($button['id']))<input type="hidden" name="buttons[{{ $index }}][id]" value="{{ $button['id'] }}">@endif
            <div class="admin-field"><label>Button text</label><input name="buttons[{{ $index }}][text]" value="{{ $button['text'] ?? '' }}" maxlength="60" required></div>
            <div class="admin-field"><label>Link / URL</label><input name="buttons[{{ $index }}][url]" value="{{ $button['url'] ?? '' }}" maxlength="2048" placeholder="/shop or https://example.com" required></div>
            <div class="admin-field"><label>Open link in</label><select name="buttons[{{ $index }}][target]" required><option value="_self" @selected(($button['target'] ?? '_self') === '_self')>Same tab</option><option value="_blank" @selected(($button['target'] ?? '_self') === '_blank')>New tab</option></select></div>
            <button class="remove-link" type="button" data-welcome-button-remove>Remove button</button>
        </fieldset>
    @endforeach
</div>
<button class="button button-light button-small" type="button" data-welcome-button-add>Add a button</button>

<template data-welcome-button-template>
    <fieldset class="welcome-popup-admin-button" data-welcome-button-row>
        <div class="admin-field"><label>Button text</label><input name="buttons[__INDEX__][text]" maxlength="60" required></div>
        <div class="admin-field"><label>Link / URL</label><input name="buttons[__INDEX__][url]" maxlength="2048" placeholder="/shop or https://example.com" required></div>
        <div class="admin-field"><label>Open link in</label><select name="buttons[__INDEX__][target]" required><option value="_self">Same tab</option><option value="_blank">New tab</option></select></div>
        <button class="remove-link" type="button" data-welcome-button-remove>Remove button</button>
    </fieldset>
</template>
