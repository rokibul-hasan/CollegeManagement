<script setup>
import { reactive, ref } from 'vue';

const form = reactive({ roll: '', registration: '' });
const message = ref('');

function submit() {
    message.value = form.roll && form.registration
        ? 'অনলাইন ফলাফল সেবা শীঘ্রই চালু হবে। আপাতত নোটিশ বোর্ডে প্রকাশিত ফলাফল দেখুন।'
        : 'রোল ও রেজিস্ট্রেশন নম্বর দুটিই লিখুন।';
}
</script>

<template>
    <div id="result" class="card-dark form-card">
        <h3 style="color: #fff">ফলাফল</h3>
        <p style="color: #c5d6ea">রোল ও রেজিস্ট্রেশন নম্বর দিয়ে মার্কশিট দেখুন।</p>
        <form class="form-stack" @submit.prevent="submit">
            <input v-model.trim="form.roll" type="text" inputmode="numeric" placeholder="রোল নম্বর" class="input input-dark">
            <input v-model.trim="form.registration" type="text" inputmode="numeric" placeholder="রেজিস্ট্রেশন নম্বর" class="input input-dark">
            <button type="submit" class="btn btn-gold" style="padding: 13px; font-size: 15.5px; font-weight: 700">ফলাফল দেখুন</button>
        </form>
        <p v-if="message" class="form-message">{{ message }}</p>
    </div>
</template>
