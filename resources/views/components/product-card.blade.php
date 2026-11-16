@props(['product', 'compare' => false])
@php
    $flash = $product->flashItem();
    $off = $product->discount_percent;
@endphp
<article class="pcard group relative flex h-full flex-col rounded-xl bg-white p-2.5 ring-1 ring-ink-100 transition hover:shadow-[0_8px_24px_-8px_rgba(0,0,0,.18)] hover:ring-brand-300 sm:p-3">
    <a href="{{ route('product', $product->slug) }}" class="relative mb-2 block aspect-square overflow-hidden rounded-lg bg-linear-to-b from-ink-50 to-white">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="pcard-art h-full w-full object-contain">
        @else
            <div class="pcard-art absolute inset-[12%]"><x-art :type="$product->art_key" /></div>
        @endif
        <div class="absolute left-1 top-1 flex flex-col items-start gap-1">
            @foreach ($product->labels->take(2) as $label)
                <span class="rounded px-1.5 py-0.5 text-[10px] font-bold leading-tight text-white" style="background: {{ $label->color }}">{{ $label->name }}</span>
            @endforeach
        </div>
        @if ($product->stock <= 0)
            <span class="absolute inset-x-0 bottom-0 bg-ink-900/75 py-1 text-center text-[11px] font-semibold text-white">Tạm hết hàng</span>
        @endif
    </a>

    @if ($flash)
        <div class="mb-1.5 inline-flex w-fit items-center gap-1 rounded bg-brand-600 px-1.5 py-0.5 text-[11px] font-bold text-white">
            <x-ico name="bolt" size="12" class="text-sun-400" /><span class="num" data-countdown="{{ \App\Models\FlashSale::current()->ends_at->toIso8601String() }}">--:--:--</span>
        </div>
    @endif

    <h3 class="line-clamp-2 min-h-[2.6em] text-[13px] font-medium leading-[1.3] text-ink-800">
        <a href="{{ route('product', $product->slug) }}" class="hover:text-brand-600">{{ $product->name }}</a>
    </h3>

    <div class="mt-1.5 flex min-h-[18px] items-center gap-1.5">
        @if ($product->old_price)
            <s class="num text-[12px] text-ink-400">{{ vnd($product->old_price) }}</s>
            <span class="num rounded bg-brand-50 px-1 text-[11px] font-bold text-brand-600">-{{ $off }}%</span>
        @endif
    </div>
    <p class="num text-[16px] font-extrabold text-brand-600">{{ vnd($product->final_price) }}</p>

    @if ($flash)
        <div class="relative mt-2 h-5 overflow-hidden rounded-full bg-sun-300/50">
            <div class="prog absolute inset-y-0 left-0 rounded-full" style="width: {{ max(12, round($flash->sold / max(1, $flash->quantity) * 100)) }}%"></div>
            <span class="num relative grid h-full place-items-center text-[11px] font-bold text-ink-900">{{ $flash->quantity - $flash->sold <= 2 ? 'Sắp cháy hàng · ' : '' }}Đã bán {{ $flash->sold }}/{{ $flash->quantity }} suất</span>
        </div>
    @endif

    <div class="mt-auto flex items-center justify-between gap-1 pt-2">
        <span class="flex items-center gap-1 text-[11.5px] text-ink-500">
            <x-ico name="star" size="13" class="text-sun-500" />
            <span class="num">{{ $product->rating_count ? number_format($product->rating_avg, 1, ',', '.') : 'Mới' }} · Đã bán {{ short_count($product->sold_count) }}</span>
        </span>
        @if ($compare)
            <button type="button" data-compare="{{ json_encode(['id' => $product->id, 'name' => $product->name]) }}"
                class="flex items-center gap-1 text-[12px]" :class="$store.shop.inCompare({{ $product->id }}) ? 'font-semibold text-brand-600' : 'text-sky-700 hover:text-sky-900'">
                <x-ico name="plus" size="14" /><span x-text="$store.shop.inCompare({{ $product->id }}) ? 'Đã chọn' : 'So sánh'">So sánh</span>
            </button>
        @elseif ($product->stock > 0)
            <button type="button" data-add="{{ $product->id }}" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-600 transition hover:bg-brand-600 hover:text-white" aria-label="Thêm {{ $product->name }} vào giỏ">
                <x-ico name="cart" size="16" />
            </button>
        @endif
    </div>
    @if ($compare && $product->stock > 0)
        <button type="button" data-add="{{ $product->id }}" class="mt-2 rounded-lg bg-brand-600 py-1.5 text-[12px] font-bold text-white transition hover:bg-brand-700 lg:opacity-0 lg:group-hover:opacity-100 lg:focus:opacity-100">Thêm vào giỏ</button>
    @endif
</article>
