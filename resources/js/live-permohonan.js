// Live updates for the public permohonan list: polls the latest endpoint every
// 3 seconds (first page only, paused while the tab is hidden) and prepends new
// rows. Text is set with textContent, never innerHTML, because it comes from
// the public form.
import { statusBadge } from './permohonan-status';

const POLL_MS = 3000;

export function registerLivePermohonan(Alpine) {
    Alpine.data('livePermohonan', ({ endpoint, latestId = 0, enabled = true }) => ({
        latestId,
        state: enabled ? 'live' : 'paused', // live | error | paused
        timer: null,

        init() {
            if (!enabled) return;
            this.start();
            document.addEventListener('visibilitychange', () => (document.hidden ? this.stop() : this.start(true)));
        },

        start(immediately = false) {
            this.stop();
            if (immediately) this.poll();
            this.timer = setInterval(() => this.poll(), POLL_MS);
        },

        stop() {
            clearInterval(this.timer);
        },

        async poll() {
            try {
                const res = await fetch(`${endpoint}?last_id=${this.latestId}`, { headers: { Accept: 'application/json' } });
                if (!res.ok) throw new Error(res.statusText);
                const data = await res.json();
                this.state = 'live';
                this.latestId = Math.max(this.latestId, data.latest_id ?? 0);
                const added = [...data.permohonans].reverse().filter((p) => this.prepend(p)).length;
                if (added > 0) {
                    window.toast.info(added === 1 ? 'Ada 1 permohonan baru' : `Ada ${added} permohonan baru`);
                }
            } catch (e) {
                this.state = 'error';
            }
        },

        prepend(p) {
            const tbody = this.$refs.tbody;
            if (tbody.querySelector(`tr[data-id="${p.id_permohonan}"]`)) return false;
            this.$refs.empty?.remove();

            const cell = (cls) => Object.assign(document.createElement('td'), { className: `p-4 align-middle first:pl-6 last:pr-6 ${cls}` });
            const text = (tag, cls, value) => Object.assign(document.createElement(tag), { className: cls, textContent: value ?? '' });

            const row = document.createElement('tr');
            row.dataset.id = p.id_permohonan;
            row.className = 'animate-row-in bg-primary/5 hover:bg-muted/50 border-b transition-colors duration-1000';

            const who = cell('');
            const wrap = text('div', 'grid', '');
            wrap.append(
                text('span', 'font-medium', p.nama_pemohon),
                text('span', 'text-muted-foreground text-xs', `${p.unit?.nama_unit ?? '-'} · ${new Date(p.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' })}`),
            );
            who.append(wrap);

            const what = cell('text-muted-foreground');
            what.textContent = p.keluhan.length > 100 ? `${p.keluhan.slice(0, 100)}...` : p.keluhan;

            const status = cell('');
            status.append(statusBadge(p.status_permohonan));

            row.append(who, what, status);
            tbody.prepend(row);
            setTimeout(() => row.classList.remove('bg-primary/5'), 2000);
            return true;
        },
    }));
}
