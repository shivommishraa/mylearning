<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SiteSetting;
use App\Support\StoreCatalogRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const FIELDS = [
        'business_name' => ['required', 'string', 'max:120'],
        'business_type' => ['required', 'string', 'max:120'],
        'phone' => ['required', 'string', 'max:30'],
        'email' => ['required', 'email', 'max:180'],
        'address' => ['required', 'string', 'max:500'],
        'whatsapp' => ['nullable', 'string', 'max:30'],
        'delivery_fee' => ['required', 'numeric', 'min:0', 'max:10000'],
        'free_delivery_minimum' => ['required', 'numeric', 'min:0', 'max:100000'],
        'require_email_verification' => ['sometimes', 'boolean'],
        'announcement_text' => ['required', 'string', 'max:160'],
        'seo_title' => ['nullable', 'string', 'max:160'],
        'seo_description' => ['nullable', 'string', 'max:320'],
        'hero_title' => ['nullable', 'string', 'max:180'],
        'hero_subtitle' => ['nullable', 'string', 'max:500'],
        'footer_tagline' => ['nullable', 'string', 'max:160'],
        'footer_note' => ['nullable', 'string', 'max:160'],
        'footer_note_url' => ['nullable', 'url', 'max:2048'],
    ];

    public function edit(): View
    {
        $settings = SiteSetting::query()->pluck('value', 'key');

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate(self::FIELDS);

        foreach (['footer_tagline', 'footer_note', 'footer_note_url'] as $field) {
            if (array_key_exists($field, $validated)) {
                $validated[$field] ??= '';
            }
        }

        foreach (['announcement_text', 'seo_title', 'seo_description', 'hero_title', 'hero_subtitle'] as $field) {
            if (StoreCatalogRules::isRestricted($validated[$field] ?? '')) {
                throw ValidationException::withMessages([$field => StoreCatalogRules::forbiddenMessage()]);
            }
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated as $key => $value) {
                SiteSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => in_array($key, ['delivery_fee', 'free_delivery_minimum']) ? 'delivery' : 'general'],
                );
            }
        });

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'settings.updated',
            'properties' => ['keys' => array_keys($validated)],
        ]);

        return back()->with('success', 'Website settings saved.');
    }
}
