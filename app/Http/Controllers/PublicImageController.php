<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicImageController extends Controller
{
    public function __invoke(string $path): StreamedResponse
    {
        abort_unless(
            preg_match('#\A(?:banners|categories|coupons|products)/[A-Za-z0-9_-][A-Za-z0-9._-]*\.(?:jpe?g|png|webp)\z#i', $path) === 1,
            404,
        );
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path, headers: [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
