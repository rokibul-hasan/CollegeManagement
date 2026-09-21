<script setup>
import { computed, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
    data: { type: Object, default: () => ({}) },
});

const router = useRouter();
const form = reactive({ roll: '', registration: '' });
const message = ref('');

const heading = computed(() => props.data.heading || 'ফলাফল');
const text = computed(() => props.data.text || 'রোল ও রেজিস্ট্রেশন নম্বর দিয়ে মার্কশিট দেখুন।');
const buttonLabel = computed(() => props.data.button_label || 'ফলাফল দেখুন');

/**
 * With no result service configured the card only explains where results are published.
 */
const fallbackMessage = computed(
    () => props.data.message || 'অনলাইন ফলাফল সেবা শীঘ্রই চালু হবে। আপাতত নোটিশ বোর্ডে প্রকাশিত ফলাফল দেখুন।',
);

function submit() {
    if (!form.roll || !form.registration) {
        message.value = 'রোল ও রেজিস্ট্রেশন নম্বর দুটিই লিখুন।';

        return;
    }

    const target = props.data.action_url;

    if (!target) {
        message.value = fallbackMessage.value;

        return;
    }

    const query = { roll: form.roll, registration: form.registration };

    if (target.startsWith('/')) {
        router.push({ path: target, query });

        return;
    }

    const url = new URL(target);
    Object.entries(query).forEach(([key, value]) => url.searchParams.set(key, value));
    window.open(url.toString(), '_blank', 'noopener');
}
</script>

<template>
    <div class="card-dark form-card">
        <h3 style="color: #fff">{{ heading }}</h3>
        <p style="color: #c5d6ea">{{ text }}</p>
        <form class="form-stack" @submit.prevent="submit">
            <input v-model.trim="form.roll" type="text" inputmode="numeric" placeholder="রোল নম্বর" class="input input-dark">
            <input v-model.trim="form.registration" type="text" inputmode="numeric" placeholder="রেজিস্ট্রেশন নম্বর" class="input input-dark">
            <button type="submit" class="btn btn-gold" style="padding: 13px; font-size: 15.5px; font-weight: 700">{{ buttonLabel }}</button>
        </form>
        <p v-if="message" class="form-message">{{ message }}</p>
    </div>
</template>
