import './bootstrap';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import { renderIcons } from './icons';
import { registerToasts } from './toast';
import { registerCrud } from './crud';
import { registerLivePermohonan } from './live-permohonan';
import { registerPasskey } from './passkey';
import { registerRecaptcha } from './recaptcha';
import { registerLiveSearch } from './live-search';

window.Alpine = Alpine;
window.renderIcons = renderIcons;

Alpine.plugin(focus);
registerToasts(Alpine);
registerCrud(Alpine);
registerLivePermohonan(Alpine);
registerPasskey(Alpine);
registerRecaptcha(Alpine);
registerLiveSearch(Alpine);

// Alpine re-renders parts of the page (x-if, x-for, toasts), so icons are
// re-rendered after every DOM change instead of once on load.
document.addEventListener('alpine:initialized', () => {
    renderIcons();
    new MutationObserver(() => renderIcons()).observe(document.body, { childList: true, subtree: true });
});

Alpine.start();
