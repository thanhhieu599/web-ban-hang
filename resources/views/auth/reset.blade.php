@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="wrap max-w-md py-8">
    <form action="{{ route('password.update') }}" method="post" class="card space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <h1 class="text-[22px] font-extrabold">Đặt mật khẩu mới</h1>
        <div><label for="email" class="label">Email</label><input id="email" name="email" type="email" value="{{ old('email', $email) }}" class="field" autocomplete="email" required>@error('email')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
        <div><label for="password" class="label">Mật khẩu mới (ít nhất 8 ký tự)</label><input id="password" name="password" type="password" class="field" autocomplete="new-password" required>@error('password')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
        <div><label for="password_confirmation" class="label">Nhập lại mật khẩu mới</label><input id="password_confirmation" name="password_confirmation" type="password" class="field" autocomplete="new-password" required></div>
        <button class="btn btn-primary w-full !py-3">Lưu mật khẩu mới</button>
    </form>
</div>
@endsection
