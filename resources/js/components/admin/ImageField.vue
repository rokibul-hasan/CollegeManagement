<script setup>
import { ref } from 'vue';
import api, { errorMessage } from '@/api';
import { toast } from '@/stores/toast';

const model = defineModel({ type: String, default: '' });
const uploading = ref(false);

async function upload(event) {
    const file = event.target.files[0];
    event.target.value = '';

    if (!file) {
        return;
    }

    uploading.value = true;
    const form = new FormData();
    form.append('image', file);

    try {
        const { data } = await api.post('/admin/media', form);
        model.value = data.url;
    } catch (error) {
        toast(errorMessage(error), 'error');
    } finally {
        uploading.value = false;
    }
}
</script>

<template>
    <div style="display: flex; gap: 12px; align-items: flex-start">
        <div class="logo-preview" style="width: 72px; height: 72px; flex: none">
            <img v-if="model" :src="model" alt="">
            <span v-else class="placeholder-note">ছবি নেই</span>
        </div>
        <div style="flex: 1; min-width: 0; display: grid; gap: 6px">
            <div style="display: flex; flex-wrap: wrap; gap: 6px">
                <label class="btn btn-soft btn-sm" style="cursor: pointer">
                    {{ uploading ? 'আপলোড হচ্ছে…' : 'ছবি আপলোড' }}
                    <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" hidden :disabled="uploading" @change="upload">
                </label>
                <button v-if="model" type="button" class="btn btn-danger btn-sm" @click="model = ''">সরান</button>
            </div>
            <input v-model.trim="model" class="input mono" style="padding: 7px 10px; font-size: 12.5px" placeholder="অথবা ছবির লিংক দিন">
        </div>
    </div>
</template>
