@extends('layouts.app')
@section('title', 'Sản phẩm yêu thích')

@section('content')
<div class="wrap grid gap-4 py-4 lg:grid-cols-[220px_minmax(0,1fr)]">
    <nav class="card flex gap-1 self-start !p-2 lg:flex-col" aria-label="Tài khoản">
        <a href="{{ route('account') }}" class="rounded-lg px-3 py-2 text-[14px] {{ request()->routeIs('account', 'account.order') ? 'bg-brand-50 font-semibold text-brand-700' : 'hover:bg-ink-50' }}">Đơn hàng của tôi</a>
        <a href="{{ route('account.wishlist') }}" class="rounded-lg px-3 py-2 text-[14px] {{ request()->routeIs('account.wishlist') ? 'bg-brand-50 font-semibold text-brand-700' : 'hover:bg-ink-50' }}">Sản phẩm yêu thích</a>
        <form action="{{ route('logout') }}" method="post" class="ml-auto lg:ml-0">@csrf<button class="w-full rounded-lg px-3 py-2 text-left text-[14px] text-ink-600 hover:bg-ink-50">Đăng xuất</button></form>
    </nav>
    <section class="card">
        <h1 class="mb-3 text-[20px] font-extrabold">Sản phẩm yêu thích</h1>
        @if ($items->isEmpty())
            <p class="text-[14px] text-ink-500">Chưa có sản phẩm nào. Bấm biểu tượng ngôi sao ở trang sản phẩm để lưu lại.</p>
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($items as $item)<x-product-card :product="$item->product" />@endforeach
            </div>
        @endif
    </section>
</div>
@endsection
