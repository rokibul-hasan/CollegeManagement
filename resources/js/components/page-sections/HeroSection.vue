<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import NoticeBoard from '@/components/sections/NoticeBoard.vue';
import SmartLink from '@/components/site/SmartLink.vue';
import { site } from '@/stores/site';
import { safeUrl } from '@/utils/html';

const props = defineProps({
    data: { type: Object, required: true },
});

const slides = computed(() => (props.data.slides || []).filter((item) => item.image));
const buttons = computed(() => (props.data.buttons || []).filter((button) => button.label && safeUrl(button.url)));
const slide = ref(0);
let timer = null;

function startAutoplay() {
    clearInterval(timer);

    if (slides.value.length > 1) {
        timer = setInterval(() => {
            slide.value = (slide.value + 1) % slides.value.length;
        }, 6000);
    }
}

function goTo(index) {
    slide.value = index;
    startAutoplay();
}

watch(() => slides.value.length, () => {
    slide.value = 0;
    startAutoplay();
});

onMounted(startAutoplay);
onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <div class="hero-layout">
        <div class="hero">
            <img
                v-for="(item, index) in slides"
                v-show="slide === index"
                :key="index"
                :src="item.image"
                :alt="item.alt || ''"
            >
            <div class="hero-shade" />
            <div class="hero-body">
                <div v-if="data.badge" class="hero-badge">{{ data.badge }}</div>
                <h2>{{ data.heading || site.settings.site_name }}</h2>
                <p v-if="data.lead">{{ data.lead }}</p>
                <div v-if="buttons.length" class="hero-actions">
                    <SmartLink
                        v-for="(button, index) in buttons"
                        :key="index"
                        :to="safeUrl(button.url)"
                        :new-tab="/^https?:/i.test(button.url)"
                        class="btn"
                        :class="button.style === 'ghost' ? 'btn-ghost-light' : 'btn-amber'"
                    >
                        {{ button.label }}
                    </SmartLink>
                </div>
            </div>
            <div v-if="slides.length > 1" class="hero-dots">
                <button
                    v-for="(item, index) in slides"
                    :key="index"
                    type="button"
                    :class="{ 'is-active': slide === index }"
                    :aria-label="`স্লাইড ${index + 1}`"
                    @click="goTo(index)"
                />
            </div>
        </div>

        <NoticeBoard v-if="data.show_notices === '1'" />
    </div>
</template>
