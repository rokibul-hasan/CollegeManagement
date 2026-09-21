<script setup>
import { computed, onMounted, provide, ref } from 'vue';
import { useRoute } from 'vue-router';
import SiteHeader from '@/components/site/SiteHeader.vue';
import SmartLink from '@/components/site/SmartLink.vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import NoticePopup from '@/components/site/NoticePopup.vue';
import VideoPopup from '@/components/site/VideoPopup.vue';
import { loadSite, site } from '@/stores/site';

const route = useRoute();
const popup = ref(null);
const noticePopupOpen = ref(false);

// Page editors can preview another template with ?template=… (the API decides which sections they get).
const template = computed(() => route.query.template || site.settings.site_template || 'classic');

// Admin template previews load the site in an iframe with ?embed=1; the notice popup would cover them.
const embedded = computed(() => route.query.embed === '1');

provide('openNoticePopup', () => popup.value?.show());

const tickerText = computed(() => site.settings.ticker_text || site.notices[0]?.title || '');

onMounted(() => loadSite());
</script>

<template>
    <div v-if="site.loaded" :data-template="template" style="background: var(--bg); color: var(--ink); min-height: 100vh">
        <NoticePopup v-if="!embedded" ref="popup" @toggle="noticePopupOpen = $event" />
        <VideoPopup v-if="!embedded" :hold="noticePopupOpen" />

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
                <SmartLink v-for="link in site.menus.topbar" :key="link.id" :to="link.url || '#'" :new-tab="link.newTab">
                    {{ link.label }}
                </SmartLink>
            </div>
        </div>

        <SiteHeader />

        <div v-if="tickerText" class="ticker">
            <div class="container">
                <span v-if="site.settings.ticker_label" class="ticker-label">{{ site.settings.ticker_label }}</span>
                <div class="ticker-text">{{ tickerText }}</div>
                <SmartLink v-if="site.settings.ticker_link_label" :to="site.settings.ticker_link_url || '/notices'" class="ticker-more">
                    {{ site.settings.ticker_link_label }}
                </SmartLink>
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
