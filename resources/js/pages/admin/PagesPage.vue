<script setup>
import { onMounted, ref } from 'vue';
import api, { errorMessage } from '@/api';
import { toast } from '@/stores/toast';
import { bnDigits } from '@/utils/bn';

const pages = ref([]);
const loading = ref(true);

async function fetchPages() {
    pages.value = (await api.get('/admin/pages')).data;
    loading.value = false;
}

async function remove(page) {
    if (!window.confirm(`"${page.title}" পেজটি ও এর সব সেকশন মুছে ফেলবেন? মেনুতে এর লিংক থাকলে সেটিও সরিয়ে দিন।`)) {
        return;
    }

    try {
        await api.delete(`/admin/pages/${page.id}`);
        toast('পেজ মুছে ফেলা হয়েছে।');
        fetchPages();
    } catch (error) {
        toast(errorMessage(error), 'error');
    }
}

onMounted(fetchPages);
</script>

<template>
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>সকল পেজ</h2>
                <p>প্রতিটি পেজ সেকশন দিয়ে তৈরি — লেখা, কার্ড, টেবিল, ছবি, HTML ইত্যাদি যোগ ও সাজানো যায়।</p>
            </div>
            <span class="spacer" />
            <router-link to="/admin/pages/create" class="btn btn-primary">+ নতুন পেজ</router-link>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>পেজ</th><th>লিংক</th><th>সেকশন</th><th>অবস্থা</th><th /></tr></thead>
                <tbody>
                    <tr v-for="page in pages" :key="page.id">
                        <td style="font-weight: 600">{{ page.title }}</td>
                        <td><a :href="`/${page.slug}`" target="_blank" rel="noopener" class="mono" style="font-size: 13px">/{{ page.slug }} ↗</a></td>
                        <td>{{ bnDigits(page.sections_count) }}টি</td>
                        <td>
                            <span v-if="page.is_published" class="tag">প্রকাশিত</span>
                            <span v-else class="tag tag-red">খসড়া</span>
                        </td>
                        <td>
                            <div class="actions">
                                <router-link :to="`/admin/pages/${page.id}/edit`" class="btn btn-soft btn-sm">সম্পাদনা</router-link>
                                <button type="button" class="btn btn-danger btn-sm" @click="remove(page)">মুছুন</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!loading && !pages.length"><td colspan="5" class="empty-state">কোনো পেজ নেই।</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
