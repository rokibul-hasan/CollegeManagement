<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    rows: { type: Array, required: true },
    note: { type: String, default: '' },
    link: { type: Object, default: null },
});
</script>

<template>
    <div class="card fee-card">
        <h3>{{ title }}</h3>
        <p class="sub">{{ subtitle }}</p>
        <div v-for="row in rows" :key="row.label" class="fee-row">
            <span>{{ row.label }}</span>
            <span>{{ row.amount }}</span>
        </div>
        <p v-if="note" class="placeholder-note" style="margin: 16px 0 0">{{ note }}</p>
        <router-link v-if="link" :to="link.to" style="display: inline-block; margin-top: 18px; font-size: 15px; font-weight: 600">
            {{ link.label }} →
        </router-link>
    </div>
</template>
