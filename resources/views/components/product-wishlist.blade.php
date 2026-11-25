@php
    $placement = $placement ?? 'card';
    $isWishlisted = auth()->check() && auth()->user()->wishlist->contains('id', $product->id);
    $buttonClass = $placement === 'detail' ? 'detail-wishlist-button' : 'product-wish';
@endphp

@auth
    <form method="POST" action="{{ route('wishlist.toggle', $product) }}" data-product-action="wishlist" data-product-name="{{ $product->name }}" @if($placement === 'detail') class="detail-wishlist" @endif>
        @csrf
        <button type="submit" class="{{ $buttonClass }}{{ $isWishlisted ? ' is-saved' : '' }}" title="{{ $isWishlisted ? 'Remove from wishlist' : 'Save to wishlist' }}" aria-label="{{ $isWishlisted ? 'Remove ' : 'Add ' }}{{ $product->name }} {{ $isWishlisted ? 'from' : 'to' }} wishlist" aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}">
            <span class="wishlist-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/></svg></span>
            @if($placement === 'detail')
                <span>{{ $isWishlisted ? 'Saved to wishlist' : 'Add to wishlist' }}</span>
            @endif
        </button>
        @if($placement === 'detail')
            <p class="product-action-feedback" data-product-feedback role="status" aria-live="polite" hidden></p>
        @endif
    </form>
@else
    <a class="{{ $buttonClass }} wishlist-login-link" href="{{ route('login') }}" title="Sign in to save to wishlist" aria-label="Sign in to add {{ $product->name }} to your wishlist">
        <span class="wishlist-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/></svg></span>
        @if($placement === 'detail')
            <span>Sign in to save</span>
        @endif
    </a>
@endauth
