@extends('layouts.app')

@section('title', $product->meta_title ?: $product->name)
@section('description', $product->meta_description ?: $product->short_description)
@section('og_type', 'product')
@if ($product->image_url) @section('og_image', $product->image_url) @endif

@php
    $flash = $product->flashItem();
    $images = collect($product->images ?? [])->map(fn ($i) => asset('storage/'.$i));
@endphp

@push('head')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $product->name, 'sku' => $product->sku,
    'description' => $product->short_description, 'image' => $images->all(), 'brand' => ['@type' => 'Brand', 'name' => $product->brand->name ?? ''],
    'offers' => ['@type' => 'Offer', 'priceCurrency' => 'VND', 'price' => $product->final_price, 'url' => url()->current(),
        'availability' => 'https://schema.org/'.($product->stock > 0 ? 'InStock' : 'OutOfStock')],
] + ($product->rating_count ? ['aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => $product->rating_avg, 'reviewCount' => $product->rating_count]] : []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<div class="wrap space-y-4 py-4">
    @include('partials.breadcrumb', ['crumbs' => array_filter([
        $product->category?->parent ? [$product->category->parent->name, route('category', $product->category->parent->slug)] : null,
        $product->category ? [$product->category->name, route('category', $product->category->slug)] : null,
        [$product->name, null],
    ])])

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)]">
        {{-- Ảnh --}}
        <section class="card" x-data="{ img: 0 }">
            <div class="relative aspect-square overflow-hidden rounded-xl bg-linear-to-b from-ink-50 to-white">
                @forelse ($images as $n => $src)
                    <img src="{{ $src }}" alt="{{ $product->name }}" x-show="img === {{ $n }}" class="h-full w-full object-contain" @if ($n) x-cloak loading="lazy" @endif>
                @empty
                    <div class="absolute inset-[14%]"><x-art :type="$product->art_key" /></div>
                @endforelse
                <div class="absolute left-3 top-3 flex flex-col items-start gap-1.5">
                    @foreach ($product->labels as $label)
                        <span class="rounded px-2 py-1 text-[11px] font-bold text-white" style="background: {{ $label->color }}">{{ $label->name }}</span>
                    @endforeach
                </div>
            </div>
            @if ($images->count() > 1)
                <div class="no-sb mt-3 flex gap-2 overflow-x-auto">
                    @foreach ($images as $n => $src)
                        <button @click="img = {{ $n }}" class="h-16 w-16 shrink-0 overflow-hidden rounded-lg ring-2" :class="img === {{ $n }} ? 'ring-brand-600' : 'ring-ink-100'" aria-label="Ảnh {{ $n + 1 }}">
                            <img src="{{ $src }}" alt="" class="h-full w-full object-contain" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Thông tin mua hàng --}}
        <section class="card">
            <h1 class="text-[20px] font-extrabold leading-snug sm:text-[24px]">{{ $product->name }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[13px] text-ink-500">
                <a href="#danh-gia" class="flex items-center gap-1 hover:text-brand-600"><x-ico name="star" size="15" class="text-sun-500" />
                    <span class="num">{{ $product->rating_count ? number_format($product->rating_avg, 1, ',', '.').' ('.$product->rating_count.' đánh giá)' : 'Chưa có đánh giá' }}</span></a>
                <span class="num">Đã bán {{ short_count($product->sold_count) }}</span>
                <span>SKU: {{ $product->sku }}</span>
                @if ($product->brand)<span>Thương hiệu: <b class="text-ink-800">{{ $product->brand->name }}</b></span>@endif
            </div>

            @if ($flash)
                <div class="banner-red mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl px-4 py-2.5 text-white">
                    <span class="flex items-center gap-2 text-[14px] font-extrabold uppercase"><x-ico name="bolt" size="18" class="text-sun-400" />Flash sale</span>
                    <span class="text-[13px]">Kết thúc sau <b class="num text-[15px]" data-countdown="{{ \App\Models\FlashSale::current()->ends_at->toIso8601String() }}">--:--:--</b> · còn {{ $flash->quantity - $flash->sold }} suất</span>
                </div>
            @endif

            <div class="mt-4 flex flex-wrap items-end gap-x-3 gap-y-1">
                <p class="num text-[30px] font-black leading-none text-brand-600">{{ vnd($product->final_price) }}</p>
                @if ($product->old_price)
                    <s class="num text-[15px] text-ink-400">{{ vnd($product->old_price) }}</s>
                    <span class="num rounded bg-brand-50 px-1.5 py-0.5 text-[13px] font-bold text-brand-600">-{{ $product->discount_percent }}%</span>
                @endif
            </div>
            @if ($product->short_description)<p class="mt-3 text-[14px] leading-relaxed text-ink-600">{{ $product->short_description }}</p>@endif

            <form action="{{ route('cart.add') }}" method="post" class="mt-4 space-y-4" x-data="{ qty: 1 }">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                @foreach ($product->options as $option)
                    <fieldset>
                        <legend class="label">{{ $option->name }}</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($option->values as $n => $value)
                                <label class="cursor-pointer">
                                    <input type="radio" name="options[{{ $option->id }}]" value="{{ $n }}" class="peer sr-only" @checked($n === 0)>
                                    <span class="block rounded-lg border border-ink-200 px-3 py-2 text-[13px] peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:font-semibold peer-checked:text-brand-700 peer-focus-visible:outline-2">
                                        {{ $value['label'] }}@if ((int) $value['price'] > 0) <span class="num text-ink-500">+{{ vnd($value['price']) }}</span>@endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach

                @if ($product->stock > 0)
                    <div class="flex items-center gap-4">
                        <span class="label !mb-0">Số lượng</span>
                        <div class="flex items-center rounded-xl border border-ink-200">
                            <button type="button" class="grid h-10 w-10 place-items-center text-[18px]" @click="qty = Math.max(1, qty - 1)" aria-label="Giảm">−</button>
                            <input name="quantity" x-model.number="qty" type="number" min="1" max="{{ $product->stock }}" class="num h-10 w-12 border-x border-ink-200 text-center text-[14px] outline-none [appearance:textfield]" aria-label="Số lượng">
                            <button type="button" class="grid h-10 w-10 place-items-center text-[18px]" @click="qty = Math.min({{ $product->stock }}, qty + 1)" aria-label="Tăng">+</button>
                        </div>
                        <span class="text-[13px] {{ $product->is_low_stock ? 'font-semibold text-orange-600' : 'text-ink-500' }}">{{ $product->is_low_stock ? 'Chỉ còn '.$product->stock.' sản phẩm' : 'Còn hàng' }}</span>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-[1.4fr_1fr_auto]">
                        <button name="buy_now" value="1" class="btn btn-primary !py-3 !text-[16px]">Mua ngay</button>
                        <button class="btn btn-outline !py-3"><x-ico name="cart" size="18" />Thêm vào giỏ</button>
                        <button type="button" data-wish="{{ $product->id }}" class="btn btn-ghost !py-3 {{ $wished ? 'text-brand-600' : '' }}" aria-label="Lưu vào yêu thích"><x-ico name="star" size="18" /></button>
                    </div>
                @else
                    <p class="rounded-xl bg-ink-50 p-4 text-[14px] font-semibold text-ink-700">Sản phẩm tạm hết hàng. Gọi {{ setting('hotline_sales') }} để được báo khi có hàng.</p>
                @endif
            </form>

            <ul class="mt-5 grid gap-2 border-t border-ink-100 pt-4 text-[13px] text-ink-700 sm:grid-cols-2">
                <li class="flex items-center gap-2"><x-ico name="shield" size="18" class="text-brand-600" />Bảo hành: {{ $product->warranty ?: 'chính hãng' }}</li>
                <li class="flex items-center gap-2"><x-ico name="truck" size="18" class="text-brand-600" />Miễn phí giao hàng cho đơn từ {{ vnd(setting('free_ship_from')) }}</li>
                <li class="flex items-center gap-2"><x-ico name="refresh" size="18" class="text-brand-600" />Đổi trả trong 30 ngày</li>
                <li class="flex items-center gap-2"><x-ico name="card" size="18" class="text-brand-600" />Hỗ trợ trả góp 0%</li>
            </ul>
        </section>
    </div>

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <section class="card">
            <h2 class="mb-3 text-[18px] font-extrabold">Mô tả sản phẩm</h2>
            <div class="prose-content">{!! $product->description ?: '<p>Đang cập nhật.</p>' !!}</div>
        </section>
        <section class="card self-start">
            <h2 class="mb-3 text-[18px] font-extrabold">Thông số kỹ thuật</h2>
            <dl class="overflow-hidden rounded-xl text-[13px] ring-1 ring-ink-100">
                @php $rows = collect($product->specs ?? [])->union($product->attributeValues->groupBy('attribute.name')->map(fn ($v) => $v->pluck('value')->join(', '))); @endphp
                @forelse ($rows as $key => $value)
                    <div class="grid grid-cols-[42%_1fr] {{ $loop->odd ? 'bg-ink-50' : 'bg-white' }}">
                        <dt class="px-3 py-2.5 text-ink-600">{{ $key }}</dt><dd class="px-3 py-2.5 font-medium">{{ $value }}</dd>
                    </div>
                @empty
                    <p class="p-3 text-ink-500">Đang cập nhật.</p>
                @endforelse
            </dl>
        </section>
    </div>

    {{-- Đánh giá --}}
    <section id="danh-gia" class="card scroll-mt-40">
        <h2 class="mb-3 text-[18px] font-extrabold">Đánh giá từ khách hàng @if ($product->rating_count)<span class="num text-ink-500">({{ $product->rating_count }})</span>@endif</h2>
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
            <div class="space-y-4">
                @forelse ($reviews as $review)
                    <article class="border-b border-ink-100 pb-4 last:border-0">
                        <div class="flex items-center gap-2">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-ink-100 text-[12px] font-bold">{{ mb_substr($review->name, 0, 1) }}</span>
                            <span class="text-[14px] font-semibold">{{ $review->name }}</span>
                            <span class="flex text-sun-500" aria-label="{{ $review->rating }} trên 5 sao">@for ($s = 0; $s < $review->rating; $s++)<x-ico name="star" size="13" />@endfor</span>
                            <span class="num ml-auto text-[12px] text-ink-400">{{ $review->created_at->format('d/m/Y') }}</span>
                        </div>
                        <p class="mt-2 text-[14px] text-ink-700">{{ $review->comment }}</p>
                        @if ($review->reply)
                            <p class="mt-2 rounded-lg bg-ink-50 p-3 text-[13px] text-ink-700"><b>Phản hồi của cửa hàng:</b> {{ $review->reply }}</p>
                        @endif
                    </article>
                @empty
                    <p class="text-[14px] text-ink-500">Chưa có đánh giá. Hãy là người đầu tiên chia sẻ trải nghiệm.</p>
                @endforelse
            </div>
            <div>
                @auth
                    <form action="{{ route('product.review', $product) }}" method="post" class="space-y-3 rounded-xl bg-ink-50 p-4">
                        @csrf
                        <p class="text-[14px] font-bold">Viết đánh giá</p>
                        <div>
                            <label for="rating" class="label">Số sao</label>
                            <select id="rating" name="rating" class="field">
                                @foreach ([5 => '5 sao - Rất hài lòng', 4 => '4 sao - Hài lòng', 3 => '3 sao - Bình thường', 2 => '2 sao - Chưa tốt', 1 => '1 sao - Rất tệ'] as $v => $l)
                                    <option value="{{ $v }}" @selected(old('rating', 5) == $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="comment" class="label">Nhận xét</label>
                            <textarea id="comment" name="comment" rows="3" class="field" placeholder="Sản phẩm dùng thế nào? Giao hàng, lắp đặt ra sao?">{{ old('comment') }}</textarea>
                            @error('comment')<p class="mt-1 text-[12px] text-brand-600">{{ $message }}</p>@enderror
                        </div>
                        <button class="btn btn-primary w-full">Gửi đánh giá</button>
                    </form>
                @else
                    <div class="rounded-xl bg-ink-50 p-4 text-[14px]">
                        <p class="font-bold">Bạn đã mua sản phẩm này?</p>
                        <p class="mt-1 text-ink-600">Đăng nhập để viết đánh giá.</p>
                        <a href="{{ route('login') }}" class="btn btn-outline mt-3">Đăng nhập</a>
                    </div>
                @endauth
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="card">
            <h2 class="mb-3 text-[18px] font-extrabold">Sản phẩm tương tự</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($related as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
