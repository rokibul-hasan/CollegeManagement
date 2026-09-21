<script setup>
import { computed, reactive, ref } from 'vue';
import SmartLink from '@/components/site/SmartLink.vue';

const props = defineProps({
    data: { type: Object, default: () => ({}) },
});

const form = reactive({ studentId: '', password: '' });
const message = ref('');

const heading = computed(() => props.data.heading || 'স্টুডেন্ট লগইন');
const text = computed(() => props.data.text || 'উপস্থিতি, ফি ও ফলাফল দেখতে নিজের আইডি দিয়ে প্রবেশ করুন।');
const buttonLabel = computed(() => props.data.button_label || 'প্রবেশ করুন');
const linkLabel = computed(() => props.data.link_label || 'পাসওয়ার্ড ভুলে গেছেন?');

/**
 * When a portal address is configured the card links straight to it instead of
 * collecting credentials this site cannot verify.
 */
const portal = computed(() => props.data.action_url || '');

function submit() {
    message.value = form.studentId && form.password
        ? props.data.message || 'স্টুডেন্ট পোর্টাল শীঘ্রই চালু হবে। বিস্তারিত জানতে কলেজ অফিসে যোগাযোগ করুন।'
        : 'স্টুডেন্ট আইডি ও পাসওয়ার্ড দুটিই লিখুন।';
}
</script>

<template>
    <div class="card form-card">
        <h3 style="color: var(--navy)">{{ heading }}</h3>
        <p style="color: var(--text)">{{ text }}</p>
        <SmartLink
            v-if="portal"
            :to="portal"
            :new-tab="!portal.startsWith('/')"
            class="btn btn-primary"
            style="margin-top: 14px; padding: 13px; font-size: 15.5px; font-weight: 700"
        >
            {{ buttonLabel }}
        </SmartLink>
        <form v-else class="form-stack" @submit.prevent="submit">
            <input v-model.trim="form.studentId" type="text" placeholder="স্টুডেন্ট আইডি" class="input" autocomplete="username">
            <input v-model="form.password" type="password" placeholder="পাসওয়ার্ড" class="input" autocomplete="current-password">
            <button type="submit" class="btn btn-primary" style="padding: 13px; font-size: 15.5px; font-weight: 700">{{ buttonLabel }}</button>
        </form>
        <p v-if="message" class="form-message">{{ message }}</p>
        <SmartLink
            v-if="linkLabel"
            :to="data.link_url || '/contact'"
            style="display: inline-block; margin-top: 14px; font-size: 14.5px; font-weight: 600"
        >
            {{ linkLabel }}
        </SmartLink>
    </div>
</template>
