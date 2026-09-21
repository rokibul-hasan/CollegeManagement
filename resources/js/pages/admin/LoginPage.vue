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
const showPassword = ref(false);

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
                    <div class="password-wrap">
                        <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" class="input" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" :aria-label="showPassword ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখুন'" :title="showPassword ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখুন'" :aria-pressed="showPassword" @click="showPassword = !showPassword">
                            <svg v-if="showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
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
