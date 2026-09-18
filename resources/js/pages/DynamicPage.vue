<script setup>
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import PageHero from '@/components/site/PageHero.vue';
import PageSidebar from '@/components/site/PageSidebar.vue';
import PageSections from '@/components/page-sections/PageSections.vue';
import NotFoundPage from './NotFoundPage.vue';
import api from '@/api';
import { site } from '@/stores/site';

const route = useRoute();
const page = ref(null);
const missing = ref(false);

watch(
    () => [route.params.slug, route.query.preview],
    async ([slug, preview]) => {
        missing.value = false;

        try {
            const { data } = await api.get(`/pages/${slug}`, { params: { preview: preview ? 1 : undefined } });
            page.value = data;
            document.title = `${data.title} | ${site.settings.site_name}`;
        } catch {
            page.value = null;
            missing.value = true;
        }
    },
    { immediate: true },
);
</script>

<template>
    <NotFoundPage v-if="missing" />
    <template v-else-if="page">
        <div v-if="!page.is_published" class="container" style="padding-top: 14px">
            <div class="form-message">এটি প্রিভিউ — পেজটি এখনো প্রকাশিত হয়নি।</div>
        </div>
        <PageHero :title="page.title" :lead="page.lead || ''" />
        <div class="container page-body">
            <div v-if="page.layout === 'sidebar'" class="two-col">
                <PageSections :sections="page.sections" />
                <PageSidebar />
            </div>
            <PageSections v-else :sections="page.sections" />
        </div>
    </template>
    <div v-else class="loading-screen">লোড হচ্ছে…</div>
</template>
