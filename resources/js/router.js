import { createRouter, createWebHistory } from 'vue-router';
import SiteLayout from '@/layouts/SiteLayout.vue';
import HomePage from '@/pages/HomePage.vue';
import { can, checkAuth, isSuperAdmin } from '@/stores/auth';
import { site } from '@/stores/site';

const page = (title, component) => ({ meta: { title }, component });
const guarded = (permission, title, component) => ({ meta: { title, permission }, component });

const routes = [
    {
        path: '/',
        component: SiteLayout,
        children: [
            { path: '', name: 'home', component: HomePage },
            { path: 'notices', name: 'notices', ...page('নোটিশ', () => import('@/pages/NoticesPage.vue')) },
            { path: 'notices/:id(\\d+)', name: 'notice', ...page('নোটিশ', () => import('@/pages/NoticeShowPage.vue')) },
            { path: ':slug([a-z0-9-]+)', name: 'page', component: () => import('@/pages/DynamicPage.vue') },
            { path: ':pathMatch(.*)*', name: 'not-found', ...page('পাতা পাওয়া যায়নি', () => import('@/pages/NotFoundPage.vue')) },
        ],
    },
    {
        path: '/admin/login',
        name: 'admin.login',
        component: () => import('@/pages/admin/LoginPage.vue'),
        meta: { title: 'অ্যাডমিন লগইন' },
    },
    {
        path: '/admin',
        component: () => import('@/layouts/AdminLayout.vue'),
        meta: { requiresAdmin: true },
        children: [
            { path: '', name: 'admin.dashboard', ...page('ড্যাশবোর্ড', () => import('@/pages/admin/DashboardPage.vue')) },
            { path: 'notices', name: 'admin.notices', ...guarded('notices.manage', 'নোটিশ', () => import('@/pages/admin/NoticesPage.vue')) },
            { path: 'notices/create', name: 'admin.notices.create', ...guarded('notices.manage', 'নতুন নোটিশ', () => import('@/pages/admin/NoticeFormPage.vue')) },
            { path: 'notices/:id(\\d+)/edit', name: 'admin.notices.edit', ...guarded('notices.manage', 'নোটিশ সম্পাদনা', () => import('@/pages/admin/NoticeFormPage.vue')) },
            { path: 'notice-categories', name: 'admin.categories', ...guarded('notices.manage', 'নোটিশ ক্যাটাগরি', () => import('@/pages/admin/CategoriesPage.vue')) },
            { path: 'pages', name: 'admin.pages', ...guarded('pages.manage', 'পেজ', () => import('@/pages/admin/PagesPage.vue')) },
            { path: 'pages/create', name: 'admin.pages.create', ...guarded('pages.manage', 'নতুন পেজ', () => import('@/pages/admin/PageEditorPage.vue')) },
            { path: 'pages/:id(\d+)/edit', name: 'admin.pages.edit', ...guarded('pages.manage', 'পেজ সম্পাদনা', () => import('@/pages/admin/PageEditorPage.vue')) },
            { path: 'menus', name: 'admin.menus', ...guarded('menus.manage', 'মেনু ব্যবস্থাপনা', () => import('@/pages/admin/MenusPage.vue')) },
            { path: 'settings', name: 'admin.settings', ...guarded('settings.manage', 'সাইট সেটিংস', () => import('@/pages/admin/SettingsPage.vue')) },
            { path: 'users', name: 'admin.users', ...guarded('users.manage', 'ইউজার ব্যবস্থাপনা', () => import('@/pages/admin/UsersPage.vue')) },
            { path: 'roles', name: 'admin.roles', ...guarded('roles.manage', 'রোল ও পারমিশন', () => import('@/pages/admin/RolesPage.vue')) },
            { path: 'system', name: 'admin.system', meta: { title: 'সিস্টেম', superAdmin: true }, component: () => import('@/pages/admin/SystemPage.vue') },
            { path: 'profile', name: 'admin.profile', ...page('প্রোফাইল', () => import('@/pages/admin/ProfilePage.vue')) },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }

        if (to.hash) {
            return { el: to.hash, top: 140, behavior: 'smooth' };
        }

        if (to.path === from.path) {
            return false;
        }

        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    if (to.matched.some((record) => record.meta.requiresAdmin)) {
        const user = await checkAuth().catch(() => null);

        if (!user) {
            return { name: 'admin.login', query: { redirect: to.fullPath } };
        }

        if ((to.meta.permission && !can(to.meta.permission)) || (to.meta.superAdmin && !isSuperAdmin())) {
            return { name: 'admin.dashboard' };
        }
    }

    if (to.name === 'admin.login') {
        const user = await checkAuth().catch(() => null);

        if (user) {
            return { name: 'admin.dashboard' };
        }
    }

    return true;
});

router.afterEach((to) => {
    const siteName = site.settings.site_name || document.title;
    document.title = to.meta.title ? `${to.meta.title} | ${siteName}` : siteName;
});

export default router;
