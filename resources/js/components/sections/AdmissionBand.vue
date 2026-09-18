<script setup>
import { admission } from '@/data/content';
</script>

<template>
    <section id="bharti" class="admission-band">
        <div class="container">
            <div>
                <h3>ভর্তি তথ্য</h3>
                <p>{{ admission.text }}</p>
            </div>
            <div style="display: grid; gap: 10px">
                <router-link v-for="link in admission.links" :key="link.label" :to="link.to" class="band-link">
                    {{ link.label }} <span>→</span>
                </router-link>
            </div>
        </div>
    </section>
</template>
