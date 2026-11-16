@extends('layouts.app')

@section('content')
<div class="wrap space-y-5 py-5">
    <h1 class="sr-only">{{ $site['site_name'] ?? config('app.name') }}: điện máy, điện lạnh, gia dụng chính hãng</h1>

    {{-- Banner chính + banner phụ --}}
    @if ($heroAds->isNotEmpty())
        <div class="grid gap-4 {{ $sideAds->isNotEmpty() ? 'lg:grid-cols-[minmax(0,2.1fr)_minmax(0,1fr)]' : '' }}">
            <div class="relative overflow-hidden rounded-2xl" x-data="slider({{ $heroAds->count() }})" @mouseenter="pause = true" @mouseleave="pause = false">
                <div class="flex transition-transform duration-500 ease-out" :style="`transform: translateX(-${i * 100}%)`">
                    @foreach ($heroAds as $ad)
                        <x-banner :ad="$ad" variant="hero" class="w-full shrink-0 !rounded-none" />
                    @endforeach
                </div>
                @if ($heroAds->count() > 1)
                    <button class="absolute left-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-ink-800 shadow md:grid" @click="prev()" aria-label="Banner trước"><x-ico name="left" size="18" /></button>
                    <button class="absolute right-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-ink-800 shadow md:grid" @click="next()" aria-label="Banner sau"><x-ico name="right" size="18" /></button>
                    <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-1.5">
                        @foreach ($heroAds as $n => $ad)
                            <button @click="i = {{ $n }}" class="h-1.5 rounded-full bg-white transition-all" :class="i === {{ $n }} ? 'w-6' : 'w-1.5 opacity-50'" aria-label="Banner {{ $n + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
            @if ($sideAds->isNotEmpty())
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-1">
                    @foreach ($sideAds as $ad)
                        <x-banner :ad="$ad" />
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Danh mục nổi bật --}}
    @if ($tiles->isNotEmpty())
        <section class="card">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-[18px] font-extrabold">Danh mục nổi bật</h2>
                <button class="text-[13px] font-semibold text-brand-600 lg:hidden" @click="drawer = true">Tất cả</button>
            </div>
            <div class="no-sb -mx-1 grid auto-cols-[25%] grid-flow-col grid-rows-2 overflow-x-auto sm:auto-cols-[16.66%] lg:grid-flow-row lg:grid-cols-8 lg:overflow-visible">
                @foreach ($tiles as $tile)
                    <a href="{{ route('category', $tile->slug) }}" class="group flex flex-col items-center gap-2 px-1 py-2 text-center">
                        <span class="relative grid h-[68px] w-[68px] place-items-center rounded-full bg-linear-to-b from-sky-50 to-ink-50 transition group-hover:from-brand-50 group-hover:to-brand-100">
                            <span class="h-12 w-12 transition group-hover:scale-110"><x-cat-icon :category="$tile" /></span>
                            @if ($tile->sale_badge)<span class="tag-sale absolute -bottom-1 rounded px-1.5 text-[10px] font-extrabold">{{ $tile->sale_badge }}</span>@endif
                        </span>
                        <span class="text-[12.5px] font-medium leading-tight text-ink-800 group-hover:text-brand-700">{{ $tile->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Flash sale --}}
    @if ($flash && $flashProducts->isNotEmpty())
        @php $groups = $flashProducts->map(fn ($p) => $p->category->parent->name ?? $p->category->name)->unique()->values(); @endphp
        <section id="flash-sale" class="banner-red relative scroll-mt-40 overflow-hidden rounded-2xl p-4 sm:p-6" x-data="{ tab: 'all' }">
            <div class="confetti absolute inset-0"></div>
            <div class="relative">
                <div class="flex flex-wrap items-center justify-between gap-4 text-white">
                    <div class="flex items-center gap-3">
                        <span class="grid h-12 w-12 place-items-center rounded-xl bg-sun-400 text-brand-700 shadow-[inset_0_-3px_0_rgba(0,0,0,.15)]"><x-ico name="bolt" size="28" /></span>
                        <div>
                            @if ($flash->subtitle)<p class="text-[11px] font-bold uppercase tracking-[.2em] text-sun-300">{{ $flash->subtitle }}</p>@endif
                            <h2 class="stroke-txt text-[24px] font-black uppercase leading-none sm:text-[34px]">{{ $flash->name }}</h2>
                        </div>
                    </div>
                    <div class="flex items-center gap-2" data-countdown="{{ $flash->ends_at->toIso8601String() }}">
                        <span class="text-[13px] font-semibold text-white/85">Kết thúc sau</span>
                        <span hidden class="rounded-lg bg-sun-400 px-2 py-1.5 text-[15px] font-black text-ink-900"><span class="num" data-day>0</span> ngày</span>
                        @foreach ([0, 1, 2] as $n)
                            <span class="num grid h-9 min-w-10 place-items-center rounded-lg bg-white px-1 text-[18px] font-black text-brand-700" data-part>--</span>
                            @if ($n < 2)<span class="blink font-black">:</span>@endif
                        @endforeach
                    </div>
                </div>

                <div class="no-sb mt-4 flex gap-2 overflow-x-auto pb-1">
                    <button @click="tab = 'all'" class="shrink-0 rounded-full px-4 py-1.5 text-[13px] font-semibold transition" :class="tab === 'all' ? 'bg-sun-400 text-ink-900' : 'bg-white/95 text-ink-700 hover:bg-white'">Tất cả</button>
                    @foreach ($groups as $g)
                        <button @click="tab = @js($g)" class="shrink-0 rounded-full px-4 py-1.5 text-[13px] font-semibold transition" :class="tab === @js($g) ? 'bg-sun-400 text-ink-900' : 'bg-white/95 text-ink-700 hover:bg-white'">{{ $g }}</button>
                    @endforeach
                </div>

                <div class="relative mt-3" x-data="row">
                    <div x-ref="row" class="no-sb flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth">
                        @foreach ($flashProducts as $product)
                            <div class="w-[47%] shrink-0 snap-start sm:w-[31.5%] lg:w-[calc((100%-60px)/6)]" x-show="tab === 'all' || tab === @js($product->category->parent->name ?? $product->category->name)">
                                <x-product-card :product="$product" />
                            </div>
                        @endforeach
                    </div>
                    <button class="absolute -left-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white text-ink-800 shadow-lg ring-1 ring-ink-100 md:grid" @click="scroll(-1)" aria-label="Xem trước"><x-ico name="left" size="18" /></button>
            <button class="absolute -right-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white text-ink-800 shadow-lg ring-1 ring-ink-100 md:grid" @click="scroll(1)" aria-label="Xem tiếp"><x-ico name="right" size="18" /></button>
                </div>
            </div>
        </section>
    @endif

    {{-- Ưu đãi theo danh mục --}}
    @if ($offerTabs->isNotEmpty())
        <section x-data="{ tab: 0 }">
            <h2 class="mb-3 text-[20px] font-extrabold">Ưu đãi dành cho bạn</h2>
            <div class="overflow-hidden rounded-2xl bg-white">
                <div class="no-sb flex overflow-x-auto border-b border-ink-100">
                    @foreach ($offerTabs as $n => $cat)
                        <button @click="tab = {{ $n }}" class="relative shrink-0 px-5 py-3 text-left transition" :class="tab === {{ $n }} ? 'bg-brand-50' : 'hover:bg-ink-50'">
                            <span class="block text-[12px] font-semibold uppercase tracking-wide" :class="tab === {{ $n }} ? 'text-brand-700' : 'text-ink-500'">{{ $cat->name }}</span>
                            <span class="block text-[18px] font-black italic leading-tight" :class="tab === {{ $n }} ? 'text-brand-600' : 'text-ink-800'">Giảm tới {{ ltrim($cat->sale_badge, '-') }}</span>
                            <span x-show="tab === {{ $n }}" class="absolute inset-x-0 bottom-0 h-[3px] bg-brand-600"></span>
                        </button>
                    @endforeach
                </div>
                @foreach ($offerTabs as $n => $cat)
                    <div x-show="tab === {{ $n }}" @if ($n) x-cloak @endif>
                        <div class="grid grid-cols-2 gap-3 p-3 sm:grid-cols-3 sm:p-4 lg:grid-cols-6">
                            @foreach ($cat->products as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                        </div>
                        <div class="pb-4 text-center">
                            <a href="{{ route('category', $cat->slug) }}" class="btn btn-outline !rounded-full !py-2 !text-[13px]">Xem thêm {{ mb_strtolower($cat->name) }} <x-ico name="right" size="14" /></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Ô deal nổi bật --}}
    @if ($dealAds->isNotEmpty())
        <section>
            <h2 class="mb-3 text-[20px] font-extrabold">Deal nổ lớn, lên đời lớn</h2>
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:grid-rows-2">
                @foreach ($dealAds as $n => $ad)
                    @if ($n === 0)
                        <x-banner :ad="$ad" variant="deal-lg" class="order-first col-span-2 lg:order-none lg:col-start-2 lg:row-span-2 lg:row-start-1" />
                    @else
                        <x-banner :ad="$ad" />
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    {{-- Bộ sưu tập --}}
    @foreach ($collections as $collection)
        @continue($collection->products->isEmpty())
        <section class="card">
            <div class="mb-3">
                @if ($collection->subtitle)<p class="text-[12px] font-bold uppercase tracking-wider text-orange-600">{{ $collection->subtitle }}</p>@endif
                <h2 class="text-[18px] font-extrabold uppercase sm:text-[20px]">{{ $collection->name }}</h2>
            </div>
            <div class="relative" x-data="row">
                <div x-ref="row" class="no-sb flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth">
                    @foreach ($collection->products as $product)
                        <div class="w-[47%] shrink-0 snap-start sm:w-[31.5%] lg:w-[calc((100%-60px)/6)]"><x-product-card :product="$product" /></div>
                    @endforeach
                </div>
                <button class="absolute -left-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white text-ink-800 shadow-lg ring-1 ring-ink-100 md:grid" @click="scroll(-1)" aria-label="Xem trước"><x-ico name="left" size="18" /></button>
            <button class="absolute -right-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white text-ink-800 shadow-lg ring-1 ring-ink-100 md:grid" @click="scroll(1)" aria-label="Xem tiếp"><x-ico name="right" size="18" /></button>
            </div>
        </section>
    @endforeach

    {{-- Tin tức --}}
    @if ($posts->isNotEmpty())
        <section class="card">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-[18px] font-extrabold sm:text-[20px]">Tin tức &amp; sự kiện</h2>
                <a href="{{ route('news') }}" class="text-[13px] font-semibold text-brand-600 hover:underline">Xem tất cả</a>
            </div>
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ($posts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Phản hồi khách hàng --}}
    @if ($testimonials->isNotEmpty())
        <section>
            <h2 class="mb-3 text-[18px] font-extrabold sm:text-[20px]">Khách hàng nói gì</h2>
            <div class="grid gap-3 md:grid-cols-3">
                @foreach ($testimonials as $t)
                    <figure class="card">
                        <div class="flex gap-0.5 text-sun-500">@for ($s = 0; $s < $t->rating; $s++)<x-ico name="star" size="15" />@endfor</div>
                        <blockquote class="mt-2 text-[14px] leading-relaxed text-ink-700">{{ $t->content }}</blockquote>
                        <figcaption class="mt-3 flex items-center gap-2.5">
                            @if ($t->avatar)
                                <img src="{{ asset('storage/'.$t->avatar) }}" alt="" class="h-9 w-9 rounded-full object-cover">
                            @else
                                <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-50 text-[13px] font-bold text-brand-700">{{ mb_substr($t->name, 0, 1) }}</span>
                            @endif
                            <span><span class="block text-[13px] font-bold">{{ $t->name }}</span><span class="block text-[12px] text-ink-500">{{ $t->role }}</span></span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Từ khóa --}}
    <section>
        <h2 class="mb-3 text-[18px] font-extrabold">Tìm kiếm nhiều nhất</h2>
        <div class="flex flex-wrap gap-2">
            @foreach (array_filter(explode('|', setting('hot_keywords'))) as $keyword)
                <a href="{{ route('search', ['q' => trim($keyword)]) }}" class="rounded-full bg-white px-3.5 py-1.5 text-[13px] text-ink-700 ring-1 ring-ink-100 hover:text-brand-600 hover:ring-brand-300">{{ trim($keyword) }}</a>
            @endforeach
        </div>
    </section>
</div>
@endsection
