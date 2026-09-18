<script setup>
import { streams } from '@/data/content';

defineProps({
    detailed: { type: Boolean, default: false },
});
</script>

<template>
    <div class="grid grid-auto-260">
        <div v-for="stream in streams" :key="stream.name" class="card stream-card">
            <h4>{{ stream.name }}</h4>
            <p>{{ stream.desc }}</p>
            <ul v-if="detailed" class="chips">
                <li v-for="subject in stream.subjects" :key="subject">{{ subject }}</li>
            </ul>
            <div class="meta">{{ stream.meta }}</div>
        </div>
    </div>
</template>
