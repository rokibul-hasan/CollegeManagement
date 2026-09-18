<script setup>
import TextSection from './TextSection.vue';
import CardsSection from './CardsSection.vue';
import PeopleSection from './PeopleSection.vue';
import TableSection from './TableSection.vue';
import GallerySection from './GallerySection.vue';
import BandSection from './BandSection.vue';
import HtmlSection from './HtmlSection.vue';
import WidgetSection from './WidgetSection.vue';

defineProps({
    sections: { type: Array, required: true },
});

const components = {
    text: TextSection,
    cards: CardsSection,
    people: PeopleSection,
    table: TableSection,
    gallery: GallerySection,
    band: BandSection,
    html: HtmlSection,
    widget: WidgetSection,
};
</script>

<template>
    <div class="ps-grid">
        <section
            v-for="(section, index) in sections"
            :key="section.id ?? section.uid ?? index"
            class="ps-section"
            :class="`ps-${section.width || 'full'}`"
        >
            <component :is="components[section.type]" v-if="components[section.type]" :data="section.data || {}" />
        </section>
    </div>
</template>
