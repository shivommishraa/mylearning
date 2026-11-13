<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\StoreCatalogRules;
use App\Support\StoreImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly StoreImageManager $images) {}

    public function index(): View
    {
        return view('admin.categories.index', ['categories' => Category::withCount('products')->orderBy('sort_order')->paginate(15)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image'] = $this->images->save(
            $data['image_file'] ?? null,
            $data['image_url'] ?? null,
            null,
            'categories',
            $request->boolean('remove_image'),
        );
        unset($data['image_file'], $data['image_url']);
        Category::create($data);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $category->name === $data['name'] ? $category->slug : Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        $previousImage = $category->image;
        $data['image'] = $this->images->save(
            $data['image_file'] ?? null,
            $data['image_url'] ?? null,
            $previousImage,
            'categories',
            $request->boolean('remove_image'),
        );
        unset($data['image_file'], $data['image_url']);
        $category->update($data);
        $this->images->deleteReplaced($previousImage, $category->image);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'Move or archive this category’s products before deleting it.']);
        }

        $this->images->deleteReplaced($category->image, null);
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        if (StoreCatalogRules::isRestricted($data['name'])) {
            throw ValidationException::withMessages(['name' => StoreCatalogRules::forbiddenMessage()]);
        }
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
