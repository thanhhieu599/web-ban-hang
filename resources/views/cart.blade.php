@extends('layouts.app')
@section('title', 'Giỏ hàng')

@section('content')
<div class="wrap space-y-4 py-4">
    @include('partials.breadcrumb', ['crumbs' => [['Giỏ hàng', null]]])
    <h1 class="text-[22px] font-extrabold">Giỏ hàng</h1>

    @if ($lines->isEmpty())
        <div class="card grid place-items-center gap-3 py-14 text-center">
            <x-ico name="cart" size="44" class="text-ink-300" />
            <p class="text-[16px] font-bold">Giỏ hàng đang trống</p>
            <p class="text-[14px] text-ink-500">Xem các deal đang giảm giá hôm nay để bắt đầu.</p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-1">Tiếp tục mua sắm</a>
        </div>
    @else
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.9fr)_minmax(0,1fr)]">
            <section class="card divide-y divide-ink-100">
                @foreach ($lines as $line)
                    <div class="flex gap-3 py-4 first:pt-0 last:pb-0">
                        <a href="{{ route('product', $line->product->slug) }}" class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-ink-50 sm:h-24 sm:w-24">
                            @if ($line->product->image_url)<img src="{{ $line->product->image_url }}" alt="" class="h-full w-full object-contain">@else<x-art :type="$line->product->art_key" />@endif
                        </a>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('product', $line->product->slug) }}" class="line-clamp-2 text-[14px] font-semibold hover:text-brand-600">{{ $line->product->name }}</a>
                            @foreach ($line->options as $option)
                                <p class="text-[12px] text-ink-500">{{ $option['name'] }}: {{ $option['label'] }}@if ($option['price']) (+{{ vnd($option['price']) }})@endif</p>
                            @endforeach
                            <p class="num mt-1 text-[14px] font-bold text-brand-600">{{ vnd($line->unit) }}</p>
                            <div class="mt-2 flex items-center justify-between gap-3">
                                <form action="{{ route('cart.update') }}" method="post" class="flex items-center rounded-lg border border-ink-200">
                                    @csrf
                                    <input type="hidden" name="key" value="{{ $line->key }}">
                                    <button name="quantity" value="{{ $line->qty - 1 }}" class="grid h-8 w-8 place-items-center" aria-label="Giảm số lượng">−</button>
                                    <span class="num grid h-8 w-9 place-items-center border-x border-ink-200 text-[13px] font-semibold">{{ $line->qty }}</span>
                                    <button name="quantity" value="{{ min($line->qty + 1, $line->product->stock) }}" class="grid h-8 w-8 place-items-center" aria-label="Tăng số lượng">+</button>
                                </form>
                                <form action="{{ route('cart.update') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="key" value="{{ $line->key }}"><input type="hidden" name="quantity" value="0">
                                    <button class="text-[13px] text-ink-500 hover:text-brand-600">Xóa</button>
                                </form>
                            </div>
                        </div>
                        <p class="num hidden w-28 shrink-0 text-right text-[15px] font-extrabold sm:block">{{ vnd($line->total) }}</p>
                    </div>
                @endforeach
            </section>

            <aside class="space-y-4 self-start">
                <section class="card">
                    <p class="mb-2 text-[14px] font-bold">Mã giảm giá</p>
                    @if ($coupon)
                        <form action="{{ route('cart.coupon') }}" method="post" class="flex items-center justify-between rounded-xl bg-green-50 px-3 py-2 text-[13px]">
                            @csrf <input type="hidden" name="remove" value="1">
                            <span class="font-semibold text-green-800">{{ $coupon->code }}: {{ $coupon->name }}</span>
                            <button class="font-semibold text-ink-600 hover:text-brand-600">Bỏ</button>
                        </form>
                    @else
                        <form action="{{ route('cart.coupon') }}" method="post" class="flex gap-2">
                            @csrf
                            <label for="code" class="sr-only">Mã giảm giá</label>
                            <input id="code" name="code" value="{{ old('code') }}" class="field uppercase" placeholder="Nhập mã">
                            <button class="btn btn-ghost shrink-0">Áp dụng</button>
                        </form>
                        @error('code')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror
                    @endif
                </section>
                <section class="card">
                    <dl class="space-y-2 text-[14px]">
                <div class="flex justify-between"><dt class="text-ink-600">Tạm tính</dt><dd class="num font-semibold">{{ vnd(\App\Services\Cart::subtotal()) }}</dd></div>
                @if ($coupon)
                    <div class="flex justify-between text-green-700"><dt>Mã {{ $coupon->code }}</dt><dd class="num font-semibold">-{{ vnd(\App\Services\Cart::discount()) }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-ink-600">Phí giao hàng</dt><dd class="num font-semibold">{{ \App\Services\Cart::shipping() ? vnd(\App\Services\Cart::shipping()) : 'Miễn phí' }}</dd></div>
                <div class="flex items-end justify-between border-t border-ink-100 pt-3"><dt class="font-bold">Tổng cộng</dt><dd class="num text-[22px] font-black text-brand-600">{{ vnd(\App\Services\Cart::total()) }}</dd></div>
            </dl>
                    <a href="{{ route('checkout') }}" class="btn btn-primary mt-4 w-full !py-3 !text-[16px]">Tiến hành đặt hàng</a>
                    <a href="{{ route('home') }}" class="mt-3 block text-center text-[13px] font-semibold text-ink-600 hover:text-brand-600">Tiếp tục mua sắm</a>
                </section>
            </aside>
        </div>
    @endif
</div>
@endsection
