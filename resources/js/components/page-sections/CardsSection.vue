<script setup>
import SmartLink from '@/components/site/SmartLink.vue';
import SectionHeading from './SectionHeading.vue';
import TextBody from './TextBody.vue';
import { bnDigits } from '@/utils/bn';
import { safeUrl } from '@/utils/html';

defineProps({
    data: { type: Object, required: true },
});
</script>

<template>
    <div>
        <SectionHeading :heading="data.heading" :lead="data.lead" :more-label="data.more_label" :more-url="data.more_url" />

        <div v-if="data.style === 'numbered'" class="ps-cards" :style="{ '--ps-cols': data.columns || 3 }">
            <component
                :is="safeUrl(item.url) ? SmartLink : 'div'"
                v-for="(item, index) in data.items || []"
                :key="index"
                v-bind="safeUrl(item.url) ? { to: safeUrl(item.url), newTab: /^https?:/i.test(item.url) } : {}"
                class="service-card"
            >
                <div class="no">{{ bnDigits(String(index + 1).padStart(2, '0')) }}</div>
                <div class="title">{{ item.title }}</div>
                <div v-if="item.text" class="sub">{{ item.text }}</div>
            </component>
        </div>

        <div v-else class="ps-cards" :style="{ '--ps-cols': data.columns || 3 }">
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
