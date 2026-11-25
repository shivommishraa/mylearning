@extends('layouts.admin')
@section('title', 'Website & delivery')
@section('content')
<div class="admin-title"><div><h1>Your store, your way.</h1><p>Keep customer-facing details, search snippets and delivery settings up to date.</p></div></div>
<form class="admin-panel" method="POST" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
    <h2>Neighbourhood & contact details</h2>
    <div class="form-grid">
        <div class="admin-field"><label>Business name</label><input name="business_name" value="{{ old('business_name',$settings['business_name'] ?? $site['business_name']) }}" required></div>
        <div class="admin-field"><label>Business type</label><input name="business_type" value="{{ old('business_type',$settings['business_type'] ?? $site['business_type']) }}" required></div>
        <div class="admin-field"><label>Phone</label><input name="phone" value="{{ old('phone',$settings['phone'] ?? $site['phone']) }}" required></div>
        <div class="admin-field"><label>Email</label><input type="email" name="email" value="{{ old('email',$settings['email'] ?? $site['email']) }}" required></div>
        <div class="admin-field"><label>WhatsApp number (include country code)</label><input name="whatsapp" value="{{ old('whatsapp',$settings['whatsapp'] ?? $site['whatsapp']) }}"></div>
        <div class="admin-field"><label>Store address</label><textarea name="address" rows="3" required>{{ old('address',$settings['address'] ?? $site['address']) }}</textarea><small>Used for directions and Google Maps.</small></div>
    </div>
    <h2 style="margin-top:24px">Delivery & checkout</h2>
    <div class="form-grid"><div class="admin-field"><label>Standard delivery fee (₹)</label><input type="number" step="0.01" min="0" name="delivery_fee" value="{{ old('delivery_fee',$settings['delivery_fee'] ?? $site['delivery_fee']) }}" required></div><div class="admin-field"><label>Free delivery on baskets over (₹)</label><input type="number" step="0.01" min="0" name="free_delivery_minimum" value="{{ old('free_delivery_minimum',$settings['free_delivery_minimum'] ?? $site['free_delivery_minimum']) }}" required></div></div>
    <h2 style="margin-top:24px">Customer account verification</h2>
    <div class="admin-field">
        <label style="display:flex;align-items:center;gap:8px"><input type="hidden" name="require_email_verification" value="0"><input type="checkbox" name="require_email_verification" value="1" style="width:auto;padding:0;margin:0" @checked((string) old('require_email_verification', $settings['require_email_verification'] ?? '0') === '1')> Require email verification</label>
        <small>When enabled, new customers must verify their email before using account and checkout pages. Admin accounts are not affected.</small>
    </div>
    <h2 style="margin-top:24px">Homepage & Google search</h2>
    <div class="admin-field"><label>Announcement strip</label><input name="announcement_text" value="{{ old('announcement_text',$settings['announcement_text'] ?? $site['announcement_text']) }}" required maxlength="160"></div>
    <div class="admin-field"><label>Homepage headline</label><input name="hero_title" value="{{ old('hero_title',$settings['hero_title'] ?? $site['hero_title']) }}"></div>
    <div class="admin-field"><label>Homepage introduction</label><textarea name="hero_subtitle" rows="2">{{ old('hero_subtitle',$settings['hero_subtitle'] ?? $site['hero_subtitle']) }}</textarea></div>
    <div class="admin-field"><label>SEO page title</label><input name="seo_title" value="{{ old('seo_title',$settings['seo_title'] ?? $site['seo_title']) }}"></div>
    <div class="admin-field"><label>SEO description</label><textarea name="seo_description" rows="2">{{ old('seo_description',$settings['seo_description'] ?? $site['seo_description']) }}</textarea></div>
    <h2 style="margin-top:24px">Footer content</h2>
    <div class="form-grid">
        <div class="admin-field"><label for="footer_tagline">Footer tagline</label><input id="footer_tagline" name="footer_tagline" value="{{ old('footer_tagline',$settings['footer_tagline'] ?? $site['footer_tagline']) }}" maxlength="160"></div>
        <div class="admin-field"><label for="footer_note">Footer note</label><input id="footer_note" name="footer_note" value="{{ old('footer_note',$settings['footer_note'] ?? $site['footer_note']) }}" maxlength="160"></div>
        <div class="admin-field"><label for="footer_note_url">Footer note URL (optional)</label><input type="url" id="footer_note_url" name="footer_note_url" value="{{ old('footer_note_url',$settings['footer_note_url'] ?? $site['footer_note_url']) }}" maxlength="2048" placeholder="https://example.com"></div>
    </div>
    <button class="button">Save store settings</button>
</form>
<section class="admin-panel">
    <h2>Payment setup</h2>
    <p class="muted" style="font-size:12px">Cash on delivery is available. The configured online-payment mode is <strong>{{ config('payments.gateway') }}</strong>; {{ filled(config('payments.razorpay.key_id')) ? 'a Razorpay key ID is configured.' : 'no online gateway credentials are configured.' }}</p>
    <p class="muted" style="font-size:11px;margin:0">Keep provider keys in your server environment (<code>PAYMENT_GATEWAY</code>, <code>RAZORPAY_KEY_ID</code>, <code>RAZORPAY_KEY_SECRET</code>, <code>RAZORPAY_WEBHOOK_SECRET</code>). Secrets are never stored in the website settings or exposed in this panel.</p>
</section>
@endsection
