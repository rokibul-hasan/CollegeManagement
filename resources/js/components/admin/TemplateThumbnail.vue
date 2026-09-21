<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * A live, scaled-down screenshot of the public site in the given template.
 * The site is rendered at desktop width inside an iframe and shrunk to fit the card.
 */
const props = defineProps({
    template: { type: String, required: true },
    // Change it to reload the preview after saving.
    version: { type: Number, default: 0 },
});

const SITE_WIDTH = 1280;
const SITE_HEIGHT = 900;

const box = ref(null);
const scale = ref(0.2);
const loaded = ref(false);
let observer = null;

const src = computed(() => `/?template=${props.template}&embed=1&v=${props.version}`);

onMounted(() => {
    observer = new ResizeObserver(([entry]) => {
        scale.value = entry.contentRect.width / SITE_WIDTH;
    });
    observer.observe(box.value);
});

onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <div ref="box" class="template-thumb" :style="{ height: `${Math.round(SITE_HEIGHT * scale)}px` }">
        <iframe
            :key="src"
            :src="src"
            title="টেমপ্লেট প্রিভিউ"
            loading="lazy"
            tabindex="-1"
            aria-hidden="true"
            :style="{ width: `${SITE_WIDTH}px`, height: `${SITE_HEIGHT}px`, transform: `scale(${scale})` }"
            @load="loaded = true"
        />
        <span v-if="!loaded" class="template-thumb-loading">প্রিভিউ লোড হচ্ছে…</span>
    </div>
</template>
