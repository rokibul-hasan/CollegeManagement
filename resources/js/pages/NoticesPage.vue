<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageHero from '@/components/site/PageHero.vue';
import NoticeRow from '@/components/site/NoticeRow.vue';
import api from '@/api';
import { site } from '@/stores/site';
import { bnDigits } from '@/utils/bn';

const route = useRoute();
const router = useRouter();

const notices = ref([]);
const meta = ref({ current_page: 1, last_page: 1 });
const loading = ref(false);
const search = ref(route.query.q ?? '');

const activeCategory = computed(() => site.noticeCategories.find((category) => category.slug === route.query.category));

async function fetchNotices() {
    loading.value = true;

    try {
        const { data } = await api.get('/notices', {
            params: { category: route.query.category, q: route.query.q, page: route.query.page },
        });
        notices.value = data.data;
        meta.value = data;
    } finally {
        loading.value = false;
    }
}

function setQuery(changes) {
    router.push({ query: { ...route.query, page: undefined, ...changes } });
}

watch(() => route.query, fetchNotices, { immediate: true });
</script>

<template>
    <PageHero
        :title="activeCategory?.name ?? 'নোটিশ বোর্ড'"
        lead="প্রতিষ্ঠানের সকল বিজ্ঞপ্তি, রুটিন, ফলাফল ও প্রশাসনিক নোটিশ।"
        :trail="activeCategory ? [{ label: 'নোটিশ', to: '/notices' }] : []"
    />

    <div class="container page-body">
        <div class="cat-tabs">
            <button type="button" class="cat-tab" :class="{ 'is-active': !route.query.category }" @click="setQuery({ category: undefined })">
                সকল নোটিশ <span class="count">{{ bnDigits(site.noticeTotal) }}</span>
            </button>
            <button
                v-for="category in site.noticeCategories"
                :key="category.id"
                type="button"
                class="cat-tab"
                :class="{ 'is-active': route.query.category === category.slug }"
                @click="setQuery({ category: category.slug })"
            >
                {{ category.name }} <span class="count">{{ bnDigits(category.notices_count) }}</span>
            </button>
        </div>

        <form class="notice-toolbar" @submit.prevent="setQuery({ q: search || undefined })">
            <input v-model.trim="search" type="search" class="input" placeholder="নোটিশ খুঁজুন…">
            <button type="submit" class="btn btn-primary">খুঁজুন</button>
        </form>

        <div class="notice-list" :style="{ opacity: loading ? 0.55 : 1 }">
            <NoticeRow v-for="notice in notices" :key="notice.id" :notice="notice" />
            <div v-if="!loading && !notices.length" class="empty-state">কোনো নোটিশ পাওয়া যায়নি।</div>
        </div>

        <div v-if="meta.last_page > 1" class="pager">
            <button type="button" :disabled="meta.current_page === 1" @click="setQuery({ page: meta.current_page - 1 })">‹</button>
            <button
                v-for="page in meta.last_page"
                :key="page"
                type="button"
                :class="{ 'is-active': page === meta.current_page }"
                @click="setQuery({ page })"
            >
                {{ bnDigits(page) }}
            </button>
            <button type="button" :disabled="meta.current_page === meta.last_page" @click="setQuery({ page: meta.current_page + 1 })">›</button>
        </div>
    </div>
</template>
