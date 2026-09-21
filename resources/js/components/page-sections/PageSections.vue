<script setup>
import { SECTION_COMPONENTS } from './registry';

defineProps({
    sections: { type: Array, required: true },
});
</script>

<template>
    <div class="ps-grid">
        <section
            v-for="(section, index) in sections"
            :key="section.id ?? section.uid ?? index"
            class="ps-section"
            :class="`ps-${section.width || 'full'}`"
        >
            <component :is="SECTION_COMPONENTS[section.type]" v-if="SECTION_COMPONENTS[section.type]" :data="section.data || {}" />
        </section>
    </div>
</template>
