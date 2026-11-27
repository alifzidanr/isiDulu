// A small Sonner-style toast system (Sonner is what shadcn/ui uses for toasts).
//
//   toast.success('Kampus berhasil ditambahkan!')
//   toast.error('Gagal', { description: '...' })
//   toast.confirm({ title: 'Hapus kampus?', description: '...', confirmLabel: 'Hapus',
//                   destructive: true, onConfirm: () => form.submit() })
//   requireText: 'Kampus A' adds a box where that exact text must be typed before
//   the confirm button works; onConfirm then receives what was typed.
//
// Confirmations are toasts with action buttons rather than modal dialogs, so
// the page stays usable while one is open. The <x-ui.toaster /> component in
// the layout renders the stack, and flashes session messages into it on load.

const DEFAULT_DURATION = 4000;
let nextId = 1;

export function registerToasts(Alpine) {
    Alpine.store('toasts', {
        items: [],

        add(toast) {
            const id = nextId++;
            const item = { id, type: 'default', title: '', description: '', typed: '', ...toast };
            this.items.push(item);
            // Keep the stack short: drop the oldest non-confirm toast.
            if (this.items.length > 4) {
                const oldest = this.items.find((t) => t.type !== 'confirm');
                if (oldest) this.remove(oldest.id);
            }
            const duration = item.duration ?? (item.type === 'confirm' ? null : DEFAULT_DURATION);
            if (duration) setTimeout(() => this.remove(id), duration);
            return id;
        },

        remove(id) {
            this.items = this.items.filter((t) => t.id !== id);
        },

        /** True once a confirm toast's required text (if any) has been typed exactly. */
        ready(item) {
            return !item?.requireText || (item.typed ?? '').trim() === String(item.requireText).trim();
        },

        confirm(id) {
            const item = this.items.find((t) => t.id === id);
            if (!this.ready(item)) return;
            this.remove(id);
            item?.onConfirm?.(item.typed);
        },

        cancel(id) {
            const item = this.items.find((t) => t.id === id);
            this.remove(id);
            item?.onCancel?.();
        },
    });

    const store = () => Alpine.store('toasts');
    const make = (type) => (title, opts = {}) => store().add({ ...opts, type, title });

    window.toast = Object.assign(make('default'), {
        success: make('success'),
        error: make('error'),
        warning: make('warning'),
        info: make('info'),
        confirm: (opts) => {
            // Only one confirmation at a time: a second click replaces the first.
            store().items.filter((t) => t.type === 'confirm').forEach((t) => store().cancel(t.id));
            return store().add({ confirmLabel: 'Ya, lanjutkan', cancelLabel: 'Batal', ...opts, type: 'confirm' });
        },
        dismiss: (id) => store().remove(id),
    });
}
