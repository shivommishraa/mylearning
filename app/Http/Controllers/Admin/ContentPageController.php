<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentPageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', ['pages' => ContentPage::latest()->paginate(15)]);
    }

    public function store(Request $request): RedirectResponse
    {
        ContentPage::create($this->validated($request));

        return back()->with('success', 'Content page created.');
    }

    public function update(Request $request, ContentPage $page): RedirectResponse
    {
        $page->update($this->validated($request, $page));

        return back()->with('success', 'Content page updated.');
    }

    public function destroy(ContentPage $page): RedirectResponse
    {
        $page->delete();

        return back()->with('success', 'Content page deleted.');
    }

    private function validated(Request $request, ?ContentPage $page = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:180', 'alpha_dash', Rule::unique('content_pages', 'slug')->ignore($page?->id)],
            'body' => ['nullable', 'string', 'max:50000'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'is_published' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = Str::slug($data['slug']);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
