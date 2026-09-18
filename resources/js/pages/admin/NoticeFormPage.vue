<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api, { errorMessage, fieldErrors, toFormData } from '@/api';
import { invalidateSite } from '@/stores/site';
import { toast } from '@/stores/toast';

const route = useRoute();
const router = useRouter();

const isEdit = computed(() => Boolean(route.params.id));
const categories = ref([]);
const errors = ref({});
const saving = ref(false);
const existingAttachment = ref(null);
const fileInput = ref(null);

const form = reactive({
    title: '',
    notice_category_id: '',
    body: '',
    published_on: new Date().toISOString().slice(0, 10),
    is_published: true,
    show_in_popup: false,
    is_pinned: false,
    attachment: null,
    remove_attachment: false,
});

function onFileChange(event) {
    form.attachment = event.target.files[0] ?? null;
}

async function save() {
    saving.value = true;
    errors.value = {};

    const payload = toFormData({ ...form, attachment: form.attachment ?? undefined });

    if (!form.attachment) {
        payload.delete('attachment');
    }

    try {
        if (isEdit.value) {
            payload.append('_method', 'PUT');
            await api.post(`/admin/notices/${route.params.id}`, payload);
        } else {
            await api.post('/admin/notices', payload);
        }

        invalidateSite();
        toast(isEdit.value ? 'নোটিশ হালনাগাদ হয়েছে।' : 'নোটিশ প্রকাশ হয়েছে।');
        router.push('/admin/notices');
    } catch (error) {
        errors.value = fieldErrors(error);
        toast(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    categories.value = (await api.get('/admin/notice-categories')).data;

    if (isEdit.value) {
        const { data } = await api.get(`/admin/notices/${route.params.id}`);
        Object.assign(form, {
            title: data.title,
            notice_category_id: data.notice_category_id ?? '',
            body: data.body ?? '',
            published_on: data.published_on,
            is_published: data.is_published,
            show_in_popup: data.show_in_popup,
            is_pinned: data.is_pinned,
        });
        existingAttachment.value = data.attachment_url;
    }
});
</script>

<template>
    <form class="panel" @submit.prevent="save">
        <div class="panel-head">
            <div>
                <h2>{{ isEdit ? 'নোটিশ সম্পাদনা' : 'নতুন নোটিশ' }}</h2>
                <p>প্রকাশিত নোটিশ ওয়েবসাইটের নোটিশ বোর্ড, টিকার ও নোটিশ পাতায় দেখাবে।</p>
            </div>
        </div>
        <div class="panel-body">
            <div class="form-grid">
                <div class="field full">
                    <label for="title">শিরোনাম *</label>
                    <input id="title" v-model="form.title" class="input" required maxlength="255">
                    <span v-if="errors.title" class="error">{{ errors.title }}</span>
                </div>
                <div class="field">
                    <label for="category">ক্যাটাগরি</label>
                    <select id="category" v-model="form.notice_category_id" class="input">
                        <option value="">— ক্যাটাগরি নেই —</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <span v-if="errors.notice_category_id" class="error">{{ errors.notice_category_id }}</span>
                </div>
                <div class="field">
                    <label for="published_on">প্রকাশের তারিখ *</label>
                    <input id="published_on" v-model="form.published_on" type="date" class="input" required>
                    <span class="hint">ভবিষ্যতের তারিখ দিলে সেই দিন থেকে দেখাবে।</span>
                    <span v-if="errors.published_on" class="error">{{ errors.published_on }}</span>
                </div>
                <div class="field full">
                    <label for="body">বিস্তারিত</label>
                    <textarea id="body" v-model="form.body" class="input" rows="8" />
                    <span v-if="errors.body" class="error">{{ errors.body }}</span>
                </div>
                <div class="field full">
                    <span class="label">সংযুক্ত ফাইল (PDF, Word, Excel, ছবি — সর্বোচ্চ ১০ MB)</span>
                    <div v-if="existingAttachment && !form.remove_attachment" style="display: flex; gap: 12px; align-items: center; font-size: 14px">
                        <a :href="existingAttachment" target="_blank" rel="noopener">বর্তমান ফাইল দেখুন ↗</a>
                        <button type="button" class="btn btn-danger btn-sm" @click="form.remove_attachment = true">ফাইল সরান</button>
                    </div>
                    <input ref="fileInput" type="file" class="input" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" @change="onFileChange">
                    <span v-if="errors.attachment" class="error">{{ errors.attachment }}</span>
                </div>
                <div class="field full" style="display: flex; flex-wrap: wrap; gap: 22px">
                    <label class="switch"><input v-model="form.is_published" type="checkbox"> প্রকাশিত</label>
                    <label class="switch"><input v-model="form.show_in_popup" type="checkbox"> হোমপেজ পপআপে দেখান</label>
                    <label class="switch"><input v-model="form.is_pinned" type="checkbox"> শীর্ষে পিন করুন</label>
                </div>
            </div>
            <div class="form-actions" style="margin-top: 20px">
                <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'সংরক্ষণ হচ্ছে…' : 'সংরক্ষণ করুন' }}</button>
                <router-link to="/admin/notices" class="btn btn-outline">বাতিল</router-link>
            </div>
        </div>
    </form>
</template>
