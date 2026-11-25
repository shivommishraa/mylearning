<nav class="account-nav" aria-label="Account navigation">
    @foreach(($navigationItems['account'] ?? collect()) as $item)
        @php($itemIsActive = request()->url() === url($item->url))
        <a class="{{ $itemIsActive ? 'active' : '' }}" href="{{ $item->url }}">{{ $item->label }}</a>
        @if($item->children->isNotEmpty())
            <div class="account-nav-submenu">
                @foreach($item->children as $child)
                    @php($childIsActive = request()->url() === url($child->url))
                    <a class="{{ $childIsActive ? 'active' : '' }}" href="{{ $child->url }}">{{ $child->label }}</a>
                @endforeach
            </div>
        @endif
    @endforeach
    <form method="POST" action="{{ route('logout') }}" style="padding:5px 12px">@csrf<button class="remove-link" style="border:0;background:none;padding:0">Sign out</button></form>
</nav>
