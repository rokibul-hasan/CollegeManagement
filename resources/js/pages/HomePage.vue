<script setup>
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import HomeSections from '@/components/site/HomeSections.vue';
import api from '@/api';

// Last response per template, so returning to the home page renders instantly.
const cache = new Map();

const route = useRoute();
const home = ref(null);
const failed = ref(false);

watch(
    () => route.query.template,
    async (template) => {
        const key = template || 'default';
        failed.value = false;
        home.value = cache.get(key) ?? home.value;

        try {
            const { data } = await api.get('/home', { params: { template: template || undefined } });
            cache.set(key, data);
            home.value = data;
        } catch {
            failed.value = !home.value;
        }
    },
    { immediate: true },
);
</script>

<template>
    <HomeSections v-if="home" :sections="home.sections" />
    <div v-else class="loading-screen">
        <span v-if="failed">হোমপেজ লোড করা যায়নি। পাতাটি রিফ্রেশ করুন।</span>
        <span v-else>লোড হচ্ছে…</span>
    </div>
</template>
