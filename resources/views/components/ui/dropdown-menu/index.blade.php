{{--
    shadcn/ui DropdownMenu, driven by Alpine.
    <x-ui.dropdown-menu align="end">
        <x-slot:trigger><x-ui.button variant="ghost" size="icon-sm" icon="ellipsis" /></x-slot:trigger>
        <x-ui.dropdown-menu.label>Aksi</x-ui.dropdown-menu.label>
        <x-ui.dropdown-menu.item icon="pencil" x-on:click="...">Edit</x-ui.dropdown-menu.item>
    </x-ui.dropdown-menu>
    The panel is positioned fixed under the trigger, so it is not clipped by
    scrolling table containers.
--}}
@props(['align' => 'end', 'width' => 'w-48', 'trigger'])

<div x-data="{
        open: false,
        style: '',
        toggle() { this.open ? this.close() : this.show() },
        show() {
            const r = this.$refs.trigger.getBoundingClientRect();
            const below = window.innerHeight - r.bottom > 260;
            const y = below ? `top:${r.bottom + 4}px` : `bottom:${window.innerHeight - r.top + 4}px`;
            const x = '{{ $align }}' === 'end' ? `right:${window.innerWidth - r.right}px` : `left:${r.left}px`;
            this.style = `${y};${x}`;
            this.open = true;
        },
        close() { this.open = false },
     }"
     x-on:keydown.escape.window="close()" x-on:scroll.window="close()" x-on:resize.window="close()"
     {{ $attributes->merge(['class' => 'relative inline-block']) }}>
    <div x-ref="trigger" x-on:click="toggle()">{{ $trigger }}</div>

    <template x-teleport="body">
        <div x-show="open" x-cloak x-on:click.outside="close()" x-bind:style="style"
             x-transition:enter="ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="bg-popover text-popover-foreground fixed z-50 {{ $width }} overflow-hidden rounded-md border p-1 shadow-md"
             x-on:click="if ($event.target.closest('[data-menu-item]')) close()">
            {{ $slot }}
        </div>
    </template>
</div>
