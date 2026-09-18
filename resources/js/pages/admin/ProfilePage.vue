<script setup>
import { reactive, ref } from 'vue';
import api, { errorMessage, fieldErrors } from '@/api';
import { auth } from '@/stores/auth';
import { toast } from '@/stores/toast';

const form = reactive({
    name: auth.user?.name ?? '',
    email: auth.user?.email ?? '',
    current_password: '',
    password: '',
    password_confirmation: '',
});
const errors = ref({});
const saving = ref(false);

async function save() {
    saving.value = true;
    errors.value = {};

    try {
        const { data } = await api.put('/admin/profile', form);
        auth.user = data.user;
        Object.assign(form, { current_password: '', password: '', password_confirmation: '' });
        toast('প্রোফাইল হালনাগাদ হয়েছে।');
    } catch (error) {
        errors.value = fieldErrors(error);
        toast(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <form class="panel" style="max-width: 720px" @submit.prevent="save">
        <div class="panel-head">
            <div>
                <h2>প্রোফাইল ও পাসওয়ার্ড</h2>
                <p>পাসওয়ার্ড পরিবর্তন না করতে চাইলে পাসওয়ার্ডের ঘর খালি রাখুন।</p>
            </div>
        </div>
        <div class="panel-body form-grid">
            <div class="field">
                <label for="name">নাম</label>
                <input id="name" v-model="form.name" class="input" required>
                <span v-if="errors.name" class="error">{{ errors.name }}</span>
            </div>
            <div class="field">
                <label for="email">ইমেইল</label>
                <input id="email" v-model.trim="form.email" type="email" class="input" required :readonly="auth.user?.is_super_admin">
                <span v-if="auth.user?.is_super_admin" class="hint">সুপার অ্যাডমিনের ইমেইল পরিবর্তন করা যায় না।</span>
                <span v-if="errors.email" class="error">{{ errors.email }}</span>
            </div>
            <div class="field full">
                <label for="current_password">বর্তমান পাসওয়ার্ড</label>
                <input id="current_password" v-model="form.current_password" type="password" class="input" autocomplete="current-password">
                <span v-if="errors.current_password" class="error">{{ errors.current_password }}</span>
            </div>
            <div class="field">
                <label for="password">নতুন পাসওয়ার্ড</label>
                <input id="password" v-model="form.password" type="password" class="input" autocomplete="new-password">
                <span v-if="errors.password" class="error">{{ errors.password }}</span>
            </div>
            <div class="field">
                <label for="password_confirmation">নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="input" autocomplete="new-password">
            </div>
            <div class="form-actions field full">
                <button type="submit" class="btn btn-primary" :disabled="saving">সংরক্ষণ করুন</button>
            </div>
        </div>
    </form>
</template>
