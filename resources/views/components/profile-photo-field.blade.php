@php($photoName = $profileUser->name ?: old('name', 'Customer'))
<div class="profile-photo-field" data-photo-editor>
    <button class="profile-photo-current" type="button" data-photo-preview data-photo-open aria-label="View and edit profile photo">
        @if($profileUser->profile_photo_path)
            <img src="{{ route('profile.photo', $profileUser) }}" alt="{{ $photoName }} profile photo" data-photo-preview-image>
        @else
            <span data-photo-placeholder>{{ str($photoName)->substr(0, 1)->upper() }}</span>
        @endif
    </button>
    <div class="profile-photo-controls">
        <strong>Profile photo <span class="optional-note">Optional</span></strong>
        <p>Choose the area to keep. Click your current photo to crop it again.</p>
        <input class="profile-photo-input" id="{{ $photoInputId }}" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" data-photo-input aria-describedby="{{ $photoInputId }}-help">
        <label class="button button-light button-small" for="{{ $photoInputId }}">Choose photo</label>
        <small id="{{ $photoInputId }}-help">JPG, PNG or WebP · up to 5 MB</small>
        @if($profileUser->profile_photo_path)
            <label class="profile-remove-option">
                <input type="checkbox" name="remove_profile_photo" value="1" @checked(old('remove_profile_photo')) data-photo-remove>
                Remove current photo
            </label>
        @endif
        <p class="profile-photo-status" data-photo-status aria-live="polite"></p>
        @error('profile_photo')<p class="field-error">{{ $message }}</p>@enderror
        @error('image_file')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="photo-crop-backdrop" data-crop-backdrop hidden>
        <section class="photo-crop-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $photoInputId }}-crop-title">
            <header>
                <div>
                    <span class="eyebrow">Photo editor</span>
                    <h2 id="{{ $photoInputId }}-crop-title">Make it yours</h2>
                    <p>Drag the square to move it, or drag its corner to change how much is cropped.</p>
                </div>
                <button class="photo-crop-close" type="button" aria-label="Close photo editor" data-crop-cancel>&times;</button>
            </header>
            <canvas class="photo-crop-canvas" width="320" height="320" data-crop-canvas aria-label="Square profile photo crop preview"></canvas>
            <div class="photo-crop-actions">
                <button class="button button-light button-small" type="button" data-crop-rotate="-90">Rotate left</button>
                <button class="button button-light button-small" type="button" data-crop-rotate="90">Rotate right</button>
                <span class="photo-crop-spacer"></span>
                <button class="button button-light button-small" type="button" data-crop-cancel>Cancel</button>
                <button class="button button-small" type="button" data-crop-apply>Use photo</button>
            </div>
            <p class="photo-crop-error" data-crop-error role="alert" hidden></p>
        </section>
    </div>
</div>
