@extends('layouts.app')
@section('title', 'Tra cứu đơn hàng')

@section('content')
<div class="wrap max-w-2xl space-y-4 py-6">
    <h1 class="text-[22px] font-extrabold">Tra cứu đơn hàng</h1>
    <form class="card grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        <div><label for="code" class="label">Mã đơn hàng</label><input id="code" name="code" value="{{ request('code') }}" class="field uppercase" placeholder="DH261116ABCDE" required></div>
        <div><label for="phone" class="label">Số điện thoại đặt hàng</label><input id="phone" name="phone" type="tel" value="{{ request('phone') }}" class="field" placeholder="09xxxxxxxx" required></div>
        <button class="btn btn-primary">Tra cứu</button>
    </form>

    @if ($searched && ! $order)
        <p class="card text-[14px] text-ink-700">Không tìm thấy đơn hàng. Kiểm tra lại mã đơn và số điện thoại đã dùng khi đặt, hoặc gọi {{ setting('hotline_care') }}.</p>
    @elseif ($order)
        <section class="card">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-[16px] font-extrabold">Đơn <span class="num">{{ $order->code }}</span></h2>
                <span @class(['rounded-full px-2.5 py-1 text-[12px] font-bold', 'bg-amber-100 text-amber-800' => $order->status === 'pending', 'bg-sky-100 text-sky-800' => in_array($order->status, ['confirmed', 'shipping']), 'bg-green-100 text-green-800' => $order->status === 'completed', 'bg-ink-100 text-ink-700' => in_array($order->status, ['cancelled', 'returned'])])>{{ $order->status_label }}</span>
            </div>
            <p class="mt-1 text-[13px] text-ink-500">Đặt lúc {{ $order->created_at->format('H:i d/m/Y') }} · {{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? '' }}</p>
            @php $steps = ['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao', 'completed' => 'Hoàn thành']; $at = array_search($order->status, array_keys($steps)); @endphp
            @if ($at !== false)
                <ol class="mt-4 grid grid-cols-4 gap-1 text-center text-[11px] sm:text-[12px]">
                    @foreach (array_values($steps) as $n => $label)
                        <li><span class="mx-auto mb-1 block h-1.5 rounded-full {{ $n <= $at ? 'bg-brand-600' : 'bg-ink-100' }}"></span><span class="{{ $n <= $at ? 'font-semibold text-ink-900' : 'text-ink-400' }}">{{ $label }}</span></li>
                    @endforeach
                </ol>
            @endif
            @foreach ($order->shipments as $shipment)
                <p class="mt-3 rounded-xl bg-ink-50 p-3 text-[13px]">{{ $shipment->carrier }}@if ($shipment->tracking_code) · Mã vận đơn <b class="num">{{ $shipment->tracking_code }}</b>@endif · {{ \App\Models\Shipment::STATUSES[$shipment->status] ?? '' }}</p>
            @endforeach
            <div class="mt-3"><ul class="divide-y divide-ink-100">
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
        </dl></div>
        </section>
    @endif
</div>
@endsection
