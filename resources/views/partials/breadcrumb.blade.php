{{-- $crumbs: [[tên, url|null], ...] --}}
<nav class="flex flex-wrap items-center gap-1.5 text-[13px] text-ink-500" aria-label="Breadcrumb">
    <a href="{{ route('home') }}" class="hover:text-brand-600">Trang chủ</a>
    @foreach ($crumbs as [$name, $url])
        <x-ico name="right" size="12" />
        @if ($url && ! $loop->last)
            <a href="{{ $url }}" class="hover:text-brand-600">{{ $name }}</a>
        @else
            <span class="font-semibold text-ink-800">{{ $name }}</span>
        @endif
    @endforeach
</nav>
