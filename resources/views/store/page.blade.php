@extends('layouts.store')
@section('title', $page->meta_title ?: $page->title.' · '.$site['business_name'])
@section('meta_description', $page->meta_description ?: $site['seo_description'])
@section('content')
<article class="wrap" style="max-width:900px;padding-bottom:60px">
    <div class="breadcrumb"><a href="{{ route('home') }}">Home</a> / {{ $page->title }}</div>
    <span class="eyebrow">{{ $site['business_name'] }}</span><h1 class="page-title">{{ $page->title }}</h1>
    <div class="detail-description" style="white-space:pre-line">{{ $page->body }}</div>
</article>
@endsection
