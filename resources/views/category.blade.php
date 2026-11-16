@extends('layouts.app')

@section('title', ($category?->meta_title ?: $title).($products->currentPage() > 1 ? ' - Trang '.$products->currentPage() : ''))
@section('description', $category?->meta_description ?: ($category?->description ?? ''))

@php
    // Tạo lại URL hiện tại với bộ lọc được bật/tắt
    $toggle = function (string $key, $value) {
        $query = request()->except('page');
        $current = (array) ($query[$key] ?? []);
        $query[$key] = in_array($value, $current) ? array_values(array_diff($current, [$value])) : [...$current, $value];
        return url()->current().'?'.http_build_query(array_filter($query, fn ($v) => $v !== [] && $v !== null && $v !== ''));
    };
    $set = function (string $key, $value) {
        $query = request()->except('page');
        if (($query[$key] ?? null) == $value) { unset($query[$key]); } else { $query[$key] = $value; }
        return url()->current().'?'.http_build_query(array_filter($query, fn ($v) => $v !== [] && $v !== null && $v !== ''));
    };
    $activeCount = count($selBrands) + count($selValues) + ($selPrice ? 1 : 0);
    $resetUrl = url()->current().(isset($q) && $q !== '' ? '?'.http_build_query(['q' => $q]) : '');
    $chip = 'flex h-10 items-center rounded-lg border px-3 text-[13px] transition';
    $on = 'border-brand-600 bg-brand-50 font-semibold text-brand-700';
    $off = 'border-ink-200 text-ink-700 hover:border-ink-400';
@endphp

