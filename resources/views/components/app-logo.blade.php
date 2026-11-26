{{-- YPI Al Azhar logo with the app name. `compact` shows the mark only. --}}
@props(['compact' => false, 'size' => 'size-10'])
<span {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    <img src="{{ asset('images/logo.png') }}" alt="YPI Al Azhar" class="{{ $size }} shrink-0 object-contain">
    @unless ($compact)
        <span class="grid leading-tight">
            <span class="text-sm font-semibold tracking-tight">HelpDesk</span>
            <span class="text-muted-foreground text-xs">YPI Al Azhar</span>
        </span>
    @endunless
</span>
