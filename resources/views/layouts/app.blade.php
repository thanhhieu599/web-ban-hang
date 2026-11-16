<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = trim($site['site_name'].' '.$site['site_tagline']);
        $pageTitle = trim($__env->yieldContent('title'));
        $description = trim($__env->yieldContent('description')) ?: $site['meta_description'];
    @endphp
    <title>{{ $pageTitle ? $pageTitle.' | '.$siteName : $siteName.' - Điện máy chính hãng, giá tốt' }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle ?: $siteName }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif
    <meta name="theme-color" content="#e0121f">
    <link rel="icon" href="{{ $site['favicon'] ? asset('storage/'.$site['favicon']) : asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body data-cart="{{ $cartCount }}" data-flash="{{ session('flash') }}" class="min-h-screen pb-16 lg:pb-0">
{{-- Gradient dùng chung cho hình minh họa --}}
<svg width="0" height="0" class="absolute" aria-hidden="true"><defs>
    <linearGradient id="g-screen" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1e3a8a"/><stop offset=".55" stop-color="#0ea5e9"/><stop offset="1" stop-color="#a5f3fc"/></linearGradient>
    <linearGradient id="g-phone" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fb7185"/><stop offset="1" stop-color="#7c3aed"/></linearGradient>
    <linearGradient id="g-steel" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#3a434d"/><stop offset=".5" stop-color="#4b5662"/><stop offset="1" stop-color="#2c333b"/></linearGradient>
</defs></svg>

