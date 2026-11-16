@extends('layouts.app')
@section('title', 'Câu hỏi thường gặp')

@section('content')
<div class="wrap max-w-3xl space-y-4 py-4">
    @include('partials.breadcrumb', ['crumbs' => [['Câu hỏi thường gặp', null]]])
    <h1 class="text-[22px] font-extrabold">Câu hỏi thường gặp</h1>
    @forelse ($groups as $group => $faqs)
        <section class="card">
            <h2 class="mb-2 text-[16px] font-extrabold">{{ $group }}</h2>
            <div class="divide-y divide-ink-100">
                @foreach ($faqs as $faq)
                    <details class="group py-3">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-[14px] font-semibold">{{ $faq->question }}<x-ico name="down" size="16" class="shrink-0 transition group-open:rotate-180" /></summary>
                        <p class="mt-2 text-[14px] leading-relaxed text-ink-600">{{ $faq->answer }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    @empty
        <p class="card text-[14px] text-ink-500">Chưa có câu hỏi nào.</p>
    @endforelse
    <p class="text-[14px] text-ink-600">Chưa thấy câu trả lời bạn cần? <a href="{{ route('contact') }}" class="font-semibold text-brand-600 hover:underline">Gửi câu hỏi cho cửa hàng</a>.</p>
</div>
@endsection
