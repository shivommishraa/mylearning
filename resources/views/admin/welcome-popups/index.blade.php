@extends('layouts.admin')
@section('title', 'Welcome popup')
@section('content')
<div class="admin-title"><div><h1>Welcome popup</h1><p>Build and manage the message shown to visitors on the homepage.</p></div></div>

@if(!$popup->exists)
    @can('welcome_popup.create')
        <form class="admin-panel" method="POST" enctype="multipart/form-data" action="{{ route('admin.welcome-popup.store') }}">@csrf
            <h2>Create your welcome popup</h2>
            <p class="muted" style="font-size:12px">The popup starts disabled. A user with Manage permission can enable it from Display settings.</p>
            @include('admin.welcome-popups.content-fields')
            <button class="button button-small">Create welcome popup</button>
        </form>
    @else
        <section class="admin-panel"><p class="muted">No welcome popup has been created. Ask a Super Admin to grant you Create permission.</p></section>
    @endcan
@else
    @cannot('welcome_popup.manage')
        <section class="admin-panel"><p class="muted" style="font-size:12px">Popup status: <strong>{{ $popup->is_enabled ? 'Enabled' : 'Disabled' }}</strong>. Manage permission is required to change its display settings.</p></section>
    @endcannot
    @can('welcome_popup.manage')
        <form class="admin-panel" method="POST" action="{{ route('admin.welcome-popup.display') }}">@csrf @method('PUT')
            <h2>Display settings</h2>
            <div class="form-grid">
                <div class="admin-field">
                    <label>Popup visibility</label>
                    <label class="permission-item"><input type="hidden" name="is_enabled" value="0"><input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $popup->is_enabled))> Enable on the homepage</label>
                </div>
                <div class="admin-field">
                    <label for="display_frequency">Display frequency</label>
                    <select id="display_frequency" name="display_frequency" required>
                        <option value="once_per_session" @selected(old('display_frequency', $popup->display_frequency) === 'once_per_session')>Once per browser session (shows again next time)</option>
                        <option value="every_visit" @selected(old('display_frequency', $popup->display_frequency) === 'every_visit')>Every homepage visit</option>
                        <option value="once_per_visitor" @selected(old('display_frequency', $popup->display_frequency) === 'once_per_visitor')>Only once on this browser (until site data is cleared)</option>
                    </select>
                </div>
                <div class="admin-field"><label for="display_delay">Show after (seconds)</label><input id="display_delay" type="number" name="display_delay" min="0" max="30" value="{{ old('display_delay', $popup->display_delay) }}" required><small>Choose between 0 and 30 seconds.</small></div>
            </div>
            <button class="button button-small">Save display settings</button>
        </form>
    @endcan

    @can('welcome_popup.edit')
        <form class="admin-panel" method="POST" enctype="multipart/form-data" action="{{ route('admin.welcome-popup.update') }}">@csrf @method('PUT')
            <h2>Popup content and design</h2>
            @include('admin.welcome-popups.content-fields')
            <button class="button button-small">Save popup content</button>
        </form>
    @endcan

    @can('welcome_popup.delete')
        <form class="admin-panel" method="POST" action="{{ route('admin.welcome-popup.destroy') }}" onsubmit="return confirm('Delete the welcome popup and its uploaded images?')">@csrf @method('DELETE')
            <h2>Remove popup</h2>
            <p class="muted" style="font-size:12px">This permanently removes the popup, its buttons and uploaded images.</p>
            <button class="button button-danger button-small">Delete welcome popup</button>
        </form>
    @endcan
@endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-welcome-button-list]').forEach((list) => {
    const form = list.closest('form');
    const template = form.querySelector('[data-welcome-button-template]');
    const addButton = form.querySelector('[data-welcome-button-add]');
    let nextIndex = Number(list.dataset.nextIndex);

    list.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-welcome-button-remove]');
        if (removeButton) {
            removeButton.closest('[data-welcome-button-row]').remove();
        }
    });

    addButton.addEventListener('click', () => {
        if (list.querySelectorAll('[data-welcome-button-row]').length >= 5) {
            return;
        }

        list.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__INDEX__/g, String(nextIndex)));
        nextIndex += 1;
    });
});
</script>
@endpush
