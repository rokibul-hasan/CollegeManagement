<script setup>
import { computed } from 'vue';

const props = defineProps({
    to: { type: String, default: '' },
    newTab: { type: Boolean, default: false },
});

const isInternal = computed(() => props.to.startsWith('/') && !props.to.startsWith('//') && !props.newTab);
</script>

<template>
    <router-link v-if="isInternal" :to="to"><slot /></router-link>
    <a v-else :href="to || '#'" :target="newTab ? '_blank' : null" :rel="newTab ? 'noopener' : null"><slot /></a>
</template>
