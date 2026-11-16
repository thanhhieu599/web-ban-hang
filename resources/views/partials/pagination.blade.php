@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-1.5 pt-5 text-[13px]" aria-label="Phân trang">
        @if ($paginator->onFirstPage())
            <span class="rounded-lg border border-ink-100 px-3 py-2 text-ink-300">Trước</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-lg border border-ink-200 px-3 py-2 hover:border-brand-600 hover:text-brand-600">Trước</a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-1 text-ink-400">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="num rounded-lg bg-brand-600 px-3.5 py-2 font-bold text-white" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="num hidden rounded-lg border border-ink-200 px-3.5 py-2 hover:border-brand-600 hover:text-brand-600 sm:block">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-lg border border-ink-200 px-3 py-2 hover:border-brand-600 hover:text-brand-600">Sau</a>
        @else
            <span class="rounded-lg border border-ink-100 px-3 py-2 text-ink-300">Sau</span>
        @endif
    </nav>
@endif
