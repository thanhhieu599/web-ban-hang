@props(['post', 'size' => 'md'])
@php
    $ratio = $size === 'row' ? 'aspect-[16/10]' : 'aspect-[16/9]';
@endphp
<a href="{{ route('news.show', $post->slug) }}" class="group {{ $size === 'row' ? 'flex gap-3' : 'block' }}">
    <div class="{{ $size === 'row' ? 'w-28 shrink-0' : '' }}">
        @if ($post->image_url)
            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" loading="lazy" class="{{ $ratio }} w-full rounded-xl object-cover">
        @else
            <div class="banner-{{ $post->theme }} {{ $ratio }} relative overflow-hidden rounded-xl">
                <div class="confetti absolute inset-0"></div>
                @if ($size !== 'row' && $post->category)
                    <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-ink-800">{{ $post->category->name }}</span>
                @endif
                <div class="absolute bottom-0 right-[6%] w-[42%] transition duration-300 group-hover:-translate-y-1"><x-art :type="$post->art ?: 'tv'" /></div>
            </div>
        @endif
    </div>
    <div class="min-w-0">
        <h3 @class(['font-semibold leading-snug group-hover:text-brand-600', 'line-clamp-2 text-[13px]' => $size === 'row', 'mt-2 line-clamp-2 text-[14px]' => $size === 'md', 'mt-3 text-[18px] font-extrabold sm:text-[22px]' => $size === 'xl'])>{{ $post->title }}</h3>
        @if ($size === 'xl' && $post->excerpt)
            <p class="mt-1 line-clamp-2 text-[14px] text-ink-600">{{ $post->excerpt }}</p>
        @endif
        <span class="mt-1 flex items-center gap-1 text-[12px] text-ink-500"><x-ico name="clock" size="13" /><span class="num">{{ $post->published_at?->format('d/m/Y') }}</span></span>
    </div>
</a>
