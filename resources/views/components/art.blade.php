@props(['type' => 'plug'])
<svg viewBox="0 0 120 120" {{ $attributes->merge(['class' => 'h-full w-full']) }} aria-hidden="true">{!! config('art.'.$type) ?? config('art.plug') !!}</svg>
