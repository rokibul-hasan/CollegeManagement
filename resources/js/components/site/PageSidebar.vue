<script setup>
import QuickLinksCard from '@/components/sections/QuickLinksCard.vue';
import { site } from '@/stores/site';
import { bnDate } from '@/utils/bn';
</script>

<template>
    <aside class="sidebar">
        <div class="card" style="padding: 18px">
            <div class="eyebrow" style="margin-bottom: 10px">{{ site.settings.recent_notices_title }}</div>
            <router-link
                v-for="notice in site.notices.slice(0, 5)"
                :key="notice.id"
                :to="`/notices/${notice.id}`"
                class="modal-item"
                style="padding: 12px 0"
            >
                <span class="dot" />
                <span style="min-width: 0">
                    <span class="t" style="font-size: 14.5px">{{ notice.title }}</span>
                    <span class="d">{{ bnDate(notice.published_on) }}</span>
                </span>
            </router-link>
            <p v-if="!site.notices.length" style="color: var(--muted); font-size: 14.5px">কোনো নোটিশ নেই।</p>
            <router-link to="/notices" style="display: inline-block; margin-top: 12px; font-size: 14.5px; font-weight: 600">সব নোটিশ →</router-link>
        </div>
        <QuickLinksCard />
    </aside>
</template>
