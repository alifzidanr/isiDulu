{{-- shadcn/ui Textarea. --}}
@props(['invalid' => false])
<textarea {{ $attributes->merge([
    'rows' => 4,
    'aria-invalid' => $invalid ? 'true' : null,
    'class' => 'border-input placeholder:text-muted-foreground flex min-h-16 w-full rounded-md border bg-transparent px-3 py-2 text-base shadow-xs '
        . 'transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm '
        . 'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] '
        . 'aria-invalid:ring-destructive/20 aria-invalid:border-destructive',
]) }}>{{ $slot }}</textarea>
