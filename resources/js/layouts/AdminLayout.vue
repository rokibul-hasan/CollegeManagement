<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/api';
import { auth, can, isSuperAdmin, logout } from '@/stores/auth';
import { toasts } from '@/stores/toast';

const route = useRoute();
const router = useRouter();
const sideOpen = ref(false);
const brand = ref({ name: '', logo: '' });

const nav = [
    { group: 'সাধারণ' },
    { to: '/admin', label: 'ড্যাশবোর্ড', icon: '▦', exact: true },
    { group: 'ওয়েবসাইট' },
    { to: '/admin/notices', label: 'নোটিশ', icon: '✉', permission: 'notices.manage' },
    { to: '/admin/notice-categories', label: 'নোটিশ ক্যাটাগরি', icon: '☰', permission: 'notices.manage' },
    { to: '/admin/pages', label: 'পেজ', icon: '▤', permission: 'pages.manage' },
    { to: '/admin/menus', label: 'মেনু ও ফুটার লিংক', icon: '⋮', permission: 'menus.manage' },
    { to: '/admin/settings', label: 'লোগো ও সাইট সেটিংস', icon: '⚙', permission: 'settings.manage' },
    { group: 'ব্যবস্থাপনা' },
    { to: '/admin/users', label: 'ইউজার', icon: '👥', permission: 'users.manage' },
    { to: '/admin/roles', label: 'রোল ও পারমিশন', icon: '🔑', permission: 'roles.manage' },
    { to: '/admin/system', label: 'সিস্টেম (মাইগ্রেশন/ক্যাশ)', icon: '🛠', superAdmin: true },
    { group: 'অ্যাকাউন্ট' },
    { to: '/admin/profile', label: 'প্রোফাইল ও পাসওয়ার্ড', icon: '☺' },
];

// Hide links the user cannot open, then drop group headings left with no links.
const visibleNav = computed(() => {
    const allowed = nav.filter((item) => item.group || (item.superAdmin ? isSuperAdmin() : can(item.permission)));

    return allowed.filter((item, index) => !item.group || (allowed[index + 1] && !allowed[index + 1].group));
});

const initial = computed(() => (auth.user?.name ?? 'A').slice(0, 1));

function isActive(item) {
    return item.exact ? route.path === item.to : route.path.startsWith(item.to);
}

async function signOut() {
    await logout();
    router.push({ name: 'admin.login' });
}

watch(() => route.fullPath, () => {
    sideOpen.value = false;
});

onMounted(async () => {
    const { data } = await api.get('/admin/settings');
    brand.value = { name: data.site_name, logo: data.logo_url };
});
</script>

<template>
    <div class="admin">
        <aside class="admin-side" :class="{ 'is-open': sideOpen }">
            <router-link to="/admin" class="admin-brand">
                <img v-if="brand.logo" :src="brand.logo" alt="">
                <div>
                    <div class="name">{{ brand.name || 'অ্যাডমিন' }}</div>
                    <div class="sub">ADMIN PORTAL</div>
                </div>
            </router-link>
            <nav class="admin-nav">
                <template v-for="(item, index) in visibleNav" :key="index">
                    <div v-if="item.group" class="group">{{ item.group }}</div>
                    <router-link v-else :to="item.to" :class="{ 'is-active': isActive(item) }">
                        <span class="icon">{{ item.icon }}</span>{{ item.label }}
                    </router-link>
                </template>
            </nav>
            <div class="admin-side-foot">
                <a href="/" target="_blank" rel="noopener">↗ ওয়েবসাইট দেখুন</a>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-top">
                <button type="button" class="admin-menu-btn" aria-label="মেনু" @click="sideOpen = !sideOpen">☰</button>
                <h1>{{ route.meta.title }}</h1>
                <span class="spacer" />
                <div class="admin-user">
                    <div class="avatar">{{ initial }}</div>
                    <span class="name">
                        {{ auth.user?.name }}
                        <small v-if="auth.user?.is_super_admin" class="tag tag-gold" style="margin-left: 4px">Super Admin</small>
                    </span>
                    <button type="button" class="btn btn-outline btn-sm" @click="signOut">লগআউট</button>
                </div>
            </header>
            <main class="admin-content">
                <router-view />
            </main>
        </div>

        <div class="toast-stack">
            <div v-for="item in toasts" :key="item.id" class="toast" :class="item.type">{{ item.message }}</div>
        </div>
    </div>
</template>
