<script setup>
import { coCurricular } from '@/data/content';
</script>

<template>
    <div class="grid grid-auto-230">
        <div v-for="activity in coCurricular" :key="activity.title" class="activity-card">
            <div class="title">{{ activity.title }}</div>
            <div class="desc">{{ activity.desc }}</div>
        </div>
    </div>
</template>
