{{--
    shadcn/ui Card. Compose with x-ui.card.header / title / description / content / footer.
    `flush` drops the vertical padding and gap, for cards that hold a full-width table.
--}}
@props(['flush' => false])
<div {{ $attributes->merge(['class' => 'bg-card text-card-foreground flex flex-col rounded-xl border shadow-sm ' . ($flush ? 'overflow-hidden' : 'gap-6 py-6')]) }}>
    {{ $slot }}
</div>
