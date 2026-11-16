@extends('layouts.app')
@section('title', 'Thanh toán')

@section('content')
<div class="wrap space-y-4 py-4">
    @include('partials.breadcrumb', ['crumbs' => [['Giỏ hàng', route('cart')], ['Thanh toán', null]]])
    <h1 class="text-[22px] font-extrabold">Thông tin đặt hàng</h1>
    @guest
        <p class="rounded-xl bg-white px-4 py-3 text-[13px] text-ink-600">Bạn đang đặt hàng không cần tài khoản. <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline">Đăng nhập</a> nếu muốn lưu đơn vào tài khoản.</p>
    @endguest
    @error('cart')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror

    <form action="{{ route('checkout.place') }}" method="post" class="grid gap-4 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        @csrf
        <div class="space-y-4">
            <section class="card grid gap-4 sm:grid-cols-2">
                <h2 class="text-[16px] font-extrabold sm:col-span-2">Người nhận</h2>
                <div>
                    <label for="customer_name" class="label">Họ tên</label>
                    <input id="customer_name" name="customer_name" value="{{ old('customer_name', $user?->name) }}" class="field" autocomplete="name" required>
                    @error('customer_name')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="label">Số điện thoại</label>
                    <input id="phone" name="phone" type="tel" inputmode="numeric" value="{{ old('phone', $user?->phone) }}" class="field" autocomplete="tel" placeholder="09xxxxxxxx" required>
                    @error('phone')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="label">Email <span class="font-normal text-ink-400">(không bắt buộc)</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" class="field" autocomplete="email">
                    @error('email')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="province" class="label">Tỉnh / Thành phố</label>
                    <input id="province" name="province" value="{{ old('province') }}" class="field" placeholder="Hà Nội" required>
                    @error('province')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="label">Địa chỉ nhận hàng</label>
                    <input id="address" name="address" value="{{ old('address', $user?->address) }}" class="field" autocomplete="street-address" placeholder="Số nhà, tên đường, phường/xã" required>
                    @error('address')<p class="mt-1 text-[12px] font-medium text-brand-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="note" class="label">Ghi chú <span class="font-normal text-ink-400">(không bắt buộc)</span></label>
                    <textarea id="note" name="note" rows="2" class="field" placeholder="Ví dụ: giao sau 18 giờ, gọi trước khi đến">{{ old('note') }}</textarea>
                </div>
            </section>

            <section class="card">
                <h2 class="mb-3 text-[16px] font-extrabold">Hình thức thanh toán</h2>
                <div class="space-y-2">
                    @foreach (\App\Models\Order::PAYMENT_METHODS as $key => $label)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-ink-200 p-3 has-checked:border-brand-600 has-checked:bg-brand-50">
                            <input type="radio" name="payment_method" value="{{ $key }}" class="mt-1 accent-brand-600" @checked(old('payment_method', 'cod') === $key)>
                            <span>
                                <span class="block text-[14px] font-semibold">{{ $label }}</span>
                                <span class="block text-[12px] text-ink-500">{{ ['cod' => 'Trả tiền mặt hoặc quẹt thẻ khi nhận hàng.', 'bank' => setting('bank_info'), 'installment' => 'Nhân viên sẽ gọi để hướng dẫn thủ tục trả góp qua thẻ tín dụng.'][$key] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="card self-start">
            <h2 class="mb-3 text-[16px] font-extrabold">Đơn hàng ({{ $lines->sum('qty') }} sản phẩm)</h2>
            <ul class="mb-4 divide-y divide-ink-100">
                @foreach ($lines as $line)
                    <li class="flex gap-3 py-2.5 text-[13px]">
                        <span class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-ink-50">@if ($line->product->image_url)<img src="{{ $line->product->image_url }}" alt="" class="h-full w-full object-contain">@else<x-art :type="$line->product->art_key" />@endif</span>
                        <span class="min-w-0 flex-1"><span class="line-clamp-2 font-medium">{{ $line->product->name }}</span><span class="num text-ink-500">SL: {{ $line->qty }}</span></span>
                        <span class="num shrink-0 font-semibold">{{ vnd($line->total) }}</span>
                    </li>
                @endforeach
            </ul>
            <dl class="space-y-2 text-[14px]">
                <div class="flex justify-between"><dt class="text-ink-600">Tạm tính</dt><dd class="num font-semibold">{{ vnd(\App\Services\Cart::subtotal()) }}</dd></div>
                @if ($coupon)
                    <div class="flex justify-between text-green-700"><dt>Mã {{ $coupon->code }}</dt><dd class="num font-semibold">-{{ vnd(\App\Services\Cart::discount()) }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-ink-600">Phí giao hàng</dt><dd class="num font-semibold">{{ \App\Services\Cart::shipping() ? vnd(\App\Services\Cart::shipping()) : 'Miễn phí' }}</dd></div>
                <div class="flex items-end justify-between border-t border-ink-100 pt-3"><dt class="font-bold">Tổng cộng</dt><dd class="num text-[22px] font-black text-brand-600">{{ vnd(\App\Services\Cart::total()) }}</dd></div>
            </dl>
            <button class="btn btn-primary mt-4 w-full !py-3 !text-[16px]">Đặt hàng</button>
            <p class="mt-3 text-center text-[12px] text-ink-500">Bấm Đặt hàng nghĩa là bạn đồng ý với chính sách mua hàng của cửa hàng.</p>
        </aside>
    </form>
</div>
@endsection
