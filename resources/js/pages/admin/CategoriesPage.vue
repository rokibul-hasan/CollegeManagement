<script setup>
import { onMounted, reactive, ref } from 'vue';
import api, { errorMessage, fieldErrors } from '@/api';
import { invalidateSite } from '@/stores/site';
import { toast } from '@/stores/toast';
import { bnDigits } from '@/utils/bn';

const categories = ref([]);
const editingId = ref(null);
const errors = ref({});
const form = reactive({ name: '', slug: '', sort_order: 0 });

async function fetchCategories() {
    categories.value = (await api.get('/admin/notice-categories')).data;
}

function reset() {
    editingId.value = null;
    errors.value = {};
    Object.assign(form, { name: '', slug: '', sort_order: categories.value.length + 1 });
}

function edit(category) {
    editingId.value = category.id;
    errors.value = {};
    Object.assign(form, { name: category.name, slug: category.slug, sort_order: category.sort_order });
}

async function save() {
    errors.value = {};

    try {
        if (editingId.value) {
            await api.put(`/admin/notice-categories/${editingId.value}`, form);
        } else {
            await api.post('/admin/notice-categories', form);
        }

        invalidateSite();
        toast('ক্যাটাগরি সংরক্ষণ হয়েছে।');
        await fetchCategories();
        reset();
    } catch (error) {
        errors.value = fieldErrors(error);
        toast(errorMessage(error), 'error');
    }
}

async function remove(category) {
    if (!window.confirm(`"${category.name}" ক্যাটাগরি মুছবেন? এর নোটিশগুলো ক্যাটাগরি ছাড়া থেকে যাবে।`)) {
        return;
    }

    await api.delete(`/admin/notice-categories/${category.id}`);
    invalidateSite();
    toast('ক্যাটাগরি মুছে ফেলা হয়েছে।');
    fetchCategories();
}

onMounted(async () => {
    await fetchCategories();
    reset();
});
</script>

<template>
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); align-items: start">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>নোটিশ ক্যাটাগরি</h2>
                    <p>নোটিশ পাতার ট্যাব ও মেনুর গণনায় ব্যবহৃত হয়।</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>নাম</th><th>স্লাগ</th><th>নোটিশ</th><th /></tr></thead>
                    <tbody>
                        <tr v-for="category in categories" :key="category.id">
                            <td style="font-weight: 600">{{ category.name }}</td>
                            <td class="mono" style="font-size: 12.5px">{{ category.slug }}</td>
                            <td>{{ bnDigits(category.notices_count) }}</td>
                            <td>
                                <div class="actions">
                                    <button type="button" class="btn btn-soft btn-sm" @click="edit(category)">সম্পাদনা</button>
                                    <button type="button" class="btn btn-danger btn-sm" @click="remove(category)">মুছুন</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <form class="panel" @submit.prevent="save">
            <div class="panel-head"><h2>{{ editingId ? 'ক্যাটাগরি সম্পাদনা' : 'নতুন ক্যাটাগরি' }}</h2></div>
            <div class="panel-body" style="display: grid; gap: 14px">
                <div class="field">
                    <label for="cat-name">নাম *</label>
                    <input id="cat-name" v-model="form.name" class="input" required>
                    <span v-if="errors.name" class="error">{{ errors.name }}</span>
                </div>
                <div class="field">
                    <label for="cat-slug">স্লাগ (ইংরেজি) *</label>
                    <input id="cat-slug" v-model.trim="form.slug" class="input mono" required placeholder="admission">
                    <span class="hint">লিংকে ব্যবহার হবে: /notices?category={{ form.slug || 'slug' }}</span>
                    <span v-if="errors.slug" class="error">{{ errors.slug }}</span>
                </div>
                <div class="field">
                    <label for="cat-order">ক্রম</label>
                    <input id="cat-order" v-model.number="form.sort_order" type="number" min="0" class="input">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">সংরক্ষণ করুন</button>
                    <button v-if="editingId" type="button" class="btn btn-outline" @click="reset">বাতিল</button>
                </div>
            </div>
        </form>
    </div>
</template>
