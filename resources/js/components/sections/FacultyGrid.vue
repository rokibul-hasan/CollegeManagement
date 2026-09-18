<script setup>
import { computed } from 'vue';
import { faculty } from '@/data/content';

const props = defineProps({
    limit: { type: Number, default: 0 },
});

const people = computed(() => (props.limit ? faculty.slice(0, props.limit) : faculty));
</script>

<template>
    <div class="grid grid-auto-190">
        <div v-for="(person, index) in people" :key="index" class="person-card">
            <div class="person-photo">
                <div class="avatar">{{ person.initial }}</div>
                <div class="placeholder-note">[ ছবি যোগ করুন ]</div>
            </div>
            <div class="body">
                <div class="name">{{ person.name }}</div>
                <div class="role">{{ person.role }}</div>
            </div>
        </div>
    </div>
</template>
