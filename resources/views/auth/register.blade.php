@extends('layouts.app')
@section('title', 'Đăng ký tài khoản')

@section('content')
<div class="wrap max-w-md py-8">
    <form action="{{ route('register') }}" method="post" class="card space-y-4">
        @csrf
        <h1 class="text-[22px] font-extrabold">Tạo tài khoản</h1>
        <p class="text-[13px] text-ink-600">Tài khoản giúp bạn xem lại đơn hàng, lưu sản phẩm yêu thích và gửi yêu cầu trả hàng.</p>
        <div><label for="name" class="label">Họ tên</label><input id="name" name="name" value="{{ old('name') }}" class="field" autocomplete="name" required>@error('name')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
        <div><label for="email" class="label">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="field" autocomplete="email" required>@error('email')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
        <div><label for="phone" class="label">Số điện thoại <span class="font-normal text-ink-400">(không bắt buộc)</span></label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="field" autocomplete="tel">@error('phone')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
        <div><label for="password" class="label">Mật khẩu (ít nhất 8 ký tự)</label><input id="password" name="password" type="password" class="field" autocomplete="new-password" required>@error('password')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
        <div><label for="password_confirmation" class="label">Nhập lại mật khẩu</label><input id="password_confirmation" name="password_confirmation" type="password" class="field" autocomplete="new-password" required></div>
        <button class="btn btn-primary w-full !py-3">Đăng ký</button>
        <p class="text-center text-[13px] text-ink-600">Đã có tài khoản? <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline">Đăng nhập</a></p>
    </form>
</div>
@endsection
