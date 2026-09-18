<script setup>
import PageHero from '@/components/site/PageHero.vue';
</script>

<template>
    <PageHero title="পাতা পাওয়া যায়নি" />
    <div class="container page-body">
        <div class="card empty-state">
            আপনি যে পাতাটি খুঁজছেন সেটি সরানো হয়েছে অথবা বিদ্যমান নেই।
            <div style="margin-top: 16px"><router-link to="/" class="btn btn-primary">প্রচ্ছদে ফিরে যান</router-link></div>
        </div>
    </div>
</template>
