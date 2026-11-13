<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeSection;
use App\Support\StoreCatalogRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HomeSectionController extends Controller
{
    private const TYPES = ['featured', 'best_sellers', 'latest', 'deals', 'category'];

    public function index(): View
    {
        return view('admin.content.sections', [
            'sections' => HomeSection::with('category')->orderBy('sort_order')->paginate(15),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'types' => self::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        HomeSection::create($this->validated($request));

        return back()->with('success', 'Homepage section created.');
    }

    public function update(Request $request, HomeSection $section): RedirectResponse
    {
        $section->update($this->validated($request));

        return back()->with('success', 'Homepage section updated.');
    }

    public function destroy(HomeSection $section): RedirectResponse
    {
        $section->delete();

        return back()->with('success', 'Homepage section removed.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:300'],
            'type' => ['required', Rule::in(self::TYPES)],
            'category_id' => ['nullable', 'required_if:type,category', 'exists:categories,id'],
            'product_limit' => ['required', 'integer', 'min:2', 'max:12'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['type'] === 'category') {
            $category = Category::whereKey($data['category_id'])->where('is_active', true)->first();

            if (! $category || StoreCatalogRules::isRestricted($category->name.' '.$category->slug)) {
                throw ValidationException::withMessages(['category_id' => StoreCatalogRules::forbiddenMessage()]);
            }
        } else {
            $data['category_id'] = null;
        }

        if (StoreCatalogRules::isRestricted(($data['eyebrow'] ?? '').' '.$data['title'].' '.($data['subtitle'] ?? ''))) {
            throw ValidationException::withMessages(['title' => StoreCatalogRules::forbiddenMessage()]);
        }

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
