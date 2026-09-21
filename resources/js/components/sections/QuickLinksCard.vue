<script setup>
import { inject } from 'vue';
import SmartLink from '@/components/site/SmartLink.vue';
import { site } from '@/stores/site';

const openNoticePopup = inject('openNoticePopup', null);
</script>

<template>
    <div class="card" style="padding: 18px">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px">
            <span class="eyebrow">{{ site.settings.quick_links_title }}</span>
            <span class="spacer" />
            <button v-if="openNoticePopup && site.notices.length" type="button" class="pill-btn" @click="openNoticePopup">নোটিশ পপআপ</button>
        </div>
        <div class="link-list">
            <SmartLink v-for="link in site.menus.quick" :key="link.id" :to="link.url || '#'" :new-tab="link.newTab">
                <span>{{ link.label }}</span>
                <span>→</span>
            </SmartLink>
        </div>
    </div>
</template>
