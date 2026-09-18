<script setup>
import { computed, onMounted, provide, ref } from 'vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import NoticePopup from '@/components/site/NoticePopup.vue';
import { loadSite, site } from '@/stores/site';

const popup = ref(null);

provide('openNoticePopup', () => popup.value?.show());

const tickerText = computed(() => site.settings.ticker_text || site.notices[0]?.title || '');

onMounted(() => loadSite());
</script>

<template>
    <div v-if="site.loaded" style="background: var(--bg); color: var(--ink); min-height: 100vh">
        <NoticePopup ref="popup" />

        <div v-if="site.settings.show_top_bar === '1'" class="topbar">
            <div class="container">
                <span v-if="site.settings.eiin">ইআইআইএন : {{ site.settings.eiin }}</span>
                <template v-if="site.settings.phone">
                    <span class="sep">|</span>
                    <a :href="`tel:${site.settings.phone}`">{{ site.settings.phone }}</a>
                </template>
                <template v-if="site.settings.email">
                    <span class="sep hide-sm">|</span>
                    <a :href="`mailto:${site.settings.email}`" class="hide-sm">{{ site.settings.email }}</a>
                </template>
                <span class="spacer" />
                <router-link to="/admission-fee">অনলাইন ভর্তি</router-link>
                <router-link to="/student-login">লগইন</router-link>
            </div>
        </div>

        <SiteHeader />

        <div v-if="tickerText" class="ticker">
            <div class="container">
                <span class="ticker-label">সর্বশেষ</span>
                <div class="ticker-text">{{ tickerText }}</div>
                <router-link to="/notices" class="ticker-more">দেখুন →</router-link>
            </div>
        </div>

        <main>
            <router-view />
        </main>

        <SiteFooter />
    </div>
    <div v-else class="loading-screen">
        <span v-if="site.failed">সাইট লোড করা যায়নি। পাতাটি রিফ্রেশ করুন।</span>
        <span v-else>লোড হচ্ছে…</span>
    </div>
</template>
