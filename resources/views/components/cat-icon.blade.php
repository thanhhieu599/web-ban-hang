@props(['category'])
{{-- Ảnh icon danh mục tải lên trong admin; chưa có ảnh thì dùng hình minh họa --}}
@if ($category->image)
    <img src="{{ asset('storage/'.$category->image) }}" alt="" loading="lazy" {{ $attributes->merge(['class' => 'h-full w-full object-contain']) }}>
@else
    <x-art :type="$category->icon ?: 'plug'" {{ $attributes }} />
@endif
