@extends('layouts.app')
@section('title', 'Quên mật khẩu')

@section('content')
<div class="wrap max-w-md py-8">
    <form action="{{ route('password.email') }}" method="post" class="card space-y-4">
        @csrf
        <h1 class="text-[22px] font-extrabold">Quên mật khẩu</h1>
        <p class="text-[13px] text-ink-600">Nhập email đã đăng ký. Cửa hàng sẽ gửi liên kết để bạn đặt mật khẩu mới.</p>
        <div><label for="email" class="label">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="field" autocomplete="email" required autofocus>@error('email')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
        <button class="btn btn-primary w-full !py-3">Gửi liên kết đặt lại</button>
        <p class="text-center text-[13px]"><a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline">Quay lại đăng nhập</a></p>
    </form>
</div>
@endsection
