@php
    $category = $product->category?->slug ?? '';
    $tone = match (true) {
        str_contains($category, 'atta'), str_contains($category, 'dal'), str_contains($category, 'grocery') => ['#f3ead1', '#97713d', '#fffaf0'],
        str_contains($category, 'oil') => ['#edf0cd', '#738045', '#fbf7da'],
        str_contains($category, 'snack'), str_contains($category, 'biscuit') => ['#f5dfc4', '#a85e39', '#fff2dd'],
        str_contains($category, 'tea'), str_contains($category, 'coffee') => ['#e7dfd1', '#66513e', '#f4eee2'],
        str_contains($category, 'drink'), str_contains($category, 'packaged') => ['#dceae6', '#366e66', '#eaf3ef'],
        str_contains($category, 'care'), str_contains($category, 'clean') => ['#dce9e9', '#387f80', '#edf5f2'],
        str_contains($category, 'toy') => ['#f4dfd8', '#b35440', '#fff0e8'],
        str_contains($category, 'stationery') => ['#e2e8f0', '#506b8b', '#f1f4f8'],
        default => ['#e9eadb', '#78825a', '#f7f5e8'],
    };
    $package = match (true) {
        str_contains($category, 'oil') => 'bottle',
        str_contains($category, 'care'), str_contains($category, 'clean') => 'bottle',
        str_contains($category, 'drink') => 'bottle',
        str_contains($category, 'toy') => 'toy',
        str_contains($category, 'stationery') => 'book',
        str_contains($category, 'snack'), str_contains($category, 'biscuit'), str_contains($category, 'atta'), str_contains($category, 'dal') => 'pouch',
        default => 'jar',
    };
@endphp
<svg class="product-art" viewBox="0 0 360 260" role="img" aria-label="{{ $product->name }} pack illustration" xmlns="http://www.w3.org/2000/svg">
    <rect width="360" height="260" rx="8" fill="{{ $tone[0] }}"/>
    <circle cx="181" cy="128" r="95" fill="{{ $tone[2] }}"/>
    <ellipse cx="180" cy="221" rx="86" ry="12" fill="#243b3020"/>
    @if($package === 'bottle')
        <path d="M151 62h58v24l17 18v103c0 11-8 19-19 19h-54c-11 0-19-8-19-19V104l17-18z" fill="{{ $tone[1] }}"/>
        <path d="M160 53h40v17h-40z" rx="4" fill="#d2b88a"/>
        <path d="M144 121h72v67h-72z" rx="5" fill="{{ $tone[2] }}"/>
    @elseif($package === 'toy')
        <path d="M83 171h190l-16-46c-3-9-11-15-21-15h-47l-25-30H126l-22 34z" fill="{{ $tone[1] }}"/>
        <path d="M130 112l17-24h32l19 24z" fill="{{ $tone[2] }}"/>
        <circle cx="127" cy="176" r="20" fill="#293b36"/><circle cx="127" cy="176" r="8" fill="#e8e3d4"/>
        <circle cx="235" cy="176" r="20" fill="#293b36"/><circle cx="235" cy="176" r="8" fill="#e8e3d4"/>
    @elseif($package === 'book')
        <path d="M111 67h108c16 0 28 12 28 28v111c0 6-7 10-12 6-9-7-17-10-28-10H111z" fill="{{ $tone[1] }}"/>
        <path d="M219 67h18c10 0 18 8 18 18v121l-10-7c-8-5-16-7-26-7z" fill="{{ $tone[2] }}"/>
        <path d="M130 100h69M130 118h69M130 136h54" stroke="{{ $tone[2] }}" stroke-width="5" stroke-linecap="round"/>
    @elseif($package === 'jar')
        <path d="M139 93h82l13 17v94c0 10-8 18-18 18h-72c-10 0-18-8-18-18v-94z" fill="{{ $tone[1] }}"/>
        <path d="M142 78h76v21h-76z" rx="4" fill="#d5c7a6"/>
        <path d="M131 127h98v55h-98z" fill="{{ $tone[2] }}"/>
    @else
        <path d="M126 77l21-10h66l21 10-8 16v112l-16 14h-60l-16-14V93z" fill="{{ $tone[1] }}"/>
        <path d="M134 94h92v94h-92z" fill="{{ $tone[2] }}"/>
    @endif
    <text x="180" y="139" text-anchor="middle" fill="{{ $tone[1] }}" font-family="DM Sans,sans-serif" font-size="12" font-weight="700" letter-spacing="1">{{ Str::upper(Str::limit($product->brand?->name ?: 'SSG MART', 18, '')) }}</text>
    <text x="180" y="160" text-anchor="middle" fill="#25382f" font-family="DM Sans,sans-serif" font-size="12" font-weight="700">{{ Str::limit($product->name, 23) }}</text>
    <text x="180" y="177" text-anchor="middle" fill="#68796d" font-family="DM Sans,sans-serif" font-size="9">{{ $product->unit }}</text>
    <text x="180" y="241" text-anchor="middle" fill="#546456" font-family="DM Sans,sans-serif" font-size="8" font-weight="700" letter-spacing="2">SSG MART · INDIA</text>
</svg>
