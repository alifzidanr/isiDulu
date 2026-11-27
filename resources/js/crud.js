// Shared Alpine state for the master-data pages: a "create" dialog, an "edit"
// dialog and delete confirmations shown as toasts.
//
//   <div x-data="crud({ updateUrl: '/master/kampus/__ID__', reopen: @js(old('_form')), old: @js(session()->getOldInput()) })">
//
// Forms post a hidden `_form` input ("create" or "edit:<id>"). When validation
// fails, Laravel redirects back with that value in old(), and `reopen` uses it
// to open the same dialog again with what the user had typed.

// x-model on <select> compares strings, so ids and nulls are normalised here.
const normalise = (row = {}) =>
    Object.fromEntries(Object.entries(row).map(([k, v]) => [k, v === null || v === undefined ? '' : String(v)]));

export function registerCrud(Alpine) {
    Alpine.data('crud', ({ updateUrl = '', defaults = {}, reopen = null, old = {} } = {}) => ({
        createOpen: false,
        editOpen: false,
        form: normalise(defaults),
        editing: {},

        init() {
            if (reopen === 'create') {
                this.form = { ...normalise(defaults), ...normalise(old) };
                this.createOpen = true;
            } else if (typeof reopen === 'string' && reopen.startsWith('edit:')) {
                this.editing = { ...normalise(old), id: reopen.slice(5) };
                this.editOpen = true;
            }
        },

        openCreate() {
            this.form = normalise(defaults);
            this.createOpen = true;
        },

        openEdit(row) {
            this.editing = normalise(row);
            this.editOpen = true;
        },

        get editAction() {
            return updateUrl.replace('__ID__', this.editing.id ?? '');
        },

        confirmDelete(form, title, description, name = null) {
            window.toast.confirm({
                title,
                description: description ?? 'Data akan dihapus permanen dan tidak bisa dikembalikan.',
                confirmLabel: 'Hapus',
                destructive: true,
                // Type the item's name before Hapus is enabled (checked again on the server).
                requireText: name || null,
                onConfirm: (typed) => {
                    const field = form.querySelector('[name=confirm_name]');
                    if (field) field.value = typed ?? '';
                    form.submit();
                },
            });
        },
    }));
}
