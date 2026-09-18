<script setup>
import { routines } from '@/data/content';
</script>

<template>
    <div class="table-card">
        <div class="data-grid">
            <div class="th">শ্রেণি</div>
            <div class="th">শিফট</div>
            <div class="th">রুটিন</div>
            <template v-for="routine in routines" :key="routine.cls">
                <div class="strong">{{ routine.cls }}</div>
                <div>{{ routine.shift }}</div>
                <div><router-link to="/notices?category=exam" style="font-weight: 600">ডাউনলোড (PDF)</router-link></div>
            </template>
        </div>
    </div>
</template>
