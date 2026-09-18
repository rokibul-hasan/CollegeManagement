<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '@/api';
import { can } from '@/stores/auth';
import { bnDate, bnDigits } from '@/utils/bn';

const data = ref(null);

const shortcuts = [
    { to: '/admin/notices/create', label: 'নতুন নোটিশ প্রকাশ', sub: 'নোটিশ বোর্ড ও পপআপে দেখাবে', permission: 'notices.manage' },
    { to: '/admin/menus', label: 'মেনু সম্পাদনা', sub: 'প্রধান মেনু, ফুটার ও দ্রুত লিংক', permission: 'menus.manage' },
    { to: '/admin/settings', label: 'লোগো ও তথ্য পরিবর্তন', sub: 'নাম, ঠিকানা, ফোন, ফুটার', permission: 'settings.manage' },
];

const visibleShortcuts = computed(() => shortcuts.filter((shortcut) => can(shortcut.permission)));

onMounted(async () => {
    data.value = (await api.get('/admin/dashboard')).data;
});
</script>

<template>
    <template v-if="data">
        <div class="stat-grid">
            <div class="stat accent"><small>মোট নোটিশ</small><b>{{ bnDigits(data.stats.notices) }}</b></div>
            <div class="stat"><small>প্রকাশিত</small><b>{{ bnDigits(data.stats.published) }}</b></div>
            <div class="stat"><small>পপআপে দেখানো</small><b>{{ bnDigits(data.stats.popup) }}</b></div>
            <div class="stat"><small>ক্যাটাগরি</small><b>{{ bnDigits(data.stats.categories) }}</b></div>
            <div class="stat"><small>মেনু আইটেম</small><b>{{ bnDigits(data.stats.menus) }}</b></div>
        </div>

        <div class="grid grid-auto-210">
            <router-link v-for="shortcut in visibleShortcuts" :key="shortcut.to" :to="shortcut.to" class="service-card" style="background: #fff">
                <div class="title">{{ shortcut.label }} →</div>
                <div class="sub">{{ shortcut.sub }}</div>
            </router-link>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>সাম্প্রতিক নোটিশ</h2>
                <span class="spacer" />
                <router-link v-if="can('notices.manage')" to="/admin/notices" class="more-link">সব দেখুন →</router-link>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>শিরোনাম</th><th>ক্যাটাগরি</th><th>তারিখ</th><th>অবস্থা</th></tr></thead>
                    <tbody>
                        <tr v-for="notice in data.recentNotices" :key="notice.id">
                            <td>{{ notice.title }}</td>
                            <td>{{ notice.category?.name ?? '—' }}</td>
                            <td style="white-space: nowrap">{{ bnDate(notice.published_on) }}</td>
                            <td>
                                <span v-if="notice.is_published" class="tag">প্রকাশিত</span>
                                <span v-else class="tag tag-red">খসড়া</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </template>
    <div v-else class="empty-state">লোড হচ্ছে…</div>
</template>
