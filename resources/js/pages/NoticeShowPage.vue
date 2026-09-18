<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import PageHero from '@/components/site/PageHero.vue';
import PageSidebar from '@/components/site/PageSidebar.vue';
import api from '@/api';
import { bnDate } from '@/utils/bn';

const route = useRoute();
const notice = ref(null);
const missing = ref(false);

const extension = computed(() => notice.value?.attachment?.split('.').pop() ?? '');

watch(
    () => route.params.id,
    async (id) => {
        notice.value = null;
        missing.value = false;

        try {
            const { data } = await api.get(`/notices/${id}`);
            notice.value = data;
            document.title = data.title;
        } catch {
            missing.value = true;
        }
    },
    { immediate: true },
);
</script>

<template>
    <PageHero title="নোটিশ বিস্তারিত" :trail="[{ label: 'নোটিশ', to: '/notices' }]" />

    <div class="container page-body">
        <div class="two-col">
            <div class="card notice-detail" style="padding: 30px">
                <template v-if="notice">
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; font-size: 14px; color: var(--muted)">
                        <span class="mono">{{ bnDate(notice.published_on) }}</span>
                        <router-link v-if="notice.category" :to="`/notices?category=${notice.category.slug}`" class="tag">
                            {{ notice.category.name }}
                        </router-link>
                    </div>
                    <h1>{{ notice.title }}</h1>
                    <div v-if="notice.body" class="body">{{ notice.body }}</div>
                    <div v-if="notice.attachment_url" class="attachment">
                        <div class="ext">{{ extension }}</div>
                        <div style="flex: 1; min-width: 0">
                            <div style="font-weight: 600">সংযুক্ত ফাইল</div>
                            <div style="font-size: 13.5px; color: var(--muted)">ডাউনলোড করে বিস্তারিত দেখুন</div>
                        </div>
                        <a :href="notice.attachment_url" target="_blank" rel="noopener" class="btn btn-primary">ডাউনলোড</a>
                    </div>
                    <router-link to="/notices" style="display: inline-block; margin-top: 24px; font-weight: 600">← সকল নোটিশ</router-link>
                </template>
                <div v-else-if="missing" class="empty-state">
                    নোটিশটি পাওয়া যায়নি।
                    <div style="margin-top: 14px"><router-link to="/notices" class="btn btn-primary">সকল নোটিশ</router-link></div>
                </div>
                <div v-else class="empty-state">লোড হচ্ছে…</div>
            </div>

            <PageSidebar />
        </div>
    </div>
</template>
