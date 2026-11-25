<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\WelcomePopup;
use App\Support\StoreImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class WelcomePopupController extends Controller
{
    public function __construct(private readonly StoreImageManager $images) {}

    public function index(): View
    {
        $popup = WelcomePopup::with(['images', 'buttons'])->find(1)
            ?? new WelcomePopup([
                'display_mode' => 'image_content_buttons',
                'display_frequency' => 'once_per_session',
                'image_position' => 'left',
                'content_alignment' => 'left',
                'button_style' => 'solid',
                'accent_color' => '#246b45',
                'show_close_button' => true,
            ]);

        return view('admin.welcome-popups.index', compact('popup'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(WelcomePopup::query()->whereKey(1)->exists(), 409);

        $data = $this->contentData($request);
        $uploads = $data['image_files'];
        $buttons = $data['buttons'];
        unset($data['image_files'], $data['buttons'], $data['remove_images']);

        $popup = new WelcomePopup($data);
        $popup->id = 1;
        $storedPaths = [];

        try {
            DB::transaction(function () use ($popup, $uploads, $buttons, &$storedPaths) {
                $popup->save();
                $this->addImages($popup, $uploads, $storedPaths);
                $this->syncButtons($popup, collect($buttons));
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                $this->images->deleteReplaced($path, null);
            }

            throw $exception;
        }

        $this->log('welcome-popup.created', $popup);

        return redirect()->route('admin.welcome-popup.index')->with('success', 'Welcome popup created. Enable it in display settings when it is ready.');
    }

    public function update(Request $request): RedirectResponse
    {
        $popup = WelcomePopup::with(['images', 'buttons'])->findOrFail(1);
        $data = $this->contentData($request, $popup);
        $uploads = $data['image_files'];
        $buttons = collect($data['buttons']);
        $removeImageIds = $data['remove_images'];
        unset($data['image_files'], $data['buttons'], $data['remove_images']);

        $removedImages = $popup->images->whereIn('id', $removeImageIds);
        $storedPaths = [];

        try {
            DB::transaction(function () use ($popup, $data, $uploads, $buttons, $removeImageIds, &$storedPaths) {
                $popup->fill($data);
                $popup->save();

                if ($removeImageIds !== []) {
                    $popup->images()->whereIn('id', $removeImageIds)->delete();
                }

                $this->addImages($popup, $uploads, $storedPaths, $popup->images()->max('sort_order') + 1);
                $this->syncButtons($popup, $buttons);
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                $this->images->deleteReplaced($path, null);
            }

            throw $exception;
        }

        foreach ($removedImages as $image) {
            $this->images->deleteReplaced($image->path, null);
        }

        $this->log('welcome-popup.updated', $popup);

        return redirect()->route('admin.welcome-popup.index')->with('success', 'Welcome popup updated.');
    }

    public function updateDisplaySettings(Request $request): RedirectResponse
    {
        $popup = WelcomePopup::with(['images', 'buttons'])->findOrFail(1);
        $data = $request->validate([
            'is_enabled' => ['required', 'boolean'],
            'display_frequency' => ['required', Rule::in(['once_per_session', 'every_visit', 'once_per_visitor'])],
            'display_delay' => ['required', 'integer', 'min:0', 'max:30'],
        ]);
        $data['is_enabled'] = $request->boolean('is_enabled');

        if ($data['is_enabled']) {
            $this->ensureReadyForDisplay($popup);
        }

        $popup->update($data);
        $this->log('welcome-popup.display-settings-updated', $popup);

        return redirect()->route('admin.welcome-popup.index')->with('success', 'Welcome popup display settings saved.');
    }

    public function destroy(): RedirectResponse
    {
        $popup = WelcomePopup::with('images')->findOrFail(1);

        DB::transaction(fn () => $popup->delete());

        foreach ($popup->images as $image) {
            $this->images->deleteReplaced($image->path, null);
        }

        $this->log('welcome-popup.deleted', $popup);

        return redirect()->route('admin.welcome-popup.index')->with('success', 'Welcome popup deleted.');
    }

    private function contentData(Request $request, ?WelcomePopup $popup = null): array
    {
        $mode = $request->input('display_mode');
        $data = $request->validate([
            'display_mode' => ['required', Rule::in(WelcomePopup::DISPLAY_MODES)],
            'title' => [Rule::requiredIf($mode !== 'image_only'), 'nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1500'],
            'image_position' => ['required', Rule::in(['left', 'right', 'top'])],
            'content_alignment' => ['required', Rule::in(['left', 'center'])],
            'button_style' => ['required', Rule::in(['solid', 'outline'])],
            'accent_color' => ['required', 'regex:/\\A#[0-9A-Fa-f]{6}\\z/'],
            'show_close_button' => ['nullable', 'boolean'],
            'image_files' => ['nullable', 'array', 'max:8'],
            'image_files.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
            'buttons' => ['nullable', 'array', 'max:5'],
            'buttons.*.id' => ['nullable', 'integer'],
            'buttons.*.text' => ['required', 'string', 'max:60'],
            'buttons.*.url' => ['required', 'string', 'max:2048', function ($attribute, $value, $fail) {
                $relativePath = Str::startsWith($value, '/') && ! Str::startsWith($value, '//') && ! Str::contains($value, '\\');
                $absoluteUrl = Str::startsWith($value, ['http://', 'https://']) && filter_var($value, FILTER_VALIDATE_URL) !== false;

                if (! $relativePath && ! $absoluteUrl) {
                    $fail('Button links must be a relative path or an HTTP(S) URL.');
                }
            }],
            'buttons.*.target' => ['required', Rule::in(['_self', '_blank'])],
        ]);

        $removeImageIds = $data['remove_images'] ?? [];
        $existingImageIds = $popup?->images->pluck('id')->all() ?? [];

        if (array_diff($removeImageIds, $existingImageIds) !== []) {
            throw ValidationException::withMessages(['remove_images' => 'One or more selected images are no longer available.']);
        }

        $buttonRows = collect($data['buttons'] ?? []);
        $submittedButtonIds = $buttonRows->pluck('id')->filter()->all();
        $existingButtonIds = $popup?->buttons->pluck('id')->all() ?? [];

        if (array_diff($submittedButtonIds, $existingButtonIds) !== []) {
            throw ValidationException::withMessages(['buttons' => 'One or more selected buttons are no longer available.']);
        }

        $remainingImages = count(array_diff($existingImageIds, $removeImageIds)) + count($data['image_files'] ?? []);
        if ($remainingImages > 8) {
            throw ValidationException::withMessages(['image_files' => 'A welcome popup can contain up to 8 images.']);
        }

        if (in_array($mode, ['image_only', 'image_content', 'image_content_buttons'], true) && $remainingImages === 0) {
            throw ValidationException::withMessages(['image_files' => 'Upload at least one image for the selected display mode.']);
        }

        if (in_array($mode, ['content_buttons', 'image_content_buttons'], true) && $buttonRows->isEmpty()) {
            throw ValidationException::withMessages(['buttons' => 'Add at least one button for the selected display mode.']);
        }

        $data['image_files'] = $data['image_files'] ?? [];
        $data['remove_images'] = $removeImageIds;
        $data['buttons'] = $buttonRows->all();
        $data['show_close_button'] = $request->boolean('show_close_button');

        return $data;
    }

    private function addImages(WelcomePopup $popup, array $uploads, array &$storedPaths, int $startOrder = 0): void
    {
        foreach ($uploads as $index => $upload) {
            $path = $this->images->save($upload, null, null, 'welcome-popups');

            if ($path === null) {
                throw ValidationException::withMessages(['image_files' => 'An uploaded image could not be saved.']);
            }

            $storedPaths[] = $path;
            $popup->images()->create(['path' => $path, 'sort_order' => $startOrder + $index]);
        }
    }

    private function syncButtons(WelcomePopup $popup, Collection $rows): void
    {
        $existingIds = $rows->pluck('id')->filter()->all();
        $query = $popup->buttons();

        if ($existingIds === []) {
            $query->delete();
        } else {
            $query->whereNotIn('id', $existingIds)->delete();
        }

        foreach ($rows as $index => $row) {
            $values = [
                'text' => $row['text'],
                'url' => $row['url'],
                'target' => $row['target'],
                'sort_order' => $index,
            ];

            if (! empty($row['id'])) {
                $popup->buttons()->whereKey($row['id'])->update($values);
            } else {
                $popup->buttons()->create($values);
            }
        }
    }

    private function ensureReadyForDisplay(WelcomePopup $popup): void
    {
        if ($popup->displaysImages() && ! $popup->images()->exists()) {
            throw ValidationException::withMessages(['is_enabled' => 'Add at least one image before enabling this display mode.']);
        }

        if ($popup->displaysContent() && blank($popup->title)) {
            throw ValidationException::withMessages(['is_enabled' => 'Add a title before enabling this display mode.']);
        }

        if ($popup->displaysButtons() && ! $popup->buttons()->exists()) {
            throw ValidationException::withMessages(['is_enabled' => 'Add at least one button before enabling this display mode.']);
        }
    }

    private function log(string $action, WelcomePopup $popup): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => WelcomePopup::class,
            'subject_id' => $popup->id,
        ]);
    }
}
