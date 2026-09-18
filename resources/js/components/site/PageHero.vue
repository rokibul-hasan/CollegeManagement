<script setup>
defineProps({
    title: { type: String, required: true },
    lead: { type: String, default: '' },
    trail: { type: Array, default: () => [] },
});
</script>

<template>
    <section class="page-hero">
        <div class="container">
            <div class="breadcrumb">
                <router-link to="/">প্রচ্ছদ</router-link>
                <template v-for="crumb in trail" :key="crumb.label">
                    <span>/</span>
                    <router-link v-if="crumb.to" :to="crumb.to">{{ crumb.label }}</router-link>
                    <span v-else>{{ crumb.label }}</span>
                </template>
                <span>/</span>
                <span style="color: #e9c877">{{ title }}</span>
            </div>
            <h2>{{ title }}</h2>
            <p v-if="lead">{{ lead }}</p>
        </div>
    </section>
</template>
