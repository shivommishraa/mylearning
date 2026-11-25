<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NavigationController extends Controller
{
    public function index(): View
    {
        return view('admin.navigation.index', [
            'items' => NavigationItem::with('parent')->orderBy('location')->orderBy('sort_order')->paginate(30),
            'parentItems' => NavigationItem::whereNull('parent_id')->orderBy('location')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        NavigationItem::create($this->validated($request));

        return back()->with('success', 'Navigation menu item created.');
    }

    public function update(Request $request, NavigationItem $navigationItem): RedirectResponse
    {
        $navigationItem->update($this->validated($request, $navigationItem));

        return back()->with('success', 'Navigation menu item updated.');
    }

    public function destroy(NavigationItem $navigationItem): RedirectResponse
    {
        $navigationItem->delete();

        return back()->with('success', 'Navigation menu item removed.');
    }

    private function validated(Request $request, ?NavigationItem $current = null): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'url' => ['required', 'string', 'max:2048', function ($attribute, $value, $fail) {
                if (! Str::startsWith($value, ['/', 'https://']) || Str::startsWith($value, '//')) {
                    $fail('Use a relative site path or a secure HTTPS link.');
                }
            }],
            'location' => ['required', 'in:header,footer'],
            'parent_id' => ['nullable', 'integer', 'exists:navigation_items,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $parentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $current?->parent_id;

        if ($parentId !== null) {
            $parent = NavigationItem::findOrFail($parentId);

            if ($parent->parent_id !== null || $parent->location !== $data['location'] || ($current && $parent->is($current))) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Choose a top-level menu in the same header or footer.',
                ]);
            }
        }

        if ($current && $current->children()->exists() && ($parentId !== null || $current->location !== $data['location'])) {
            throw ValidationException::withMessages([
                'parent_id' => 'Move or remove this menu’s submenus before changing its parent or location.',
            ]);
        }

        $data['parent_id'] = $parentId;
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
