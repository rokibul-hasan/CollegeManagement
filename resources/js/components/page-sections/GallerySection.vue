<script setup>
import SectionHeading from './SectionHeading.vue';

defineProps({
    data: { type: Object, required: true },
});
</script>

<template>
    <div>
        <SectionHeading :heading="data.heading" :lead="data.lead" :more-label="data.more_label" :more-url="data.more_url" />
        <div class="gallery-grid">
            <div v-for="(item, index) in (data.items || []).filter((item) => item.image)" :key="index" class="gallery-item">
                <img :src="item.image" :alt="item.caption || ''" loading="lazy">
                <div v-if="item.caption" class="caption">{{ item.caption }}</div>
            </div>
        </div>
    </div>
</template>
