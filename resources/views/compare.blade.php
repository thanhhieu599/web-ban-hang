@extends('layouts.app')
@section('title', 'So sánh sản phẩm')

@section('content')
<div class="wrap space-y-4 py-4">
    @include('partials.breadcrumb', ['crumbs' => [['So sánh sản phẩm', null]]])
    <h1 class="text-[22px] font-extrabold">So sánh sản phẩm</h1>
    @if ($products->count() < 2)
        <p class="card text-[14px] text-ink-600">Chọn ít nhất 2 sản phẩm ở trang danh mục (nút So sánh trên mỗi sản phẩm) để so sánh.</p>
    @else
        <div class="card overflow-x-auto !p-0">
            <table class="w-full min-w-[560px] text-[13px]">
                <tbody>
                    <tr class="align-top">
                        <th class="w-32 bg-ink-50 p-3 text-left font-semibold">Sản phẩm</th>
                        @foreach ($products as $p)
                            <td class="p-3">
                                <a href="{{ route('product', $p->slug) }}" class="block">
                                    <span class="mx-auto mb-2 block h-24 w-24">@if ($p->image_url)<img src="{{ $p->image_url }}" alt="" class="h-full w-full object-contain">@else<x-art :type="$p->art_key" />@endif</span>
                                    <span class="font-semibold hover:text-brand-600">{{ $p->name }}</span>
                                </a>
                            </td>
                        @endforeach
                    </tr>
                    @php
                        $rows = ['Giá' => fn ($p) => vnd($p->final_price), 'Thương hiệu' => fn ($p) => $p->brand->name ?? '—', 'Bảo hành' => fn ($p) => $p->warranty ?: '—',
                            'Đánh giá' => fn ($p) => $p->rating_count ? number_format($p->rating_avg, 1, ',', '.').' / 5 ('.$p->rating_count.')' : 'Chưa có', 'Đã bán' => fn ($p) => short_count($p->sold_count)];
                        foreach ($specKeys as $k) { $rows[$k] = fn ($p) => $p->specs[$k] ?? '—'; }
                        $rows['Tính năng'] = fn ($p) => $p->attributeValues->pluck('value')->join(', ') ?: '—';
                    @endphp
                    @foreach ($rows as $label => $get)
                        <tr class="border-t border-ink-100">
                            <th class="bg-ink-50 p-3 text-left font-semibold">{{ $label }}</th>
                            @foreach ($products as $p)<td class="num p-3 {{ $label === 'Giá' ? 'text-[15px] font-extrabold text-brand-600' : '' }}">{{ $get($p) }}</td>@endforeach
                        </tr>
                    @endforeach
                    <tr class="border-t border-ink-100">
                        <th class="bg-ink-50 p-3"></th>
                        @foreach ($products as $p)<td class="p-3">@if ($p->stock > 0)<button data-add="{{ $p->id }}" class="btn btn-primary !py-2 !text-[13px]">Thêm vào giỏ</button>@else<span class="text-ink-500">Tạm hết hàng</span>@endif</td>@endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
