// Search-as-you-type for server-rendered tables. The form is a normal GET
// form (it still works without JS); typing fetches the same page with the new
// query and swaps in just the results block, so the cursor stays in the box,
// then updates the address bar.
//   <form x-data="liveSearch({ target: '#user-results' })" x-on:submit.prevent="run()"> … </form>
export function registerLiveSearch(Alpine) {
    Alpine.data('liveSearch', ({ target, delay = 300 }) => ({
        q: '',
        busy: false,
        timer: null,
        controller: null,

        init() {
            this.q = this.$refs.input?.value ?? '';
        },

        schedule() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.run(), delay);
        },

        clear() {
            this.q = '';
            // Let x-model empty the input before the form is read.
            this.$nextTick(() => this.run());
            this.$refs.input?.focus();
        },

        async run() {
            clearTimeout(this.timer);
            const url = new window.URL(this.$root.action);
            const params = new URLSearchParams(new FormData(this.$root));
            for (const [key, value] of [...params]) if (value === '') params.delete(key);
            url.search = params.toString();

            this.controller?.abort();
            this.controller = new AbortController();
            this.busy = true;
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: this.controller.signal });
                const html = await res.text();
                const fresh = new DOMParser().parseFromString(html, 'text/html').querySelector(target);
                const current = document.querySelector(target);
                if (fresh && current) current.innerHTML = fresh.innerHTML;
                window.history.replaceState(null, '', url);
            } catch (e) {
                if (e.name !== 'AbortError') window.toast?.error('Gagal memuat hasil pencarian.');
            } finally {
                this.busy = false;
            }
        },
    }));
}
