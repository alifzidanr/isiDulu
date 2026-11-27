{{--
    Sonner-style toaster. Put it once in the layout. It shows session flashes
    (`->with('success' | 'error' | 'warning' | 'info', '...')`) and validation
    failures as toasts on page load; JavaScript calls window.toast(...) (see
    resources/js/toast.js) for everything else, including confirmations.
--}}
@php
    $flashes = collect(['success', 'error', 'warning', 'info'])
        ->filter(fn ($type) => session()->has($type))
        ->map(fn ($type) => ['type' => $type, 'title' => session($type)])
        ->values();

    if ($errors->any()) {
        $flashes->push([
            'type' => 'error',
            'title' => $errors->count() > 1 ? 'Periksa kembali isian form' : $errors->first(),
            'description' => $errors->count() > 1 ? $errors->first() : null,
        ]);
    }
@endphp

<div x-data x-init="@js($flashes).forEach((t) => window.toast[t.type](t.title, { description: t.description }))"
     class="pointer-events-none fixed right-0 bottom-0 z-[100] flex w-full flex-col items-end gap-2 p-4 sm:max-w-[420px]"
     aria-live="polite">
    <template x-for="t in $store.toasts.items" :key="t.id">
        <div class="animate-toast-in bg-popover text-popover-foreground pointer-events-auto flex w-full items-start gap-3 rounded-lg border p-4 shadow-lg"
             :class="t.type === 'confirm' && t.destructive ? 'border-destructive/30' : ''" role="status">
            <template x-if="t.type === 'success'"><i data-lucide="circle-check" class="mt-0.5 size-4 shrink-0 text-emerald-600"></i></template>
            <template x-if="t.type === 'error'"><i data-lucide="circle-x" class="text-destructive mt-0.5 size-4 shrink-0"></i></template>
            <template x-if="t.type === 'warning'"><i data-lucide="triangle-alert" class="mt-0.5 size-4 shrink-0 text-amber-500"></i></template>
            <template x-if="t.type === 'info'"><i data-lucide="info" class="text-primary mt-0.5 size-4 shrink-0"></i></template>
            <template x-if="t.type === 'confirm'">
                <i :data-lucide="t.destructive ? 'trash-2' : 'circle-check'" class="mt-0.5 size-4 shrink-0"
                   :class="t.destructive ? 'text-destructive' : 'text-primary'"></i>
            </template>

            <div class="grid min-w-0 flex-1 gap-1">
                <p class="text-sm font-medium" x-text="t.title"></p>
                <p class="text-muted-foreground text-sm" x-show="t.description" x-text="t.description"></p>

                {{-- Type-to-confirm (toast.confirm({ requireText })): e.g. the name of what's being deleted. --}}
                <template x-if="t.type === 'confirm' && t.requireText">
                    <div class="mt-2 grid gap-1.5">
                        <p class="text-muted-foreground text-xs">
                            Ketik <span class="text-foreground font-semibold break-all" x-text="t.requireText"></span> untuk konfirmasi.
                        </p>
                        <input type="text" x-model="t.typed" x-init="$nextTick(() => $el.focus())" autocomplete="off" autocapitalize="off" spellcheck="false"
                               x-on:keydown.enter.prevent="$store.toasts.confirm(t.id)" aria-label="Ketik nama untuk konfirmasi"
                               class="border-input h-8 w-full rounded-md border bg-transparent px-2.5 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                    </div>
                </template>

                <template x-if="t.type === 'confirm'">
                    <div class="mt-2 flex gap-2">
                        <button type="button" x-on:click="$store.toasts.confirm(t.id)" x-text="t.confirmLabel"
                                x-bind:disabled="!$store.toasts.ready(t)"
                                class="inline-flex h-8 cursor-pointer items-center rounded-md px-3 text-xs font-medium shadow-xs transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                                :class="t.destructive ? 'bg-destructive text-white hover:bg-destructive/90' : 'bg-primary text-primary-foreground hover:bg-primary/90'"></button>
                        <button type="button" x-on:click="$store.toasts.cancel(t.id)" x-text="t.cancelLabel"
                                class="hover:bg-accent inline-flex h-8 cursor-pointer items-center rounded-md border px-3 text-xs font-medium shadow-xs transition-colors"></button>
                    </div>
                </template>
            </div>

            <button type="button" x-on:click="t.type === 'confirm' ? $store.toasts.cancel(t.id) : $store.toasts.remove(t.id)"
                    class="text-muted-foreground hover:text-foreground -mt-1 -mr-1 cursor-pointer rounded-sm p-1 transition-colors">
                <i data-lucide="x" class="size-3.5"></i>
                <span class="sr-only">Tutup</span>
            </button>
        </div>
    </template>
</div>
