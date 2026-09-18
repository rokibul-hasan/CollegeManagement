<script setup>
defineProps({
    heading: { type: String, default: '' },
    lead: { type: String, default: '' },
    light: { type: Boolean, default: false },
});
</script>

<template>
    <div v-if="heading || lead" class="ps-heading">
        <h3 v-if="heading" class="serif-title" :style="light ? { color: '#fff' } : null">{{ heading }}</h3>
        <p v-if="lead" class="ps-lead" :style="light ? { color: '#c5d6ea' } : null">{{ lead }}</p>
    </div>
</template>
