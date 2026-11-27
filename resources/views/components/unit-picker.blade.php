{{--
    Searchable Unit / Sub Unit picker for the permohonan forms. Lists every unit
    with its kampus, and each unit's sub units indented beneath it. Picking a
    sub unit fills both id_unit (its unit) and id_sub_unit.
    <x-unit-picker :units="$units" :unit="old('id_unit')" :sub-unit="old('id_sub_unit')" :invalid="$errors->has('id_unit')" />
    $units: Unit models with `kampus` and `subUnits` loaded.
--}}
@props(['units', 'unit' => null, 'subUnit' => null, 'invalid' => false, 'id' => 'unit-picker'])
@php
    $items = [];
    foreach ($units->sortBy(fn ($u) => [($u->kampus->nama_kampus ?? ''), $u->nama_unit]) as $u) {
        $kampus = $u->kampus->nama_kampus ?? '-';
        $items[] = ['key' => 'u' . $u->id_unit, 'unit' => $u->id_unit, 'sub' => null, 'label' => $u->nama_unit, 'detail' => $kampus, 'search' => mb_strtolower($u->nama_unit . ' ' . $kampus)];
        foreach ($u->subUnits->sortBy('nama_sub_unit') as $s) {
            $items[] = ['key' => 's' . $s->id_sub_unit, 'unit' => $u->id_unit, 'sub' => $s->id_sub_unit, 'label' => $s->nama_sub_unit, 'detail' => $u->nama_unit . ' · ' . $kampus, 'search' => mb_strtolower($s->nama_sub_unit . ' ' . $u->nama_unit . ' ' . $kampus)];
        }
    }
    $selectedKey = $subUnit ? 's' . $subUnit : ($unit ? 'u' . $unit : null);
@endphp
<div x-data="{
        open: false,
        query: '',
        items: {{ Js::from($items) }},
        selected: {{ Js::from($selectedKey) }},
        active: 0,
        style: '',
        get current() { return this.items.find((i) => i.key === this.selected) || null },
        get filtered() {
            const words = this.query.toLowerCase().trim().split(/\s+/).filter(Boolean);
            return words.length ? this.items.filter((i) => words.every((w) => i.search.includes(w))) : this.items;
        },
        toggle() { this.open ? this.close() : this.show() },
        show() {
            // Fixed to the viewport, so a dialog's overflow can't clip it.
            const r = this.$refs.trigger.getBoundingClientRect();
            const below = window.innerHeight - r.bottom >= 340 || r.top < 340;
            const y = below ? `top:${r.bottom + 4}px` : `bottom:${window.innerHeight - r.top + 4}px`;
            this.style = `${y};left:${r.left}px;width:${r.width}px`;
            this.open = true;
            this.query = '';
            this.active = Math.max(0, this.filtered.findIndex((i) => i.key === this.selected));
            this.$nextTick(() => { this.$refs.search.focus(); this.scrollActive() });
        },
        close() { this.open = false },
        choose(item) { this.selected = item.key; this.close(); this.$refs.trigger.focus() },
        move(step) {
            if (!this.filtered.length) return;
            this.active = (this.active + step + this.filtered.length) % this.filtered.length;
            this.$nextTick(() => this.scrollActive());
        },
        scrollActive() { this.$refs.list?.querySelector('[data-active=true]')?.scrollIntoView({ block: 'nearest' }) },
     }"
     x-on:click.outside="if (!$refs.panel?.contains($event.target)) close()"
     x-on:resize.window="close()" x-on:scroll.window="close()"
     {{ $attributes->merge(['class' => 'relative']) }}>
    <input type="hidden" name="id_unit" x-bind:value="current ? current.unit : ''">
    <input type="hidden" name="id_sub_unit" x-bind:value="current && current.sub ? current.sub : ''">

    <button type="button" id="{{ $id }}" x-ref="trigger" x-on:click="toggle()" aria-haspopup="listbox" x-bind:aria-expanded="open"
            @if ($invalid) aria-invalid="true" @endif
            class="border-input flex min-h-9 w-full items-center justify-between gap-2 rounded-md border bg-transparent px-3 py-1.5 text-left text-base shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 md:text-sm">
        <span class="min-w-0 truncate" x-show="current">
            <span class="font-medium" x-text="current?.label"></span>
            <span class="text-muted-foreground" x-text="current ? ' — ' + current.detail : ''"></span>
        </span>
        <span class="text-muted-foreground truncate" x-show="!current">Pilih unit atau sub unit</span>
        <x-ui.icon name="chevrons-up-down" class="text-muted-foreground size-4 shrink-0 opacity-60" />
    </button>

    <div x-show="open" x-cloak x-ref="panel" x-bind:style="style" x-transition.opacity.duration.100ms
         x-on:keydown.escape.prevent.stop="close(); $refs.trigger.focus()"
         class="bg-popover text-popover-foreground fixed z-[60] overflow-hidden rounded-md border shadow-md">
        <div class="border-b p-2">
            <div class="relative">
                <x-ui.icon name="search" class="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                <input type="search" x-ref="search" x-model="query" x-on:input="active = 0"
                       x-on:keydown.arrow-down.prevent="move(1)" x-on:keydown.arrow-up.prevent="move(-1)"
                       x-on:keydown.enter.prevent="filtered[active] && choose(filtered[active])"
                       placeholder="Cari unit, sub unit atau kampus…" autocomplete="off" aria-label="Cari unit"
                       class="border-input h-9 w-full rounded-md border bg-transparent pr-3 pl-8 text-sm outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
            </div>
        </div>
        <ul x-ref="list" role="listbox" class="max-h-72 overflow-y-auto overscroll-contain p-1">
            <template x-for="(item, index) in filtered" x-bind:key="item.key">
                <li role="option" x-bind:aria-selected="item.key === selected" x-bind:data-active="index === active"
                    x-on:click="choose(item)" x-on:mousemove="active = index"
                    x-bind:class="{ 'bg-accent text-accent-foreground': index === active, 'pl-7': item.sub }"
                    class="flex cursor-pointer items-start gap-2 rounded-sm px-2 py-1.5 text-sm">
                    {{-- Lucide swaps the <i> for an <svg>, so x-show sits on a wrapper. --}}
                    <span class="mt-0.5 size-4 shrink-0">
                        <span x-show="item.key === selected" class="contents"><x-ui.icon name="check" class="size-4" /></span>
                    </span>
                    <span class="min-w-0">
                        <span class="block" x-bind:class="item.sub ? '' : 'font-medium'" x-text="item.label"></span>
                        <span class="text-muted-foreground block text-xs" x-text="item.detail"></span>
                    </span>
                </li>
            </template>
            <li x-show="!filtered.length" class="text-muted-foreground px-2 py-6 text-center text-sm">Tidak ada unit yang cocok.</li>
        </ul>
    </div>
</div>
