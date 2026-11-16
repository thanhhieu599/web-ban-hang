@extends('layouts.app')
@section('title', $page->meta_title ?: $page->title)
@section('description', $page->meta_description ?: '')

@section('content')
<div class="wrap max-w-3xl space-y-4 py-4">
    @include('partials.breadcrumb', ['crumbs' => [[$page->title, null]]])
    <article class="card">
        <h1 class="mb-4 text-[24px] font-extrabold">{{ $page->title }}</h1>
        <div class="prose-content">{!! $page->content !!}</div>
    </article>
</div>
@endsection
