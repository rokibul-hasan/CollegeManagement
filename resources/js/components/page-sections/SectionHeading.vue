<script setup>
import SmartLink from '@/components/site/SmartLink.vue';
import { safeUrl } from '@/utils/html';

defineProps({
    heading: { type: String, default: '' },
    lead: { type: String, default: '' },
    moreLabel: { type: String, default: '' },
    moreUrl: { type: String, default: '' },
    light: { type: Boolean, default: false },
});
</script>

<template>
    <div v-if="heading || lead || (moreLabel && safeUrl(moreUrl))" class="ps-heading">
        <div class="ps-heading-row">
            <h3 v-if="heading" class="serif-title" :style="light ? { color: '#fff' } : null">{{ heading }}</h3>
            <span class="spacer" />
            <SmartLink v-if="moreLabel && safeUrl(moreUrl)" :to="safeUrl(moreUrl)" :new-tab="/^https?:/i.test(moreUrl)" class="more-link">
                {{ moreLabel }} →
            </SmartLink>
        </div>
        <p v-if="lead" class="ps-lead" :style="light ? { color: '#c5d6ea' } : null">{{ lead }}</p>
    </div>
</template>
