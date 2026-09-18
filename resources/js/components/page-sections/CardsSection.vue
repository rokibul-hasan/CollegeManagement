<script setup>
import SmartLink from '@/components/site/SmartLink.vue';
import SectionHeading from './SectionHeading.vue';
import TextBody from './TextBody.vue';
import { safeUrl } from '@/utils/html';

defineProps({
    data: { type: Object, required: true },
});
</script>

<template>
    <div>
        <SectionHeading :heading="data.heading" :lead="data.lead" />
        <div class="ps-cards" :style="{ '--ps-cols': data.columns || 3 }">
            <template v-for="(item, index) in data.items || []" :key="index">
                <SmartLink
                    v-if="safeUrl(item.url)"
                    :to="safeUrl(item.url)"
                    :new-tab="/^https?:/i.test(item.url)"
                    class="ps-card ps-card-link"
                >
                    <div class="ps-card-title">{{ item.title }} <span class="ps-card-arrow">→</span></div>
                    <TextBody v-if="item.text" :text="item.text" />
                    <div v-if="item.meta" class="ps-card-meta">{{ item.meta }}</div>
                </SmartLink>
                <div v-else class="ps-card">
                    <div class="ps-card-title">{{ item.title }}</div>
                    <TextBody v-if="item.text" :text="item.text" />
                    <div v-if="item.meta" class="ps-card-meta">{{ item.meta }}</div>
                </div>
            </template>
        </div>
    </div>
</template>
