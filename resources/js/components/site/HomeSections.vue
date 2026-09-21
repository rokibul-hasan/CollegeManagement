<script setup>
import { computed } from 'vue';
import { SECTION_COMPONENTS } from '@/components/page-sections/registry';

const props = defineProps({
    sections: { type: Array, required: true },
});

/**
 * Types that span the full page width and draw their own background.
 */
const BLEED_TYPES = ['band'];

/**
 * Group sections into rows: two consecutive half-width sections share a row,
 * and a row takes its background from its first section.
 */
const rows = computed(() => {
    const result = [];
    const sections = props.sections;

    for (let index = 0; index < sections.length; index++) {
        const section = sections[index];
        const next = sections[index + 1];
        const pair = section.width === 'half' && next?.width === 'half';

        result.push({
            key: section.id ?? section.uid,
            anchor: pair ? null : section.anchor,
            type: section.type,
            bleed: BLEED_TYPES.includes(section.type) && section.width !== 'half',
            background: section.data?.background || '',
            sections: pair ? [section, next] : [section],
        });

        if (pair) {
            index++;
        }
    }

    return result;
});
</script>

<template>
    <div class="home">
        <section
            v-for="row in rows"
            :id="row.anchor || undefined"
            :key="row.key"
            class="home-row"
            :class="[`home-row-${row.type}`, { 'is-bleed': row.bleed, [`home-bg-${row.background}`]: row.background }]"
        >
            <component :is="SECTION_COMPONENTS[row.type]" v-if="row.bleed" :data="row.sections[0].data" :bleed="true" />
            <div v-else class="container">
                <div class="ps-grid">
                    <div
                        v-for="section in row.sections"
                        :id="row.sections.length > 1 ? section.anchor || undefined : undefined"
                        :key="section.id ?? section.uid"
                        class="ps-section"
                        :class="`ps-${section.width || 'full'}`"
                    >
                        <component :is="SECTION_COMPONENTS[section.type]" v-if="SECTION_COMPONENTS[section.type]" :data="section.data || {}" />
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