@section('content')
<div class="wrap space-y-4 py-4">
    @include('partials.breadcrumb', ['crumbs' => $category
        ? array_filter([$category->parent ? [$category->parent->name, route('category', $category->parent->slug)] : null, [$category->name, null]])
        : [[$title, null]]])

    @if ($category?->banner)
        <img src="{{ asset('storage/'.$category->banner) }}" alt="{{ $category->name }}" class="aspect-[4/1] w-full rounded-2xl object-cover">
    @elseif ($banners->isNotEmpty() && $category)
        <div class="relative overflow-hidden rounded-2xl" x-data="slider({{ $banners->count() }})" @mouseenter="pause = true" @mouseleave="pause = false">
            <div class="flex transition-transform duration-500" :style="`transform: translateX(-${i * 100}%)`">
                @foreach ($banners as $ad)
                    <x-banner :ad="$ad" variant="category" :art="$category->icon" class="w-full shrink-0 !rounded-none" />
                @endforeach
            </div>
            @if ($banners->count() > 1)
                <button class="absolute left-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 shadow sm:grid" @click="prev()" aria-label="Banner trước"><x-ico name="left" size="18" /></button>
                <button class="absolute right-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 shadow sm:grid" @click="next()" aria-label="Banner sau"><x-ico name="right" size="18" /></button>
            @endif
        </div>
    @endif

    @if ($siblings->isNotEmpty())
        <div class="no-sb flex gap-2 overflow-x-auto">
            @foreach ($siblings as $sibling)
                <a href="{{ route('category', $sibling->slug) }}" @class(['flex shrink-0 items-center gap-2 rounded-xl bg-white py-1.5 pl-1.5 pr-3 text-[13px] font-medium ring-1 transition', 'text-brand-700 ring-brand-600' => $sibling->id === $category->id, 'text-ink-700 ring-ink-100 hover:ring-ink-300' => $sibling->id !== $category->id])>
                    <span class="h-8 w-8 rounded-lg bg-ink-50"><x-cat-icon :category="$sibling" /></span>{{ $sibling->name }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Bộ lọc --}}
    <section class="card" x-data="{ open: {{ $selPrice ? 'true' : 'false' }} }">
        <h1 class="mb-3 text-[16px] font-extrabold">{{ $category ? $category->name.' dành cho bạn' : $title }}</h1>
        <div class="flex flex-wrap gap-2">
            <button @click="open = !open" class="{{ $chip }} gap-1.5 border-brand-600 font-semibold {{ $activeCount ? 'bg-brand-50 text-brand-700' : 'text-brand-600' }}">
                <x-ico name="filter" size="16" />Bộ lọc
                @if ($activeCount)<span class="num rounded-full bg-brand-600 px-1.5 text-[11px] text-white">{{ $activeCount }}</span>@endif
                <x-ico name="down" size="14" />
            </button>
            @foreach ($brands as $brand)
                <a href="{{ $toggle('thuong-hieu', $brand->slug) }}" rel="nofollow" class="{{ $chip }} min-w-[92px] justify-center {{ in_array($brand->slug, $selBrands) ? $on : $off }}">
                    @if ($brand->logo)
                        <img src="{{ asset('storage/'.$brand->logo) }}" alt="{{ $brand->name }}" class="max-h-6">
                    @else
                        <span class="text-[15px] font-black tracking-tight" style="color: {{ $brand->color ?: '#16161a' }}">{{ $brand->name }}</span>
                    @endif
                </a>
            @endforeach
            @foreach ($attributes->take(1) as $attribute)
                @foreach ($attribute->values as $value)
                    <a href="{{ $toggle('loc', $value->id) }}" rel="nofollow" class="{{ $chip }} {{ in_array($value->id, $selValues) ? $on : $off }}">{{ $value->value }}</a>
                @endforeach
            @endforeach
        </div>

        <div x-show="open" x-collapse x-cloak>
            <div class="mt-4 grid gap-5 border-t border-ink-100 pt-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="mb-2 text-[13px] font-bold">Khoảng giá</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach (\App\Http\Controllers\CatalogController::PRICE_RANGES as $key => [$label])
                            <a href="{{ $set('gia', $key) }}" rel="nofollow" class="rounded-lg border px-3 py-1.5 text-[13px] {{ $selPrice === $key ? $on : $off }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                @foreach ($attributes->skip(1) as $attribute)
                    <div>
                        <p class="mb-2 text-[13px] font-bold">{{ $attribute->name }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($attribute->values as $value)
                                <a href="{{ $toggle('loc', $value->id) }}" rel="nofollow" class="rounded-lg border px-3 py-1.5 text-[13px] {{ in_array($value->id, $selValues) ? $on : $off }}">{{ $value->value }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @if ($activeCount)
            <a href="{{ $resetUrl }}" class="mt-3 inline-flex items-center gap-1 text-[13px] font-semibold text-brand-600 hover:underline"><x-ico name="x" size="14" />Bỏ tất cả bộ lọc</a>
        @endif
    </section>

    {{-- Danh sách --}}
    <section class="rounded-2xl bg-white p-3 sm:p-4">
        <div class="mb-3 flex flex-wrap items-center gap-x-1 gap-y-2 text-[13px]">
            <span class="mr-2 font-bold">Sắp xếp theo:</span>
            @foreach (\App\Http\Controllers\CatalogController::SORTS as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['sap-xep' => $key, 'page' => null]) }}" rel="nofollow" class="rounded-full px-3 py-1.5 transition {{ $sort === $key ? 'bg-ink-900 font-semibold text-white' : 'text-ink-700 hover:bg-ink-50' }}">{{ $label }}</a>
            @endforeach
            <span class="ml-auto text-ink-500"><b class="num text-ink-800">{{ $products->total() }}</b> sản phẩm</span>
        </div>

        @if ($products->isEmpty())
            <div class="grid place-items-center gap-2 py-14 text-center">
                <x-ico name="search" size="36" class="text-ink-300" />
                <p class="font-semibold">Không có sản phẩm phù hợp</p>
                <p class="text-[13px] text-ink-500">Thử bỏ bớt bộ lọc hoặc tìm bằng từ khóa ngắn hơn.</p>
                @if ($activeCount)<a href="{{ $resetUrl }}" class="btn btn-outline mt-2">Bỏ tất cả bộ lọc</a>@endif
            </div>
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :compare="true" />
                @endforeach
            </div>
            {{ $products->links() }}
        @endif
    </section>

    @if ($category?->description)
        <section class="card text-[14px] leading-relaxed text-ink-600">{{ $category->description }}</section>
    @endif

    @if ($posts->isNotEmpty())
        <section class="card">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-[18px] font-extrabold">Tin tức &amp; kinh nghiệm mua hàng</h2>
                <a href="{{ route('news') }}" class="text-[13px] font-semibold text-brand-600 hover:underline">Xem tất cả</a>
            </div>
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ($posts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
