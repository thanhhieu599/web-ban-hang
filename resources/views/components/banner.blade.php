@props(['ad', 'variant' => 'tile', 'art' => null])
{{-- Banner quảng cáo quản lý trong admin. Có ảnh tải lên thì hiện ảnh, không thì dựng bằng chữ và hình minh họa. --}}
@php
    $href = $ad->link ? route('ad.click', $ad) : null;
    $tag = $href ? 'a' : 'div';
    $art = $ad->art ?: ($art ?: 'washer');
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'relative block overflow-hidden rounded-2xl '.($ad->image ? '' : 'banner-'.$ad->theme.' text-white')]) }}>
    @if ($ad->image)
        <img src="{{ asset('storage/'.$ad->image) }}" alt="{{ $ad->title ?: $ad->name }}" class="h-full w-full object-cover">
    @elseif ($variant === 'hero')
        <div class="confetti absolute inset-0"></div>
        <div class="relative grid min-h-[230px] grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] items-center gap-2 p-5 sm:min-h-[300px] sm:p-8">
            <div class="min-w-0">
                @if ($ad->eyebrow)
                    <p class="mb-2 inline-flex rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[.14em] sm:text-[12px]">{{ $ad->eyebrow }}</p>
                @endif
                <p class="stroke-txt text-[28px] font-black uppercase leading-[.95] tracking-tight sm:text-[48px]">{{ $ad->title }}</p>
                @if ($ad->subtitle)
                    <div class="ribbon mt-2 inline-block rounded-md px-3 py-1 sm:mt-3"><span class="text-[15px] font-black uppercase sm:text-[24px]">{{ $ad->subtitle }}</span></div>
                @endif
                @if ($ad->badge)
                    <div class="mt-4 hidden flex-wrap gap-2 sm:flex">
                        @foreach (explode('·', $ad->badge) as $perk)
                            <span class="rounded-xl border border-white/25 bg-black/10 px-3 py-2 text-[14px] font-bold text-sun-400">{{ trim($perk) }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="relative flex h-full items-end justify-center">
                <div class="absolute bottom-1 left-1/2 h-6 w-[80%] -translate-x-1/2 rounded-[50%] bg-black/25 blur-md"></div>
                <div class="relative w-[78%] max-w-[280px] drop-shadow-[0_10px_14px_rgba(0,0,0,.3)]"><x-art :type="$art" /></div>
            </div>
        </div>
    @elseif ($variant === 'deal-lg')
        <div class="confetti absolute inset-0"></div>
        <div class="relative flex h-full min-h-[260px] flex-col items-center p-5 text-center">
            @if ($ad->eyebrow)
                <p class="rounded-full bg-black/15 px-3 py-1 text-[12px] font-bold tracking-wider">{{ $ad->eyebrow }}</p>
            @endif
            <p class="stroke-txt mt-2 text-[34px] font-black uppercase leading-[.92] sm:text-[52px]">{{ $ad->title }}<br><span class="text-sun-400">{{ $ad->subtitle }}</span></p>
            @if ($ad->badge)
                <span class="mt-3 rounded-lg bg-white px-3 py-1.5 text-[12px] font-bold text-brand-700">{{ $ad->badge }}</span>
            @endif
            <div class="mt-auto w-[46%] max-w-[240px] pt-3"><x-art :type="$art" /></div>
        </div>
    @elseif ($variant === 'category')
        <div class="confetti absolute inset-0 opacity-60"></div>
        <div class="relative grid min-h-[180px] grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] items-center gap-3 p-5 sm:min-h-[240px] sm:px-16 sm:py-8">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[.18em] text-white/75 sm:text-[12px]">{{ $ad->eyebrow }}</p>
                <p class="mt-1 text-[24px] font-black leading-[1.02] sm:text-[40px]">{{ $ad->title }}</p>
                <p class="mt-2 max-w-md text-[13px] text-white/85 sm:text-[15px]">{{ $ad->subtitle }}</p>
            </div>
            <div class="relative flex items-end justify-center">
                <div class="absolute bottom-0 h-5 w-[80%] rounded-[50%] bg-black/25 blur-md"></div>
                <div class="relative w-[70%] max-w-[260px] drop-shadow-[0_12px_16px_rgba(0,0,0,.3)]"><x-art :type="$art" /></div>
            </div>
        </div>
    @else
        <div class="relative h-full min-h-[150px] p-4 sm:min-h-[170px] sm:p-5">
            @if ($ad->eyebrow)
                <p class="text-[11px] font-semibold uppercase tracking-wider text-sun-400">{{ $ad->eyebrow }}</p>
            @endif
            <p class="mt-1 max-w-[62%] text-[17px] font-black uppercase leading-tight sm:text-[21px]">{{ $ad->title }}</p>
            @if ($ad->subtitle)
                <p class="mt-1 max-w-[60%] text-[12px] text-white/75">{{ $ad->subtitle }}</p>
            @endif
            @if ($ad->badge)
                <p class="mt-2 inline-block rounded-md bg-white px-2 py-1 text-[12px] font-extrabold text-ink-900">{{ $ad->badge }}</p>
            @endif
            <div class="absolute -bottom-3 -right-2 w-[46%] max-w-[150px]"><x-art :type="$art" /></div>
        </div>
    @endif
</{{ $tag }}>
