import { reactive } from 'vue';
import api from '@/api';

// Shown until a logo is uploaded in the admin; inline so the site never depends on a remote image.
const DEFAULT_LOGO = 'data:image/svg+xml;utf8,'.concat(encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
    + '<circle cx="32" cy="32" r="32" fill="#12355b"/>'
    + '<path d="M32 18 12 27l20 9 20-9-20-9Zm-12 15v9c0 3.3 5.4 6 12 6s12-2.7 12-6v-9l-12 5.4L20 33Z" fill="#e9c877"/>'
    + '</svg>',
));

export const site = reactive({
    loaded: false,
    failed: false,
    settings: {},
    menus: { main: [], topbar: [], header: [], footer: [], quick: [] },
    noticeCategories: [],
    notices: [],
    noticeTotal: 0,

    get logo() {
        return this.settings.logo_url || DEFAULT_LOGO;
    },

    get popupNotices() {
        return this.notices.filter((notice) => notice.show_in_popup);
    },
});

let pending = null;

/**
 * Load the public site payload once; pass force to refetch after admin edits.
 */
export function loadSite(force = false) {
    if (site.loaded && !force) {
        return Promise.resolve(site);
    }

    pending ??= api
        .get('/site')
        .then(({ data }) => {
            Object.assign(site, data, { loaded: true, failed: false });

            return site;
        })
        .catch(() => {
            site.failed = true;
        })
        .finally(() => {
            pending = null;
        });

    return pending;
}

/**
 * Mark the cached payload stale so the public site refetches on next visit.
 */
export function invalidateSite() {
    site.loaded = false;
}
