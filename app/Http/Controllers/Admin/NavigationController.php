<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NavigationController extends Controller
{
    public function index(): View
    {
        return view('admin.navigation.index', [
            'items' => NavigationItem::orderBy('location')->orderBy('sort_order')->paginate(30),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        NavigationItem::create($this->validated($request));

        return back()->with('success', 'Navigation link created.');
    }

    public function update(Request $request, NavigationItem $navigationItem): RedirectResponse
    {
        $navigationItem->update($this->validated($request));

        return back()->with('success', 'Navigation link updated.');
    }

    public function destroy(NavigationItem $navigationItem): RedirectResponse
    {
        $navigationItem->delete();

        return back()->with('success', 'Navigation link removed.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'url' => ['required', 'string', 'max:2048', function ($attribute, $value, $fail) {
                if (! Str::startsWith($value, ['/', 'https://']) || Str::startsWith($value, '//')) {
                    $fail('Use a relative site path or a secure HTTPS link.');
                }
            }],
            'location' => ['required', 'in:header,footer'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
