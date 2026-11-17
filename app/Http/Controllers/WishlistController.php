<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\StoreCatalogRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        abort_unless(
            $product->is_active
                && $product->category?->is_active
                && ! StoreCatalogRules::isRestricted($product->name.' '.$product->category->name),
            404,
        );
        $result = auth()->user()->wishlist()->toggle($product->id);
        $isWishlisted = (bool) $result['attached'];
        $message = $isWishlisted ? 'Saved to your wishlist.' : 'Removed from your wishlist.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $product->name.' '.$message,
                'is_wishlisted' => $isWishlisted,
            ]);
        }

        return back()->with('success', $message);
    }
}
