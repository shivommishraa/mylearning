<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProductDisplaySettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.products.settings', [
            'settings' => SiteSetting::query()
                ->whereIn('key', ['enable_add_to_cart', 'show_product_prices'])
                ->pluck('value', 'key'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = $request->validate([
            'enable_add_to_cart' => ['required', 'boolean'],
            'show_product_prices' => ['required', 'boolean'],
        ]);

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) (int) $value, 'group' => 'products'],
            );
        }

        Cache::forget('ssg.site-settings');

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'products.storefront-settings.updated',
            'properties' => $settings,
        ]);

        return back()->with('success', 'Product storefront settings saved.');
    }
}
