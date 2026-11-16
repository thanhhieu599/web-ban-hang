@extends('layouts.app')
@section('title', $post->meta_title ?: $post->title)
@section('description', $post->meta_description ?: $post->excerpt)
@section('og_type', 'article')
@if ($post->image_url) @section('og_image', $post->image_url) @endif

@section('content')
<div class="wrap space-y-4 py-4">
    @include('partials.breadcrumb', ['crumbs' => array_filter([['Tin tức', route('news')], $post->category ? [$post->category->name, route('news.topic', $post->category->slug)] : null, [$post->title, null]])])
    <div class="grid gap-4 lg:grid-cols-[minmax(0,1.9fr)_minmax(0,1fr)]">
        <article class="card">
            <h1 class="text-[24px] font-extrabold leading-tight sm:text-[30px]">{{ $post->title }}</h1>
            <p class="mt-2 flex flex-wrap items-center gap-x-3 text-[13px] text-ink-500">
                <span class="num flex items-center gap-1"><x-ico name="clock" size="14" />{{ $post->published_at?->format('d/m/Y') }}</span>
                @if ($post->author)<span>{{ $post->author->name }}</span>@endif
                <span class="num">{{ number_format($post->views, 0, ',', '.') }} lượt xem</span>
            </p>
            @if ($post->image_url)
                <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="mt-4 w-full rounded-xl">
            @else
                <div class="banner-{{ $post->theme }} relative mt-4 aspect-[16/7] overflow-hidden rounded-xl"><div class="confetti absolute inset-0"></div><div class="absolute bottom-0 right-[8%] w-[26%]"><x-art :type="$post->art ?: 'tv'" /></div></div>
            @endif
            @if ($post->excerpt)<p class="mt-4 text-[16px] font-medium leading-relaxed text-ink-700">{{ $post->excerpt }}</p>@endif
            <div class="prose-content mt-4">{!! $post->content !!}</div>
            @if ($post->tags->isNotEmpty())
                <div class="mt-5 flex flex-wrap gap-2 border-t border-ink-100 pt-4">
                    @foreach ($post->tags as $tag)<span class="rounded-full bg-ink-50 px-3 py-1 text-[12px] text-ink-700">#{{ $tag->name }}</span>@endforeach
                </div>
            @endif
        </article>
        <aside class="card space-y-3 self-start">
            <h2 class="text-[16px] font-extrabold">Bài liên quan</h2>
            @forelse ($related as $item)<x-post-card :post="$item" size="row" />@empty<p class="text-[13px] text-ink-500">Chưa có bài liên quan.</p>@endforelse
        </aside>
    </div>
</div>
@endsection
