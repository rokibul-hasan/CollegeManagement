import { reactive } from 'vue';
import api from '@/api';

const DEFAULT_LOGO = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR-JJJnOXZkedUfTX2Q2QRN8sJuMWdGbsEPKlT5TRpBbXmeSMBEB8lqckk0&s=10';

export const site = reactive({
    loaded: false,
    failed: false,
    settings: {},
    menus: { main: [], footer: [], quick: [] },
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
