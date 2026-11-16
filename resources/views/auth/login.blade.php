@extends('layouts.app')
@section('title', 'Đăng nhập')

@section('content')
<div class="wrap max-w-md py-8">
    <form action="{{ route('login') }}" method="post" class="card space-y-4">
        @csrf
        <h1 class="text-[22px] font-extrabold">Đăng nhập</h1>
        <div><label for="email" class="label">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="field" autocomplete="email" required autofocus>@error('email')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
        <div><label for="password" class="label">Mật khẩu</label><input id="password" name="password" type="password" class="field" autocomplete="current-password" required></div>
        <div class="flex items-center justify-between gap-2 text-[13px]">
            <label class="flex items-center gap-2"><input type="checkbox" name="remember" value="1" class="accent-brand-600">Ghi nhớ đăng nhập</label>
            <a href="{{ route('password.request') }}" class="font-semibold text-brand-600 hover:underline">Quên mật khẩu?</a>
        </div>
        <button class="btn btn-primary w-full !py-3">Đăng nhập</button>
        <p class="text-center text-[13px] text-ink-600">Chưa có tài khoản? <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:underline">Đăng ký</a></p>
    </form>
</div>
@endsection
