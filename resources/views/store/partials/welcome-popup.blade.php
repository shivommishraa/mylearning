<div
    class="welcome-popup-backdrop"
    hidden
    aria-hidden="true"
    data-welcome-popup
    data-popup-id="{{ $welcomePopup->id }}"
    data-popup-version="{{ $welcomePopup->updated_at?->timestamp ?? 0 }}"
    data-display-frequency="{{ $welcomePopup->display_frequency }}"
    data-display-delay="{{ $welcomePopup->display_delay }}"
>
    <section
        class="welcome-popup-dialog welcome-popup--{{ str_replace('_', '-', $welcomePopup->display_mode) }} welcome-popup--image-{{ $welcomePopup->image_position }} welcome-popup--align-{{ $welcomePopup->content_alignment }}"
        role="dialog"
        aria-modal="true"
        tabindex="-1"
        style="--welcome-accent: {{ $welcomePopup->accent_color }}"
        @if($welcomePopup->displaysContent() && filled($welcomePopup->title)) aria-labelledby="welcome-popup-title-{{ $welcomePopup->id }}" @else aria-label="Welcome" @endif
    >
        @if($welcomePopup->show_close_button)
            <button type="button" class="welcome-popup-close" data-welcome-close aria-label="Close welcome popup">×</button>
        @endif

        @if($welcomePopup->displaysImages())
            <div class="welcome-popup-visual" aria-label="Welcome images">
                @foreach($welcomePopup->images as $image)
                    <div class="welcome-popup-image {{ $loop->first ? 'is-active' : '' }}" data-welcome-image aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                        <img src="{{ $image->image_url }}" alt="Welcome image {{ $loop->iteration }}" @if(!$loop->first) loading="lazy" @endif>
                    </div>
                @endforeach
                @if($welcomePopup->images->count() > 1)
                    <div class="welcome-popup-image-controls" aria-label="Welcome image controls">
                        <button type="button" data-welcome-image-prev aria-label="Previous welcome image">‹</button>
                        <div class="welcome-popup-image-dots">
                            @foreach($welcomePopup->images as $index => $image)
                                <button type="button" class="{{ $loop->first ? 'is-active' : '' }}" data-welcome-image-dot="{{ $index }}" aria-label="Show welcome image {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                        <button type="button" data-welcome-image-next aria-label="Next welcome image">›</button>
                    </div>
                @endif
            </div>
        @endif

        @if($welcomePopup->displaysContent())
            <div class="welcome-popup-copy">
                @if(filled($welcomePopup->title))
                    <h2 id="welcome-popup-title-{{ $welcomePopup->id }}">{{ $welcomePopup->title }}</h2>
                @endif
                @if(filled($welcomePopup->description))
                    <p>{{ $welcomePopup->description }}</p>
                @endif
                @if($welcomePopup->displaysButtons())
                    <div class="welcome-popup-actions">
                        @foreach($welcomePopup->buttons as $button)
                            <a class="welcome-popup-button welcome-popup-button--{{ $welcomePopup->button_style }}" href="{{ $button->url }}" target="{{ $button->target }}" @if($button->target === '_blank') rel="noopener noreferrer" @endif>{{ $button->text }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </section>
</div>
