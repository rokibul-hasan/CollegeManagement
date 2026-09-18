<script setup>
import { gallery } from '@/data/content';
</script>

<template>
    <div class="gallery-grid">
        <div v-for="(item, index) in gallery" :key="index" class="gallery-item">
            <img :src="item.image" :alt="item.caption" :style="{ objectPosition: item.position }" loading="lazy">
            <div class="caption">{{ item.caption }}</div>
        </div>
    </div>
</template>
