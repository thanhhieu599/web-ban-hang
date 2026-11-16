@extends('layouts.app')
@section('title', 'Tài khoản')

@section('content')
<div class="wrap grid gap-4 py-4 lg:grid-cols-[220px_minmax(0,1fr)]">
    <nav class="card flex gap-1 self-start !p-2 lg:flex-col" aria-label="Tài khoản">
        <a href="{{ route('account') }}" class="rounded-lg px-3 py-2 text-[14px] {{ request()->routeIs('account', 'account.order') ? 'bg-brand-50 font-semibold text-brand-700' : 'hover:bg-ink-50' }}">Đơn hàng của tôi</a>
        <a href="{{ route('account.wishlist') }}" class="rounded-lg px-3 py-2 text-[14px] {{ request()->routeIs('account.wishlist') ? 'bg-brand-50 font-semibold text-brand-700' : 'hover:bg-ink-50' }}">Sản phẩm yêu thích</a>
        <form action="{{ route('logout') }}" method="post" class="ml-auto lg:ml-0">@csrf<button class="w-full rounded-lg px-3 py-2 text-left text-[14px] text-ink-600 hover:bg-ink-50">Đăng xuất</button></form>
    </nav>
    <div class="space-y-4">
        <section class="card">
            <h1 class="mb-3 text-[20px] font-extrabold">Đơn hàng của tôi</h1>
            @forelse ($orders as $order)
                <a href="{{ route('account.order', $order->code) }}" class="flex flex-wrap items-center justify-between gap-2 border-b border-ink-100 py-3 last:border-0 hover:bg-ink-50">
                    <span><span class="num block text-[14px] font-bold">{{ $order->code }}</span><span class="num text-[12px] text-ink-500">{{ $order->created_at->format('d/m/Y') }} · {{ $order->items_count }} sản phẩm</span></span>
                    <span class="flex items-center gap-3"><span class="num text-[14px] font-extrabold">{{ vnd($order->total) }}</span><span @class(['rounded-full px-2.5 py-1 text-[12px] font-bold', 'bg-amber-100 text-amber-800' => $order->status === 'pending', 'bg-sky-100 text-sky-800' => in_array($order->status, ['confirmed', 'shipping']), 'bg-green-100 text-green-800' => $order->status === 'completed', 'bg-ink-100 text-ink-700' => in_array($order->status, ['cancelled', 'returned'])])>{{ $order->status_label }}</span></span>
                </a>
            @empty
                <p class="text-[14px] text-ink-500">Bạn chưa có đơn hàng nào. <a href="{{ route('home') }}" class="font-semibold text-brand-600">Xem sản phẩm</a></p>
            @endforelse
            {{ $orders->links() }}
        </section>
        <form action="{{ route('account.update') }}" method="post" class="card grid gap-4 sm:grid-cols-2">
            @csrf
            <h2 class="text-[16px] font-extrabold sm:col-span-2">Thông tin cá nhân</h2>
            <div><label for="name" class="label">Họ tên</label><input id="name" name="name" value="{{ old('name', $user->name) }}" class="field" required>@error('name')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
            <div><label for="phone" class="label">Số điện thoại</label><input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" class="field">@error('phone')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><label for="address" class="label">Địa chỉ</label><input id="address" name="address" value="{{ old('address', $user->address) }}" class="field"></div>
            <div class="sm:col-span-2"><button class="btn btn-primary">Lưu thay đổi</button></div>
        </form>
    </div>
</div>
@endsection
