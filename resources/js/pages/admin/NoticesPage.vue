<script setup>
import { onMounted, reactive, ref } from 'vue';
import api, { errorMessage } from '@/api';
import { invalidateSite } from '@/stores/site';
import { toast } from '@/stores/toast';
import { bnDate, bnDigits } from '@/utils/bn';

const notices = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const categories = ref([]);
const filters = reactive({ q: '', category: '', page: 1 });
const loading = ref(false);

async function fetchNotices() {
    loading.value = true;

    try {
        const { data } = await api.get('/admin/notices', { params: filters });
        notices.value = data.data;
        meta.value = data;
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    filters.page = 1;
    fetchNotices();
}

function goToPage(page) {
    filters.page = page;
    fetchNotices();
}

async function remove(notice) {
    if (!window.confirm(`"${notice.title}" নোটিশটি মুছে ফেলবেন?`)) {
        return;
    }

    try {
        await api.delete(`/admin/notices/${notice.id}`);
        invalidateSite();
        toast('নোটিশ মুছে ফেলা হয়েছে।');
        fetchNotices();
    } catch (error) {
        toast(errorMessage(error), 'error');
    }
}

onMounted(async () => {
    categories.value = (await api.get('/admin/notice-categories')).data;
    fetchNotices();
});
</script>

<template>
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>সকল নোটিশ</h2>
                <p>মোট {{ bnDigits(meta.total) }}টি নোটিশ</p>
            </div>
            <span class="spacer" />
            <router-link to="/admin/notices/create" class="btn btn-primary">+ নতুন নোটিশ</router-link>
        </div>
        <form class="panel-body" style="display: flex; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid var(--line-soft)" @submit.prevent="applyFilters">
            <input v-model.trim="filters.q" type="search" class="input" placeholder="শিরোনাম দিয়ে খুঁজুন…" style="max-width: 320px; padding: 10px 12px">
            <select v-model="filters.category" class="input" style="max-width: 220px; padding: 10px 12px" @change="applyFilters">
                <option value="">সকল ক্যাটাগরি</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
            </select>
            <button type="submit" class="btn btn-soft">খুঁজুন</button>
        </form>
        <div class="table-wrap" :style="{ opacity: loading ? 0.55 : 1 }">
            <table class="table">
                <thead>
                    <tr><th>শিরোনাম</th><th>ক্যাটাগরি</th><th>প্রকাশের তারিখ</th><th>অবস্থা</th><th /></tr>
                </thead>
                <tbody>
                    <tr v-for="notice in notices" :key="notice.id">
                        <td style="min-width: 260px">
                            <div style="font-weight: 600">{{ notice.title }}</div>
                            <div v-if="notice.attachment" style="font-size: 12.5px; color: var(--muted)">📎 সংযুক্তি আছে</div>
                        </td>
                        <td>{{ notice.category?.name ?? '—' }}</td>
                        <td style="white-space: nowrap">{{ bnDate(notice.published_on) }}</td>
                        <td>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px">
                                <span v-if="notice.is_published" class="tag">প্রকাশিত</span>
                                <span v-else class="tag tag-red">খসড়া</span>
                                <span v-if="notice.show_in_popup" class="tag tag-gold">পপআপ</span>
                                <span v-if="notice.is_pinned" class="tag tag-gold">পিন</span>
                            </div>
                        </td>
                        <td>
                            <div class="actions">
                                <router-link :to="`/admin/notices/${notice.id}/edit`" class="btn btn-soft btn-sm">সম্পাদনা</router-link>
                                <button type="button" class="btn btn-danger btn-sm" @click="remove(notice)">মুছুন</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!loading && !notices.length">
                        <td colspan="5" class="empty-state">কোনো নোটিশ পাওয়া যায়নি।</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="meta.last_page > 1" class="pager" style="padding: 0 0 18px">
            <button v-for="page in meta.last_page" :key="page" type="button" :class="{ 'is-active': page === meta.current_page }" @click="goToPage(page)">
                {{ bnDigits(page) }}
            </button>
        </div>
    </div>
</template>