<div x-data="shell" @keydown.escape.window="mega = false; drawer = false; searchOpen = false">
    {{-- Dải thông báo --}}
    <div class="hidden bg-ink-900 text-[12px] text-white/80 md:block">
        <div class="wrap flex h-8 items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                @foreach (array_filter(explode('|', $site['top_strip'])) as $i => $text)
                    <span class="flex items-center gap-1.5"><x-ico :name="['truck', 'tool', 'refresh'][$i % 3]" size="14" class="text-sun-400" />{{ trim($text) }}</span>
                @endforeach
            </div>
            <div class="flex items-center gap-4">
                <span>Tổng đài mua hàng <b class="num text-white">{{ $site['hotline_sales'] }}</b></span>
                <a href="{{ route('news') }}" class="hover:text-white">Tin tức</a>
                <a href="{{ route('faq') }}" class="hover:text-white">Hỏi đáp</a>
            </div>
        </div>
    </div>

    <header class="sticky top-0 z-50 bg-white shadow-[0_1px_0_#eee]">
        <div class="wrap relative">
            <div class="flex h-16 items-center gap-3 lg:h-[72px] lg:gap-4">
                <button class="grid h-10 w-10 place-items-center rounded-lg text-ink-800 lg:hidden" @click="drawer = true" aria-label="Mở danh mục"><x-ico name="menu" size="22" /></button>

                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2" aria-label="{{ $siteName }} - Trang chủ">
                    @if ($site['logo'])
                    <img src="{{ asset('storage/'.$site['logo']) }}" alt="{{ $siteName }}" class="h-9 w-auto max-w-[170px] object-contain lg:h-10">
                    @else
                    <span class="grid h-9 w-9 place-items-center rounded-[10px] bg-brand-600 text-white shadow-[inset_0_-3px_0_rgba(0,0,0,.18)]">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 2 4 13.5h6.2L9 22l10-12.2h-6.4z"/></svg>
                    </span>
                    <span class="leading-none">
                        <span class="block text-[22px] font-black tracking-tight text-ink-900">{{ $site['site_name'] }}</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[.18em] text-brand-600">{{ $site['site_tagline'] }}</span>
                    </span>
                    @endif
                </a>

                <button class="hidden h-11 shrink-0 items-center gap-2 rounded-xl border px-3.5 text-[14px] font-semibold transition lg:flex"
                    :class="mega ? 'border-brand-600 bg-brand-600 text-white' : 'border-ink-200 text-ink-800 hover:border-ink-300'" @click.stop="mega = !mega" :aria-expanded="mega">
                    <x-ico name="menu" size="18" />Danh mục
                </button>

                <div class="relative hidden flex-1 md:block" @click.outside="searchOpen = false">
                    <form action="{{ route('search') }}" class="flex h-11 overflow-hidden rounded-xl border-2 border-brand-600 bg-white" role="search">
                        <label for="q" class="sr-only">Tìm kiếm sản phẩm</label>
                        <input id="q" name="q" type="search" value="{{ request('q') }}" autocomplete="off" @focus="searchOpen = true" placeholder="Bạn tìm gì hôm nay? Ví dụ: điều hòa 1 HP, máy giặt 9 kg…" class="min-w-0 flex-1 px-4 text-[14px] outline-none placeholder:text-ink-400">
                        <button class="grid w-14 place-items-center bg-brand-600 text-white hover:bg-brand-700" aria-label="Tìm"><x-ico name="search" size="20" /></button>
                    </form>
                    <div x-cloak x-show="searchOpen" x-transition.opacity class="absolute inset-x-0 top-[calc(100%+6px)] z-50 rounded-xl bg-white p-4 shadow-xl ring-1 ring-ink-100">
                        <p class="mb-2 text-[12px] font-semibold uppercase tracking-wider text-ink-500">Từ khóa nổi bật</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (array_filter(explode('|', $site['hot_keywords'])) as $keyword)
                                <a href="{{ route('search', ['q' => trim($keyword)]) }}" class="rounded-full bg-ink-50 px-3 py-1.5 text-[13px] text-ink-700 hover:bg-brand-50 hover:text-brand-700">{{ trim($keyword) }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="ml-auto flex items-center gap-1 md:ml-0 lg:gap-2">
                    <a href="{{ route('track') }}" class="hidden items-center gap-2 rounded-lg px-2 py-1.5 text-[13px] leading-tight text-ink-700 hover:bg-ink-50 xl:flex">
                        <x-ico name="truck" size="24" class="text-ink-800" /><span>Tra cứu<br>đơn hàng</span>
                    </a>
                    <a href="{{ route('cart') }}" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-[13px] text-ink-700 hover:bg-ink-50" aria-label="Giỏ hàng">
                        <span class="relative text-ink-800"><x-ico name="cart" size="24" />
                            <span class="num absolute -right-2 -top-1.5 grid h-[18px] min-w-[18px] place-items-center rounded-full bg-brand-600 px-1 text-[11px] font-bold text-white" x-text="$store.shop.cart">{{ $cartCount }}</span>
                        </span><span class="hidden lg:inline">Giỏ hàng</span>
                    </a>
                    @auth
                        <a href="{{ auth()->user()->isStaff() ? url('/admin') : route('account') }}" class="hidden items-center gap-2 rounded-lg px-2 py-1.5 text-[13px] text-ink-700 hover:bg-ink-50 lg:flex">
                            <x-ico name="user" size="24" class="text-ink-800" /><span class="max-w-[110px] truncate">{{ auth()->user()->isStaff() ? 'Quản trị' : auth()->user()->name }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden items-center gap-2 rounded-lg px-2 py-1.5 text-[13px] text-ink-700 hover:bg-ink-50 lg:flex">
                            <x-ico name="user" size="24" class="text-ink-800" />Đăng nhập
                        </a>
                    @endauth
                </div>
            </div>

            <form action="{{ route('search') }}" class="mb-3 flex h-10 overflow-hidden rounded-xl border-2 border-brand-600 md:hidden" role="search">
                <label for="qm" class="sr-only">Tìm kiếm sản phẩm</label>
                <input id="qm" name="q" type="search" value="{{ request('q') }}" placeholder="Bạn tìm gì hôm nay?" class="min-w-0 flex-1 px-3 text-[14px] outline-none">
                <button class="grid w-12 place-items-center bg-brand-600 text-white" aria-label="Tìm"><x-ico name="search" size="18" /></button>
            </form>

            {{-- Menu danh mục 2 cấp --}}
            <div x-cloak x-show="mega" x-transition.opacity.duration.150ms @click.outside="mega = false" class="absolute inset-x-0 top-full z-50 hidden lg:block">
                <div class="mx-4 mt-2 flex h-[460px] overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-ink-100">
                    <nav class="w-[260px] shrink-0 overflow-y-auto border-r border-ink-100 py-2" aria-label="Danh mục cấp 1">
                        @foreach ($menu as $i => $group)
                            <a href="{{ route('category', $group->slug) }}" @mouseenter="megaI = {{ $i }}" @focus="megaI = {{ $i }}"
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-[14px] transition"
                                :class="megaI === {{ $i }} ? 'bg-brand-50 font-semibold text-brand-700' : 'text-ink-700 hover:bg-ink-50'">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-ink-50"><x-cat-icon :category="$group" /></span>
                                <span class="flex-1">{{ $group->name }}</span>
                                @if ($group->sale_badge)<span class="tag-sale rounded px-1.5 py-0.5 text-[10px] font-bold">{{ $group->sale_badge }}</span>@endif
                                <x-ico name="right" size="14" class="text-ink-300" />
                            </a>
                        @endforeach
                        <div class="mx-4 my-2 border-t border-ink-100"></div>
                        <a href="{{ route('news') }}" class="block px-4 py-2 text-[13px] text-ink-600 hover:text-brand-600">Tin tức &amp; khuyến mãi</a>
                        <a href="{{ route('track') }}" class="block px-4 py-2 text-[13px] text-ink-600 hover:text-brand-600">Tra cứu đơn hàng</a>
                    </nav>
                    @foreach ($menu as $i => $group)
                        <div x-show="megaI === {{ $i }}" class="flex min-w-0 flex-1 flex-col p-6" @if ($i) x-cloak @endif>
                            <div class="mb-4 flex items-end justify-between gap-4">
                                <div>
                                    <p class="text-[12px] font-semibold uppercase tracking-wider text-brand-600">Danh mục</p>
                                    <p class="text-[20px] font-extrabold">{{ $group->name }}</p>
                                </div>
                                <a href="{{ route('category', $group->slug) }}" class="flex items-center gap-1 text-[13px] font-semibold text-brand-600 hover:underline">Xem tất cả <x-ico name="right" size="14" /></a>
                            </div>
                            <div class="grid grid-cols-4 gap-3">
                                @foreach ($group->children as $child)
                                    <a href="{{ route('category', $child->slug) }}" class="group flex flex-col items-center gap-2 rounded-xl p-3 text-center ring-1 ring-ink-100 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-brand-300">
                                        <span class="grid h-16 w-16 place-items-center rounded-full bg-linear-to-b from-ink-50 to-white"><x-cat-icon :category="$child" /></span>
                                        <span class="text-[13px] font-medium text-ink-800 group-hover:text-brand-700">{{ $child->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                            <div class="mt-auto flex items-center gap-4 rounded-xl bg-ink-900 p-4 text-white">
                                <span class="grid h-10 w-10 place-items-center rounded-lg bg-brand-600"><x-ico name="bolt" size="20" /></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[14px] font-bold">Săn deal giá sốc mỗi ngày</p>
                                    <p class="text-[12px] text-white/70">Trả góp 0% lãi · Giao lắp miễn phí · Đổi trả 30 ngày</p>
                                </div>
                                <a href="{{ route('home') }}#flash-sale" class="rounded-lg bg-sun-400 px-4 py-2 text-[13px] font-bold text-ink-900 hover:bg-sun-300">Xem deal</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="border-t border-ink-100">
            <div class="wrap">
                <div class="no-sb flex h-11 items-center gap-1 overflow-x-auto">
                    <a href="{{ route('home') }}#flash-sale" class="mr-2 shrink-0 rounded-md bg-brand-600 px-2.5 py-1 text-[12px] font-extrabold uppercase tracking-wide text-white">Khuyến mãi hot</a>
                    @foreach ($menu->flatMap->children->where('is_featured', true)->take(13) as $link)
                        <a href="{{ route('category', $link->slug) }}" @class(['shrink-0 rounded-md px-2.5 py-1.5 text-[13px] font-medium transition', 'bg-brand-50 text-brand-700' => request()->is('danh-muc/'.$link->slug), 'text-ink-700 hover:text-brand-600' => ! request()->is('danh-muc/'.$link->slug)])>{{ $link->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </header>

    <div x-cloak x-show="mega" x-transition.opacity class="fixed inset-0 z-40 hidden bg-black/40 lg:block" @click="mega = false"></div>

    {{-- Menu di động: 2 cấp dạng xổ xuống --}}
    <div x-cloak x-show="drawer" class="fixed inset-0 z-[70] lg:hidden">
        <div class="absolute inset-0 bg-black/50" @click="drawer = false"></div>
        <aside x-show="drawer" x-transition:enter="transition duration-200" x-transition:enter-start="-translate-x-full" x-transition:leave="transition duration-150" x-transition:leave-end="-translate-x-full"
            class="absolute inset-y-0 left-0 flex w-[86%] max-w-[360px] flex-col bg-white">
            <div class="flex items-center justify-between bg-brand-600 px-4 py-3 text-white">
                <span class="text-[16px] font-bold">Danh mục sản phẩm</span>
                <button @click="drawer = false" aria-label="Đóng"><x-ico name="x" size="22" /></button>
            </div>
            <div class="flex-1 overflow-y-auto">
                @foreach ($menu as $i => $group)
                    <div class="border-b border-ink-100">
                        <button class="flex w-full items-center gap-3 px-4 py-3 text-left text-[15px] font-semibold" @click="drawerI = drawerI === {{ $i }} ? -1 : {{ $i }}" :aria-expanded="drawerI === {{ $i }}">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-ink-50"><x-cat-icon :category="$group" /></span>
                            <span class="flex-1">{{ $group->name }}</span>
                            <x-ico name="down" size="16" class="transition" ::class="drawerI === {{ $i }} && 'rotate-180'" />
                        </button>
                        <div x-show="drawerI === {{ $i }}" x-collapse @if ($i) x-cloak @endif>
                            <div class="grid grid-cols-3 gap-2 bg-ink-50 p-3">
                                @foreach ($group->children as $child)
                                    <a href="{{ route('category', $child->slug) }}" class="flex flex-col items-center gap-1.5 rounded-lg bg-white p-2 text-center">
                                        <span class="h-12 w-12"><x-cat-icon :category="$child" /></span>
                                        <span class="text-[12px] leading-tight text-ink-700">{{ $child->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="space-y-1 p-4 text-[14px] text-ink-700">
                    <a href="{{ route('news') }}" class="block py-1.5">Tin tức &amp; khuyến mãi</a>
                    <a href="{{ route('track') }}" class="block py-1.5">Tra cứu đơn hàng</a>
                    <a href="{{ route('faq') }}" class="block py-1.5">Câu hỏi thường gặp</a>
                    <a href="{{ route('contact') }}" class="block py-1.5">Liên hệ</a>
                </div>
            </div>
        </aside>
    </div>

    <main>
        @yield('content')
    </main>

    <footer class="mt-8 bg-white">
        <div class="border-b border-ink-100">
            <div class="wrap grid gap-4 py-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['truck', 'Giao nhanh 2 giờ', 'Với hàng có sẵn tại nội thành'], ['tool', 'Lắp đặt miễn phí', 'Kỹ thuật viên của cửa hàng'], ['refresh', 'Đổi trả 30 ngày', 'Nếu lỗi do nhà sản xuất'], ['card', 'Trả góp 0%', 'Duyệt nhanh qua thẻ tín dụng']] as [$icon, $title, $text])
                    <div class="flex items-center gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600"><x-ico :name="$icon" size="22" /></span>
                        <div><p class="text-[14px] font-bold">{{ $title }}</p><p class="text-[12px] text-ink-500">{{ $text }}</p></div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="wrap grid gap-8 py-8 sm:grid-cols-2 lg:grid-cols-[1.3fr_1fr_1fr_1fr]">
            <div>
                @if ($site['logo'])<img src="{{ asset('storage/'.$site['logo']) }}" alt="{{ $siteName }}" class="h-10 w-auto max-w-[180px] object-contain">@else
                <p class="text-[20px] font-black">{{ $site['site_name'] }} <span class="text-[12px] font-semibold uppercase tracking-[.16em] text-brand-600">{{ $site['site_tagline'] }}</span></p>@endif
                <div class="mt-4 space-y-3 text-[13px]">
                    <p><span class="num block text-[20px] font-black">{{ $site['hotline_sales'] }}</span><span class="text-ink-500">Tư vấn mua hàng (8:00 – 22:00)</span></p>
                    <p><span class="num block text-[20px] font-black">{{ $site['hotline_care'] }}</span><span class="text-ink-500">Chăm sóc khách hàng (8:00 – 21:00)</span></p>
                </div>
                <form action="{{ route('newsletter') }}" method="post" class="mt-4 flex max-w-xs overflow-hidden rounded-xl border border-ink-200">
                    @csrf
                    <label for="nl" class="sr-only">Email nhận tin khuyến mãi</label>
                    <input id="nl" name="email" type="email" required placeholder="Email nhận tin khuyến mãi" class="min-w-0 flex-1 px-3 py-2 text-[13px] outline-none">
                    <button class="bg-ink-900 px-3 text-[13px] font-bold text-white hover:bg-brand-600">Đăng ký</button>
                </form>
                <div class="mt-4 flex gap-2">
                    @foreach (['facebook' => 'fb', 'youtube' => 'yt', 'tiktok' => 'tt', 'zalo' => 'zl'] as $key => $icon)
                        @if ($site[$key])
                            <a href="{{ $site[$key] }}" target="_blank" rel="noopener" class="grid h-9 w-9 place-items-center rounded-full bg-ink-50 text-ink-700 hover:bg-brand-600 hover:text-white" aria-label="{{ ucfirst($key) }}"><x-ico :name="$icon" size="16" /></a>
                        @endif
                    @endforeach
                </div>
            </div>
            @foreach (\App\Models\Page::GROUPS as $key => $label)
                <div>
                    <p class="mb-3 text-[14px] font-bold">{{ $label }}</p>
                    <ul class="space-y-2 text-[13px] text-ink-600">
                        @foreach ($footerPages[$key] ?? [] as $p)
                            <li><a href="{{ route('page', $p->slug) }}" class="hover:text-brand-600">{{ $p->title }}</a></li>
                        @endforeach
                        @if ($key === 'support')
                            <li><a href="{{ route('track') }}" class="hover:text-brand-600">Tra cứu đơn hàng</a></li>
                            <li><a href="{{ route('faq') }}" class="hover:text-brand-600">Câu hỏi thường gặp</a></li>
                            <li><a href="{{ route('contact') }}" class="hover:text-brand-600">Liên hệ – góp ý</a></li>
                        @endif
                    </ul>
                </div>
            @endforeach
        </div>
        <div class="border-t border-ink-100">
            <div class="wrap py-4 text-[12px] leading-relaxed text-ink-500">
                <p class="font-semibold text-ink-700">{{ $site['company_name'] }}</p>
                <p>{{ $site['address'] }}@if ($site['tax_code']) · MST: {{ $site['tax_code'] }}@endif · Email: {{ $site['email'] }}</p>
            </div>
        </div>
        <div class="bg-ink-900 text-[12px] text-white/60"><div class="wrap py-3">© {{ date('Y') }} {{ $siteName }}</div></div>
    </footer>

    {{-- Thanh so sánh --}}
    <div x-cloak x-show="$store.shop.compare.length" x-transition class="fixed inset-x-0 bottom-16 z-40 lg:bottom-0">
        <div class="wrap">
            <div class="flex items-center gap-3 rounded-t-2xl bg-ink-900 p-3 text-white shadow-2xl">
                <div class="flex min-w-0 flex-1 gap-2">
                    <template x-for="item in $store.shop.compare" :key="item.id">
                        <div class="relative flex min-w-0 flex-1 items-center gap-2 rounded-lg bg-white/10 p-1.5 pr-6">
                            <span class="hidden truncate text-[12px] sm:block" x-text="item.name"></span>
                            <span class="truncate text-[12px] sm:hidden" x-text="item.name.split(' ').slice(0, 3).join(' ')"></span>
                            <button class="absolute right-1 top-1 text-white/60 hover:text-white" @click="$store.shop.toggleCompare(item)" aria-label="Bỏ so sánh"><x-ico name="x" size="14" /></button>
                        </div>
                    </template>
                </div>
                <a class="shrink-0 rounded-lg bg-brand-600 px-4 py-2.5 text-[13px] font-bold" :class="$store.shop.compare.length < 2 && 'pointer-events-none opacity-40'"
                    :href="'{{ route('compare') }}?ids=' + $store.shop.compare.map(c => c.id).join(',')">So sánh (<span x-text="$store.shop.compare.length"></span>)</a>
            </div>
        </div>
    </div>

    {{-- Thanh điều hướng di động --}}
    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-ink-100 bg-white pb-[env(safe-area-inset-bottom,0px)] lg:hidden" aria-label="Điều hướng nhanh">
        <div class="grid h-16 grid-cols-5 text-[11px]">
            <a href="{{ route('home') }}" @class(['flex flex-col items-center justify-center gap-0.5', 'font-semibold text-brand-600' => request()->routeIs('home'), 'text-ink-600' => ! request()->routeIs('home')])><x-ico name="home" size="22" />Trang chủ</a>
            <button @click="drawer = true" @class(['flex flex-col items-center justify-center gap-0.5', 'font-semibold text-brand-600' => request()->routeIs('category'), 'text-ink-600' => ! request()->routeIs('category')])><x-ico name="grid" size="22" />Danh mục</button>
            <a href="{{ route('news') }}" @class(['flex flex-col items-center justify-center gap-0.5', 'font-semibold text-brand-600' => request()->routeIs('news*'), 'text-ink-600' => ! request()->routeIs('news*')])><x-ico name="news" size="22" />Tin tức</a>
            <a href="{{ route('cart') }}" @class(['flex flex-col items-center justify-center gap-0.5', 'font-semibold text-brand-600' => request()->routeIs('cart'), 'text-ink-600' => ! request()->routeIs('cart')])>
                <span class="relative"><x-ico name="cart" size="22" /><span class="num absolute -right-2 -top-1 grid h-4 min-w-[16px] place-items-center rounded-full bg-brand-600 px-1 text-[10px] font-bold text-white" x-text="$store.shop.cart">{{ $cartCount }}</span></span>Giỏ hàng</a>
            <a href="{{ auth()->check() ? route('account') : route('login') }}" @class(['flex flex-col items-center justify-center gap-0.5', 'font-semibold text-brand-600' => request()->routeIs('account*', 'login'), 'text-ink-600' => ! request()->routeIs('account*', 'login')])><x-ico name="user" size="22" />Tài khoản</a>
        </div>
    </nav>

    {{-- Thông báo --}}
    <div class="pointer-events-none fixed inset-x-0 bottom-20 z-[90] flex justify-center px-4 lg:bottom-6">
        <template x-if="$store.shop.toast">
            <div class="toast pointer-events-auto flex max-w-md items-center gap-2 rounded-xl bg-ink-900 px-4 py-3 text-[13px] text-white shadow-2xl" role="status">
                <x-ico name="check" size="18" class="shrink-0 text-green-400" /><span x-text="$store.shop.toast"></span>
            </div>
        </template>
    </div>
</div>
@stack('scripts')
</body>
</html>
