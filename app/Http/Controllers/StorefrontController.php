<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\HomeSection;
use App\Models\Product;
use App\Models\Testimonial;
use App\Support\StoreCatalogRules;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(): View
    {
        $sections = HomeSection::where('is_active', true)
            ->where(function ($query) {
                $query->where('type', '!=', 'category')
                    ->orWhereHas('category', fn ($category) => $category->where('is_active', true));
            })
            ->with('category')
            ->orderBy('sort_order')
            ->get()
            ->map(function (HomeSection $section) {
                $products = StoreCatalogRules::constrainStoreProducts(
                    Product::with(['category', 'brand'])
                        ->where('is_active', true)
                        ->whereHas('category', fn ($category) => $category->where('is_active', true)),
                );

                match ($section->type) {
                    'featured' => $products->where('is_featured', true)->latest(),
                    'best_sellers' => $products->withCount('orderItems')->orderByDesc('order_items_count')->latest(),
                    'latest' => $products->latest(),
                    'deals' => $products->whereNotNull('compare_at_price')->whereColumn('compare_at_price', '>', 'price')->orderByDesc('is_featured'),
                    'category' => $products->where('category_id', $section->category_id)->latest(),
                    default => $products->latest(),
                };

                $section->setRelation('products', $products->take($section->product_limit)->get());

                return $section;
            })
            ->filter(fn (HomeSection $section) => $section->products->isNotEmpty())
            ->values();

        return view('store.home', [
            'banners' => Banner::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get()
                ->reject(fn (Category $category) => StoreCatalogRules::isRestricted($category->name.' '.$category->slug)),
            'sections' => $sections,
            'brands' => Brand::where('is_active', true)->orderBy('name')->take(12)->get(),
            'testimonials' => Testimonial::where('is_active', true)->latest()->take(3)->get(),
            'promotion' => Coupon::where('is_active', true)
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->where(fn ($query) => $query->whereNull('usage_limit')->orWhereColumn('usage_count', '<', 'usage_limit'))
                ->orderBy('minimum_order')
                ->first(),
        ]);
    }

    public function shop(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'min' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'max' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'sort' => ['nullable', Rule::in(['price-low', 'price-high', 'name'])],
        ]);
        $products = StoreCatalogRules::constrainStoreProducts(Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true)));

        if ($request->filled('q')) {
            $term = trim((string) $request->query('q'));
            $products->where(fn ($query) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$term}%")));
        }

        if ($request->filled('category')) {
            $products->whereHas('category', fn ($query) => $query->where('slug', $request->query('category')));
        }

        if ($request->filled('brand')) {
            $products->whereHas('brand', fn ($query) => $query->where('slug', $request->query('brand'))->where('is_active', true));
        }

        if ($request->filled('min')) {
            $products->where('price', '>=', $request->query('min'));
        }

        if ($request->filled('max')) {
            $products->where('price', '<=', $request->query('max'));
        }

        match ($request->query('sort')) {
            'price-low' => $products->orderBy('price'),
            'price-high' => $products->orderByDesc('price'),
            'name' => $products->orderBy('name'),
            default => $products->latest(),
        };

        return view('store.shop', [
            'products' => $products->paginate(12)->withQueryString(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get()
                ->reject(fn (Category $category) => StoreCatalogRules::isRestricted($category->name.' '.$category->slug)),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function product(Product $product): View
    {
        abort_unless(
            $product->is_active
                && $product->category?->is_active
            && ! StoreCatalogRules::isRestricted($product->name.' '.$product->category->name),
            404,
        );

        return view('store.product', [
            'product' => $product->load(['category', 'brand']),
            'related' => StoreCatalogRules::constrainStoreProducts(Product::with(['brand', 'category'])->where('is_active', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
            )->take(4)->get(),
        ]);
    }
}
