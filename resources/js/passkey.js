// Fingerprint / Face ID with the YPIA-wide passkeys, registered in Kehadiran
// or HRIS for al-azhar.id (see app/Support/KehadiranPasskeys.php).

const b64urlToBuffer = (value) => {
    const base64 = value.replace(/-/g, '+').replace(/_/g, '/').padEnd(Math.ceil(value.length / 4) * 4, '=');
    return Uint8Array.from(atob(base64), (c) => c.charCodeAt(0)).buffer;
};

const bufferToB64url = (buffer) =>
    btoa(String.fromCharCode(...new Uint8Array(buffer))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

async function post(url, body = {}) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan. Silakan coba lagi.');
    return data;
}

export const passkeySupported = () => typeof window.PublicKeyCredential === 'function';

function explain(error) {
    if (error?.name === 'NotAllowedError') return 'Verifikasi dibatalkan atau waktu habis.';
    if (error?.name === 'SecurityError') {
        return 'Passkey hanya bisa dipakai di alamat layanan-ti.al-azhar.id.';
    }
    return error?.message || 'Verifikasi passkey gagal.';
}

/** Runs the WebAuthn ceremony and returns the assertion as JSON for the server. */
async function getAssertion(optionsUrl) {
    const options = await post(optionsUrl);
    const credential = await navigator.credentials.get({
        publicKey: {
            ...options,
            challenge: b64urlToBuffer(options.challenge),
            allowCredentials: (options.allowCredentials || []).map((c) => ({ ...c, id: b64urlToBuffer(c.id) })),
        },
    });

    const r = credential.response;
    return JSON.stringify({
        id: credential.id,
        rawId: bufferToB64url(credential.rawId),
        type: credential.type,
        response: {
            clientDataJSON: bufferToB64url(r.clientDataJSON),
            authenticatorData: bufferToB64url(r.authenticatorData),
            signature: bufferToB64url(r.signature),
            userHandle: r.userHandle ? bufferToB64url(r.userHandle) : null,
        },
    });
}

export function registerPasskey(Alpine) {
    // <div x-data="passkey({ options: '...', verify: '...', mode: 'login' | 'confirm' })">
    Alpine.data('passkey', ({ options, verify, mode = 'login' }) => ({
        supported: passkeySupported(),
        busy: false,
        confirmed: false,

        async run() {
            if (this.busy) return;
            this.busy = true;
            try {
                const credential = await getAssertion(options);
                const result = await post(verify, { credential });
                if (mode === 'login') {
                    window.toast.success('Berhasil masuk.');
                    window.location.assign(result.redirect);
                    return;
                }
                this.confirmed = true;
                window.toast.success('Terverifikasi dengan fingerprint/Face ID.');
            } catch (e) {
                window.toast.error(explain(e));
            } finally {
                this.busy = false;
            }
        },
    }));
}
