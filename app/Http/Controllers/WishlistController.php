<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\StoreCatalogRules;
use Illuminate\Http\RedirectResponse;

class WishlistController extends Controller
{
    public function toggle(Product $product): RedirectResponse
    {
        abort_unless(
            $product->is_active
                && $product->category?->is_active
                && ! StoreCatalogRules::isRestricted($product->name.' '.$product->category->name),
            404,
        );
        $result = auth()->user()->wishlist()->toggle($product->id);

        return back()->with('success', $result['attached'] ? 'Saved to your wishlist.' : 'Removed from your wishlist.');
    }
}
