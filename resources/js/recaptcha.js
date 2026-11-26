// Google reCAPTCHA v3 on a form: fetches a token for `action` just before
// submitting and puts it in the form's x-ref="recaptcha" input. Without a
// site key (reCAPTCHA off) the form submits as usual.
// <form x-data="recaptchaForm('site-key', 'login')" x-on:submit.prevent="submit($el)">

function token(siteKey, action) {
    return new Promise((resolve, reject) => {
        const started = Date.now();
        (function wait() {
            if (window.grecaptcha?.execute) {
                window.grecaptcha.ready(() => window.grecaptcha.execute(siteKey, { action }).then(resolve, reject));
            } else if (Date.now() - started > 10000) {
                reject(new Error('reCAPTCHA tidak termuat.'));
            } else {
                setTimeout(wait, 100);
            }
        })();
    });
}

export function registerRecaptcha(Alpine) {
    Alpine.data('recaptchaForm', (siteKey, action = 'submit') => ({
        sending: false,

        async submit(form) {
            if (this.sending) return;
            this.sending = true;
            if (siteKey) {
                try {
                    this.$refs.recaptcha.value = await token(siteKey, action);
                } catch {
                    this.sending = false;
                    window.toast.error('Verifikasi keamanan (reCAPTCHA) gagal dimuat. Periksa koneksi lalu coba lagi.');
                    return;
                }
            }
            form.submit();
        },
    }));
}
