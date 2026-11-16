@extends('layouts.app')
@section('title', 'Liên hệ')

@section('content')
<div class="wrap space-y-4 py-4">
    @include('partials.breadcrumb', ['crumbs' => [['Liên hệ', null]]])
    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)]">
        <section class="card space-y-3 self-start text-[14px]">
            <h1 class="text-[22px] font-extrabold">Liên hệ</h1>
            <p><span class="block text-[12px] text-ink-500">Tư vấn mua hàng</span><b class="num text-[18px]">{{ setting('hotline_sales') }}</b></p>
            <p><span class="block text-[12px] text-ink-500">Chăm sóc khách hàng</span><b class="num text-[18px]">{{ setting('hotline_care') }}</b></p>
            <p><span class="block text-[12px] text-ink-500">Email</span>{{ setting('email') }}</p>
            <p><span class="block text-[12px] text-ink-500">Địa chỉ</span>{{ setting('address') }}</p>
        </section>
        <form action="{{ route('contact.send') }}" method="post" class="card grid gap-4 sm:grid-cols-2">
            @csrf
            <h2 class="text-[16px] font-extrabold sm:col-span-2">Gửi tin nhắn cho cửa hàng</h2>
            <div><label for="name" class="label">Họ tên</label><input id="name" name="name" value="{{ old('name') }}" class="field" required>@error('name')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
            <div><label for="phone" class="label">Số điện thoại</label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="field">@error('phone')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
            <div><label for="email" class="label">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="field">@error('email')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
            <div><label for="subject" class="label">Chủ đề</label><input id="subject" name="subject" value="{{ old('subject') }}" class="field"></div>
            <div class="sm:col-span-2"><label for="message" class="label">Nội dung</label><textarea id="message" name="message" rows="5" class="field" required>{{ old('message') }}</textarea>@error('message')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><button class="btn btn-primary">Gửi tin nhắn</button></div>
        </form>
    </div>
</div>
@endsection
