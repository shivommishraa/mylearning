<nav class="account-nav" aria-label="Account navigation">
    <a class="{{ $active === 'overview' ? 'active' : '' }}" href="{{ route('account') }}">Your overview</a>
    <a class="{{ $active === 'orders' ? 'active' : '' }}" href="{{ route('account.orders') }}">Order history</a>
    <a class="{{ $active === 'addresses' ? 'active' : '' }}" href="{{ route('addresses.index') }}">Delivery addresses</a>
    <a class="{{ $active === 'wishlist' ? 'active' : '' }}" href="{{ route('account.wishlist') }}">Saved favourites</a>
    <a href="{{ route('profile.edit') }}">Personal details</a>
    <form method="POST" action="{{ route('logout') }}" style="padding:5px 12px">@csrf<button class="remove-link" style="border:0;background:none;padding:0">Sign out</button></form>
</nav>
