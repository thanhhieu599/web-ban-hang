@extends('layouts.app')
@section('title', 'Đơn hàng '.$order->code)

@section('content')
<div class="wrap grid gap-4 py-4 lg:grid-cols-[220px_minmax(0,1fr)]">
    <nav class="card flex gap-1 self-start !p-2 lg:flex-col" aria-label="Tài khoản">
        <a href="{{ route('account') }}" class="rounded-lg px-3 py-2 text-[14px] {{ request()->routeIs('account', 'account.order') ? 'bg-brand-50 font-semibold text-brand-700' : 'hover:bg-ink-50' }}">Đơn hàng của tôi</a>
        <a href="{{ route('account.wishlist') }}" class="rounded-lg px-3 py-2 text-[14px] {{ request()->routeIs('account.wishlist') ? 'bg-brand-50 font-semibold text-brand-700' : 'hover:bg-ink-50' }}">Sản phẩm yêu thích</a>
        <form action="{{ route('logout') }}" method="post" class="ml-auto lg:ml-0">@csrf<button class="w-full rounded-lg px-3 py-2 text-left text-[14px] text-ink-600 hover:bg-ink-50">Đăng xuất</button></form>
    </nav>
    <div class="space-y-4">
        <section class="card">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-[20px] font-extrabold">Đơn <span class="num">{{ $order->code }}</span></h1>
                <span @class(['rounded-full px-2.5 py-1 text-[12px] font-bold', 'bg-amber-100 text-amber-800' => $order->status === 'pending', 'bg-sky-100 text-sky-800' => in_array($order->status, ['confirmed', 'shipping']), 'bg-green-100 text-green-800' => $order->status === 'completed', 'bg-ink-100 text-ink-700' => in_array($order->status, ['cancelled', 'returned'])])>{{ $order->status_label }}</span>
            </div>
            <p class="mt-1 text-[13px] text-ink-500">Đặt lúc {{ $order->created_at->format('H:i d/m/Y') }} · {{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? '' }} · {{ \App\Models\Order::PAYMENT_STATUSES[$order->payment_status] ?? '' }}</p>
            <p class="mt-2 text-[14px]">{{ $order->customer_name }} · <span class="num">{{ $order->phone }}</span><br><span class="text-ink-600">{{ $order->address }}, {{ $order->province }}</span></p>
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
            @if ($order->status === 'pending')
                <form action="{{ route('account.cancel', $order->code) }}" method="post" class="mt-4" x-data="{ sure: false }">
                    @csrf
                    <button type="button" x-show="!sure" @click="sure = true" class="btn btn-ghost">Hủy đơn hàng</button>
                    <span x-show="sure" x-cloak class="flex flex-wrap items-center gap-2 text-[14px]">Bạn chắc chắn muốn hủy đơn này?
                        <button class="btn btn-primary !py-2">Hủy đơn</button><button type="button" @click="sure = false" class="btn btn-ghost !py-2">Giữ đơn</button></span>
                </form>
            @endif
        </section>

        @foreach ($order->returns as $return)
            <section class="card text-[14px]">
                <h2 class="text-[16px] font-extrabold">Yêu cầu trả hàng: {{ \App\Models\OrderReturn::STATUSES[$return->status] ?? '' }}</h2>
                <p class="mt-1 text-ink-600">{{ $return->reason }}</p>
                @if ($return->admin_note)<p class="mt-2 rounded-lg bg-ink-50 p-3">Phản hồi của cửa hàng: {{ $return->admin_note }}</p>@endif
            </section>
        @endforeach

        @if ($order->status === 'completed' && $order->returns->whereIn('status', ['requested', 'approved'])->isEmpty())
            <form action="{{ route('account.return', $order->code) }}" method="post" class="card space-y-3">
                @csrf
                <h2 class="text-[16px] font-extrabold">Yêu cầu trả hàng</h2>
                <label for="reason" class="label">Lý do trả hàng</label>
                <textarea id="reason" name="reason" rows="3" class="field" placeholder="Mô tả tình trạng sản phẩm và lý do bạn muốn trả">{{ old('reason') }}</textarea>
                @error('reason')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror
                <button class="btn btn-outline">Gửi yêu cầu</button>
            </form>
        @endif
    </div>
</div>
@endsection
