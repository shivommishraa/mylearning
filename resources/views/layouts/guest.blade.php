@extends('layouts.store')
@section('title', 'Account access · '.$site['business_name'])
@section('content')
<section class="auth-shell auth-shell-store">
    <div class="auth-card auth-card-premium">
        {{ $slot }}
    </div>
</section>
@endsection
