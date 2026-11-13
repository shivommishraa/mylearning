@extends('layouts.store')
@section('title', 'Say hello · '.$site['business_name'])
@section('content')
<div class="wrap">
    <div class="breadcrumb"><a href="{{ route('home') }}">Home</a> / Come say hello</div>
    <h1 class="page-title">We're right around the corner.</h1><p class="page-intro">Questions, thoughts or a little hello — we’re always happy to hear from you.</p>
    <div class="contact-grid">
        <div class="contact-info">
            <span class="eyebrow">YOUR NEIGHBOURHOOD STORE</span>
            <div class="contact-item"><strong>Come by</strong><span>{{ $site['address'] }}</span></div>
            <div class="contact-item"><strong>Give us a ring</strong><a href="tel:{{ preg_replace('/\s+/', '', $site['phone']) }}">{{ $site['phone'] }}</a></div>
            <div class="contact-item"><strong>Write us a note</strong><a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></div>
            <div class="contact-item"><strong>Send us a WhatsApp</strong><a href="https://wa.me/{{ preg_replace('/\D+/', '', $site['whatsapp']) }}" target="_blank" rel="noopener">We’re all ears on WhatsApp ↗</a></div>
            <a class="button button-light button-small" style="margin-top:17px" href="https://maps.google.com/?q={{ urlencode($site['address']) }}" target="_blank" rel="noopener">Get directions →</a>
        </div>
        <div class="basket-card"><span class="eyebrow">A NOTE TO THE TEAM</span><h2 style="font-size:23px">What's on your mind?</h2>
            <form method="POST" action="{{ route('contact.store') }}">@csrf
                <div class="form-grid"><div class="admin-field"><label for="name">Your name</label><input class="field" id="name" name="name" value="{{ old('name') }}" required></div><div class="admin-field"><label for="email">Email address</label><input class="field" id="email" type="email" name="email" value="{{ old('email') }}" required></div></div>
                <div class="form-grid"><div class="admin-field"><label for="phone">Phone, if you'd like</label><input class="field" id="phone" name="phone" value="{{ old('phone') }}"></div><div class="admin-field"><label for="subject">Subject</label><input class="field" id="subject" name="subject" value="{{ old('subject') }}"></div></div>
                <div class="admin-field"><label for="message">Your message</label><textarea class="field" id="message" name="message" rows="5" required>{{ old('message') }}</textarea></div>
                <button class="button">Send your note →</button>
            </form>
        </div>
    </div>
    <iframe class="map-frame" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q={{ urlencode($site['address']) }}&output=embed" title="SSG MART location on Google Maps"></iframe>
</div>
@endsection
