@extends('layouts.app')
@section('title', 'Không tìm thấy trang')

@section('content')
<div class="wrap max-w-xl py-14 text-center">
    <p class="text-[64px] font-black leading-none text-brand-600">404</p>
    <h1 class="mt-3 text-[22px] font-extrabold">Không tìm thấy trang</h1>
    <p class="mt-2 text-[14px] text-ink-600">Trang bạn tìm có thể đã đổi địa chỉ hoặc sản phẩm đã ngừng bán. Thử tìm sản phẩm khác nhé.</p>
    <form action="{{ route('search') }}" class="mx-auto mt-6 flex h-11 max-w-sm overflow-hidden rounded-xl border-2 border-brand-600 bg-white">
        <label for="q404" class="sr-only">Tìm sản phẩm</label>
        <input id="q404" name="q" class="min-w-0 flex-1 px-4 text-[14px] outline-none" placeholder="Tìm sản phẩm">
        <button class="bg-brand-600 px-4 font-bold text-white">Tìm</button>
    </form>
    <a href="{{ route('home') }}" class="btn btn-outline mt-4">Về trang chủ</a>
</div>
@endsection
