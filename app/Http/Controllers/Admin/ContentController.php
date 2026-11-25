<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Testimonial;
use App\Support\StoreCatalogRules;
use App\Support\StoreImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function __construct(private readonly StoreImageManager $images) {}

    public function banners(): View
    {
        return view('admin.content.banners', ['banners' => Banner::orderBy('sort_order')->paginate(15)]);
    }

    public function storeBanner(Request $request): RedirectResponse
    {
        $data = $this->bannerData($request);
        $data['image'] = $this->images->save(
            $data['image_file'] ?? null,
            $data['image_url'] ?? null,
            null,
            'banners',
        );
        unset($data['image_file'], $data['image_url']);
        Banner::create($data);

        return back()->with('success', 'Banner created.');
    }

    public function updateBanner(Request $request, Banner $banner): RedirectResponse
    {
        $data = $this->bannerData($request, $banner);
        $previousImage = $banner->image;
        $data['image'] = $this->images->save(
            $data['image_file'] ?? null,
            $data['image_url'] ?? null,
            $previousImage,
            'banners',
        );
        unset($data['image_file'], $data['image_url']);
        $banner->update($data);
        $this->images->deleteReplaced($previousImage, $banner->image);

        return back()->with('success', 'Banner updated.');
    }

    public function deleteBanner(Banner $banner): RedirectResponse
    {
        $this->images->deleteReplaced($banner->image, null);
        $banner->delete();

        return back()->with('success', 'Banner deleted.');
    }

    public function testimonials(): View
    {
        return view('admin.content.testimonials', ['testimonials' => Testimonial::latest()->paginate(15)]);
    }

    public function storeTestimonial(Request $request): RedirectResponse
    {
        Testimonial::create($this->testimonialData($request));

        return back()->with('success', 'Testimonial created.');
    }

    public function updateTestimonial(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->testimonialData($request));

        return back()->with('success', 'Testimonial updated.');
    }

    public function deleteTestimonial(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return back()->with('success', 'Testimonial deleted.');
    }

    private function bannerData(Request $request, ?Banner $banner = null): array
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'image_url' => [$banner ? 'nullable' : 'required_without:image_file', 'url', 'max:2048'],
            'image_file' => [$banner ? 'nullable' : 'required_without:image_url', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_url' => ['nullable', 'string', 'max:2048', function ($attribute, $value, $fail) {
                if ($value !== null && ! Str::startsWith($value, ['/', 'https://', 'http://'])) {
                    $fail('The button destination must be a relative path or an HTTP(S) URL.');
                }

                if (is_string($value) && Str::startsWith($value, '//')) {
                    $fail('The button destination must not be a protocol-relative URL.');
                }
            }],
            'link_target' => ['nullable', 'in:_self,_blank'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        if (StoreCatalogRules::isRestricted(($data['eyebrow'] ?? '').' '.($data['title'] ?? '').' '.($data['subtitle'] ?? ''))) {
            throw ValidationException::withMessages(['title' => StoreCatalogRules::forbiddenMessage()]);
        }
        $data['title'] = $data['title'] ?? '';
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['link_target'] = $data['link_target'] ?? '_self';

        return $data;
    }

    private function testimonialData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'quote' => ['required', 'string', 'max:1500'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
