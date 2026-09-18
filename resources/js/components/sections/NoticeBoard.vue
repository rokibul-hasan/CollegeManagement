<script setup>
import { computed } from 'vue';
import { site } from '@/stores/site';
import { bnShortDate } from '@/utils/bn';

// The list is doubled so the CSS ticker animation can loop seamlessly.
const loop = computed(() => (site.notices.length > 3 ? site.notices.concat(site.notices) : site.notices));
const animate = computed(() => site.notices.length > 3);
</script>

<template>
    <div id="notice" class="notice-board">
        <div class="notice-board-head">
            <span class="title">নোটিশ বোর্ড</span>
            <span class="spacer" />
            <span class="tag">NOTICE</span>
        </div>
        <div class="notice-board-body">
            <div :class="{ 'notice-board-track': animate }">
                <router-link
                    v-for="(notice, index) in loop"
                    :key="`${notice.id}-${index}`"
                    :to="`/notices/${notice.id}`"
                    class="notice-board-item"
                >
                    <span class="meta">
                        <i />
                        <span>{{ bnShortDate(notice.published_on) }}</span>
                    </span>
                    <span class="text">{{ notice.title }}</span>
                </router-link>
                <div v-if="!site.notices.length" class="notice-board-empty">এখনো কোনো নোটিশ প্রকাশিত হয়নি।</div>
            </div>
        </div>
        <router-link to="/notices" class="notice-board-foot">সব নোটিশ দেখুন →</router-link>
    </div>
</template>
