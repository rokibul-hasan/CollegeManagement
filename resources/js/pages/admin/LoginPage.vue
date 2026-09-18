<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { errorMessage } from '@/api';
import { login } from '@/stores/auth';
import { loadSite, site } from '@/stores/site';

const route = useRoute();
const router = useRouter();

const form = reactive({ email: '', password: '', remember: true });
const error = ref('');
const submitting = ref(false);

async function submit() {
    error.value = '';
    submitting.value = true;

    try {
        await login(form);
        const redirect = String(route.query.redirect || '');
        router.replace(redirect.startsWith('/admin') ? redirect : '/admin');
    } catch (exception) {
        error.value = errorMessage(exception);
    } finally {
        submitting.value = false;
    }
}

onMounted(() => loadSite());
</script>

<template>
    <div class="login-shell">
        <form class="login-card" @submit.prevent="submit">
            <div class="brand-mini">
                <img :src="site.logo" alt="">
                <div>
                    <h1>{{ site.settings.site_name || 'কলেজ ম্যানেজমেন্ট সিস্টেম' }}</h1>
                    <div class="sub">অ্যাডমিন পোর্টালে প্রবেশ করুন</div>
                </div>
            </div>
            <div style="display: grid; gap: 14px">
                <div v-if="error" class="alert">{{ error }}</div>
                <div class="field">
                    <label for="email">ইমেইল</label>
                    <input id="email" v-model.trim="form.email" type="email" class="input" autocomplete="username" required autofocus>
                </div>
                <div class="field">
                    <label for="password">পাসওয়ার্ড</label>
                    <input id="password" v-model="form.password" type="password" class="input" autocomplete="current-password" required>
                </div>
                <label class="switch"><input v-model="form.remember" type="checkbox"> মনে রাখুন</label>
                <button type="submit" class="btn btn-primary btn-block" :disabled="submitting" style="padding: 13px">
                    {{ submitting ? 'অপেক্ষা করুন…' : 'প্রবেশ করুন' }}
                </button>
                <router-link to="/" style="text-align: center; font-size: 14px; font-weight: 600">← ওয়েবসাইটে ফিরে যান</router-link>
            </div>
        </form>
    </div>
</template>
