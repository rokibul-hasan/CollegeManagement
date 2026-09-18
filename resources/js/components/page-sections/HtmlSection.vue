<script setup>
import { computed } from 'vue';
import { sanitizeHtml } from '@/utils/html';

const props = defineProps({
    data: { type: Object, required: true },
});

const html = computed(() => sanitizeHtml(props.data.html));
</script>

<template>
    <!-- Sanitized with DOMPurify: scripts, event handlers and javascript: links are stripped. -->
    <div class="ps-html" v-html="html" />
</template>
