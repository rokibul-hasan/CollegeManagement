<script setup>
import { computed } from 'vue';
import QuickLinksCard from '@/components/sections/QuickLinksCard.vue';
import SmartLink from '@/components/site/SmartLink.vue';
import TextBody from './TextBody.vue';
import { safeUrl } from '@/utils/html';

const props = defineProps({
    data: { type: Object, required: true },
});

const messages = computed(() => (props.data.messages || []).filter((message) => message.title || message.text));
const hasSidebar = computed(() => props.data.show_quick_links === '1' || props.data.cta_heading || props.data.cta_label);

function initial(text) {
    return String(text || '').replace(/[[\]\s]/g, '').slice(0, 1) || '•';
}
</script>

<template>
    <div class="intro-grid" :class="{ 'no-sidebar': !hasSidebar }">
        <div style="display: grid; gap: 30px; min-width: 0">
            <div class="card intro-card">
                <h3 v-if="data.heading">{{ data.heading }}</h3>
                <img v-if="data.image" :src="data.image" :alt="data.heading || ''">
                <TextBody :text="data.body" />
                <SmartLink
                    v-if="data.link_label && safeUrl(data.link_url)"
                    :to="safeUrl(data.link_url)"
                    style="display: inline-block; margin-top: 14px; font-size: 15px; font-weight: 600"
                >
                    {{ data.link_label }} →
                </SmartLink>
            </div>

            <div v-if="messages.length" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px">
                <div v-for="(message, index) in messages" :key="index" class="message-card">
                    <div class="portrait" style="overflow: hidden">
                        <img v-if="message.photo" :src="message.photo" :alt="message.name || message.title" style="width: 100%; height: 100%; object-fit: cover">
                        <div v-else class="avatar">{{ initial(message.title) }}</div>
                    </div>
                    <div style="min-width: 0">
                        <h4>{{ message.title }}</h4>
                        <p>{{ message.text }}</p>
                        <SmartLink v-if="safeUrl(message.url)" :to="safeUrl(message.url)" style="font-size: 14px; font-weight: 600">বিস্তারিত →</SmartLink>
                    </div>
                </div>
            </div>
        </div>

        <aside v-if="hasSidebar" class="sidebar">
            <QuickLinksCard v-if="data.show_quick_links === '1'" />
            <div v-if="data.cta_heading || data.cta_label" class="card-deep">
                <div v-if="data.cta_heading" style="font-size: 12.5px; letter-spacing: .14em; color: var(--gold-light); margin-bottom: 10px">{{ data.cta_heading }}</div>
                <p v-if="data.cta_text" style="margin: 0 0 16px; font-size: 15px; line-height: 1.75">{{ data.cta_text }}</p>
                <SmartLink v-if="data.cta_label && safeUrl(data.cta_url)" :to="safeUrl(data.cta_url)" class="btn btn-gold btn-block">{{ data.cta_label }}</SmartLink>
            </div>
        </aside>
    </div>
</template>
