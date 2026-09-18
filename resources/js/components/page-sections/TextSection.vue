<script setup>
import { computed } from 'vue';
import SmartLink from '@/components/site/SmartLink.vue';
import TextBody from './TextBody.vue';
import { safeUrl } from '@/utils/html';

const props = defineProps({
    data: { type: Object, required: true },
});

const style = computed(() => props.data.style || 'card');
const position = computed(() => props.data.image_position || 'top');
const buttonUrl = computed(() => safeUrl(props.data.button_url));
</script>

<template>
    <div class="ps-text" :class="[`ps-style-${style}`, data.image ? `ps-image-${position}` : '']">
        <img v-if="data.image" :src="data.image" :alt="data.heading || ''" class="ps-text-image" loading="lazy">
        <div class="ps-text-content">
            <h3 v-if="data.heading" class="serif-title">{{ data.heading }}</h3>
            <TextBody :text="data.body" />
            <SmartLink
                v-if="data.button_label && buttonUrl"
                :to="buttonUrl"
                :new-tab="/^https?:/i.test(buttonUrl)"
                class="btn"
                :class="style === 'dark' ? 'btn-gold' : 'btn-primary'"
                style="margin-top: 4px"
            >
                {{ data.button_label }}
            </SmartLink>
        </div>
    </div>
</template>
