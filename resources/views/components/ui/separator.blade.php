@props(['orientation' => 'horizontal'])
<div role="separator" {{ $attributes->merge(['class' => 'bg-border shrink-0 ' . ($orientation === 'vertical' ? 'h-full w-px' : 'h-px w-full')]) }}></div>
