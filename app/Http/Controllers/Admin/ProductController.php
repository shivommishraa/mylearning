<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\StoreCatalogRules;
use App\Support\StoreImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly StoreImageManager $images) {}

    public function index(Request $request): View
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $search = trim($validated['q'] ?? '');

        return view('admin.products.index', [
            'products' => Product::with('category')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product, 'categories' => Category::where('is_active', true)->orderBy('name')->get(), 'brands' => Brand::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image'] = $this->images->save(
            $data['image_file'] ?? null,
            $data['image_url'] ?? null,
            null,
            'products',
            $request->boolean('remove_image'),
        );
        unset($data['image_file'], $data['image_url']);
        $product = Product::create($data);
        $this->log('product.created', $product);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', ['product' => $product, 'categories' => Category::where('is_active', true)->orderBy('name')->get(), 'brands' => Brand::where('is_active', true)->orderBy('name')->get()]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        $previousImage = $product->image;
        $data['image'] = $this->images->save(
            $data['image_file'] ?? null,
            $data['image_url'] ?? null,
            $previousImage,
            'products',
            $request->boolean('remove_image'),
        );
        unset($data['image_file'], $data['image_url']);
        $product->update($data);
        $this->images->deleteReplaced($previousImage, $product->image);
        $this->log('product.updated', $product);

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $previousImage = $product->image;
        $product->update(['is_active' => false, 'is_featured' => false, 'image' => null]);
        $this->images->deleteReplaced($previousImage, null);
        $this->log('product.archived', $product);

        return back()->with('success', 'Product archived. Past order records are preserved.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['nullable', 'string', 'max:80', Rule::unique('products', 'sku')->ignore($product?->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'compare_at_price' => ['nullable', 'numeric', 'gt:price', 'max:999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        if (StoreCatalogRules::isRestricted($data['name'])) {
            throw ValidationException::withMessages(['name' => StoreCatalogRules::forbiddenMessage()]);
        }
        $category = Category::whereKey($data['category_id'])->where('is_active', true)->firstOrFail();
        if (StoreCatalogRules::isRestricted($category->name.' '.$category->slug)) {
            throw ValidationException::withMessages(['category_id' => StoreCatalogRules::forbiddenMessage()]);
        }
        $data['slug'] = $product && $product->name === $data['name']
            ? $product->slug
            : Str::slug($data['name']).'-'.Str::lower(Str::random(5));
        $data['sku'] = $data['sku'] ?: 'SSG-'.Str::upper(Str::random(8));
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function log(string $action, Product $product): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => Product::class,
            'subject_id' => $product->id,
        ]);
    }
}
