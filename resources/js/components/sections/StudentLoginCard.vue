<script setup>
import { reactive, ref } from 'vue';

const form = reactive({ studentId: '', password: '' });
const message = ref('');

function submit() {
    message.value = form.studentId && form.password
        ? 'স্টুডেন্ট পোর্টাল শীঘ্রই চালু হবে। বিস্তারিত জানতে কলেজ অফিসে যোগাযোগ করুন।'
        : 'স্টুডেন্ট আইডি ও পাসওয়ার্ড দুটিই লিখুন।';
}
</script>

<template>
    <div id="login" class="card form-card">
        <h3 style="color: var(--navy)">স্টুডেন্ট লগইন</h3>
        <p style="color: var(--text)">উপস্থিতি, ফি ও ফলাফল দেখতে নিজের আইডি দিয়ে প্রবেশ করুন।</p>
        <form class="form-stack" @submit.prevent="submit">
            <input v-model.trim="form.studentId" type="text" placeholder="স্টুডেন্ট আইডি" class="input" autocomplete="username">
            <input v-model="form.password" type="password" placeholder="পাসওয়ার্ড" class="input" autocomplete="current-password">
            <button type="submit" class="btn btn-primary" style="padding: 13px; font-size: 15.5px; font-weight: 700">প্রবেশ করুন</button>
        </form>
        <p v-if="message" class="form-message">{{ message }}</p>
        <router-link to="/contact" style="display: inline-block; margin-top: 14px; font-size: 14.5px; font-weight: 600">পাসওয়ার্ড ভুলে গেছেন?</router-link>
    </div>
</template>
