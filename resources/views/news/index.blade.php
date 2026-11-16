@extends('layouts.app')
@section('title', $current ? $current->name : 'Tin tức')

@section('content')
<div class="wrap space-y-5 py-4">
    @include('partials.breadcrumb', ['crumbs' => array_filter([['Tin tức', route('news')], $current ? [$current->name, null] : null])])

    <div class="no-sb flex items-center gap-1 overflow-x-auto rounded-2xl bg-white p-2">
        <a href="{{ route('news') }}" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg {{ $current ? 'text-ink-700 hover:bg-ink-50' : 'bg-brand-600 text-white' }}" aria-label="Tất cả tin"><x-ico name="home" size="18" /></a>
        @foreach ($topics as $topic)
            <a href="{{ route('news.topic', $topic->slug) }}" class="shrink-0 rounded-lg px-3 py-2 text-[13px] font-medium {{ $current?->id === $topic->id ? 'bg-brand-50 font-semibold text-brand-700' : 'text-ink-700 hover:bg-ink-50' }}">{{ $topic->name }}</a>
        @endforeach
    </div>

    @if ($current)
        <section class="card">
            <h1 class="mb-3 text-[20px] font-extrabold">{{ $current->name }}</h1>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($posts as $post)<x-post-card :post="$post" />@empty<p class="text-[14px] text-ink-500">Chưa có bài viết.</p>@endforelse
            </div>
            {{ $posts->links() }}
        </section>
    @else
        <h1 class="sr-only">Tin tức và kinh nghiệm mua hàng</h1>
        @if ($latest->isNotEmpty())
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1.7fr)_minmax(0,1fr)]">
                <div class="card !p-3"><x-post-card :post="$latest[0]" size="xl" /></div>
                <div class="card space-y-3 !p-3">
                    @if (isset($latest[1]))<x-post-card :post="$latest[1]" />@endif
                    @foreach ($latest->slice(2) as $post)<x-post-card :post="$post" size="row" />@endforeach
                </div>
            </div>
        @endif
        @foreach ($sections as [$topic, $items])
            <section class="card">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-[18px] font-extrabold sm:text-[20px]">{{ $topic->name }}</h2>
                    <a href="{{ route('news.topic', $topic->slug) }}" class="text-[13px] font-semibold text-brand-600 hover:underline">Xem tất cả</a>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ($items->take(3) as $post)<x-post-card :post="$post" />@endforeach
                </div>
                @if ($items->count() > 3)
                    <div class="mt-4 grid gap-3 border-t border-ink-100 pt-4 sm:grid-cols-3">
                        @foreach ($items->slice(3) as $post)<x-post-card :post="$post" size="row" />@endforeach
                    </div>
                @endif
            </section>
        @endforeach
    @endif
</div>
@endsection
