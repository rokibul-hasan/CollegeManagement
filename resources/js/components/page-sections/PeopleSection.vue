<script setup>
import SectionHeading from './SectionHeading.vue';

defineProps({
    data: { type: Object, required: true },
});

function initial(name) {
    return String(name || '').replace(/[[\]\s]/g, '').slice(0, 1) || '•';
}
</script>

<template>
    <div>
        <SectionHeading :heading="data.heading" :lead="data.lead" />

        <div v-if="data.style === 'message'" style="display: grid; gap: 18px">
            <div v-for="(person, index) in data.items || []" :key="index" class="message-card" style="padding: 24px">
                <div class="portrait" style="overflow: hidden">
                    <img v-if="person.photo" :src="person.photo" :alt="person.name" style="width: 100%; height: 100%; object-fit: cover">
                    <div v-else class="avatar">{{ initial(person.role) }}</div>
                </div>
                <div style="min-width: 0">
                    <p v-if="person.text" style="font-size: 15.5px; white-space: pre-line">{{ person.text }}</p>
                    <div style="font-size: 15px; font-weight: 700; color: var(--ink)">{{ person.name }}</div>
                    <div style="font-size: 13.5px; color: var(--muted)">{{ person.role }}</div>
                </div>
            </div>
        </div>

        <div v-else class="grid grid-auto-190">
            <div v-for="(person, index) in data.items || []" :key="index" class="person-card">
                <div class="person-photo" :style="person.photo ? { padding: 0 } : null">
                    <img v-if="person.photo" :src="person.photo" :alt="person.name" style="width: 100%; height: 190px; object-fit: cover" loading="lazy">
                    <template v-else>
                        <div class="avatar">{{ initial(person.role) }}</div>
                        <div class="placeholder-note">[ ছবি যোগ করুন ]</div>
                    </template>
                </div>
                <div class="body">
                    <div class="name">{{ person.name }}</div>
                    <div class="role">{{ person.role }}</div>
                    <p v-if="person.text" style="margin: 8px 0 0; font-size: 14px; line-height: 1.7; color: var(--text); white-space: pre-line">{{ person.text }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
