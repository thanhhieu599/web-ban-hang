@extends('layouts.app')
@section('title', 'Đặt hàng thành công')

@section('content')
<div class="wrap max-w-2xl space-y-4 py-6">
    <section class="card text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-green-100 text-green-700"><x-ico name="check" size="30" /></span>
        <h1 class="mt-3 text-[22px] font-extrabold">Đặt hàng thành công</h1>
        <p class="mt-1 text-[14px] text-ink-600">Mã đơn của bạn là <b class="num text-ink-900">{{ $order->code }}</b>. Cửa hàng sẽ gọi số <b class="num">{{ $order->phone }}</b> để xác nhận trong giờ làm việc.</p>
        @if ($order->payment_method === 'bank')
            <p class="mt-3 rounded-xl bg-ink-50 p-3 text-[13px] text-ink-700">Chuyển khoản: {{ setting('bank_info') }}.<br>Nội dung: <b>{{ $order->code }}</b></p>
        @endif
    </section>
    <section class="card">
        <h2 class="mb-1 text-[16px] font-extrabold">Chi tiết đơn hàng</h2>
        <ul class="divide-y divide-ink-100">
            @foreach ($order->items as $item)
                <li class="flex justify-between gap-3 py-2.5 text-[14px]">
                    <span class="min-w-0"><span class="font-medium">{{ $item->name }}</span>
                        @foreach ($item->options ?? [] as $option)<span class="block text-[12px] text-ink-500">{{ $option['name'] }}: {{ $option['label'] }}</span>@endforeach
                        <span class="num block text-[12px] text-ink-500">{{ vnd($item->price) }} × {{ $item->quantity }}</span></span>
                    <span class="num shrink-0 font-semibold">{{ vnd($item->line_total) }}</span>
                </li>
            @endforeach
        </ul>
        <dl class="mt-2 space-y-1.5 border-t border-ink-100 pt-3 text-[14px]">
            <div class="flex justify-between"><dt class="text-ink-600">Tạm tính</dt><dd class="num">{{ vnd($order->subtotal) }}</dd></div>
            @if ($order->discount_total)<div class="flex justify-between text-green-700"><dt>Giảm giá {{ $order->discount_code }}</dt><dd class="num">-{{ vnd($order->discount_total) }}</dd></div>@endif
            <div class="flex justify-between"><dt class="text-ink-600">Phí giao hàng</dt><dd class="num">{{ $order->shipping_fee ? vnd($order->shipping_fee) : 'Miễn phí' }}</dd></div>
            <div class="flex justify-between text-[16px] font-extrabold"><dt>Tổng cộng</dt><dd class="num text-brand-600">{{ vnd($order->total) }}</dd></div>
        </dl>
    </section>
    <div class="flex flex-wrap justify-center gap-2">
        <a href="{{ route('track', ['code' => $order->code, 'phone' => $order->phone]) }}" class="btn btn-outline">Theo dõi đơn hàng</a>
        <a href="{{ route('home') }}" class="btn btn-primary">Tiếp tục mua sắm</a>
    </div>
</div>
@endsection
