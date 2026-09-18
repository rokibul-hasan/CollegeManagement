<script setup>
import { computed } from 'vue';
import { bnDate, bnDigits } from '@/utils/bn';

const props = defineProps({
    notice: { type: Object, required: true },
});

const MONTHS_SHORT = ['জানু', 'ফেব্রু', 'মার্চ', 'এপ্রি', 'মে', 'জুন', 'জুলা', 'আগ', 'সেপ্ট', 'অক্টো', 'নভে', 'ডিসে'];

const tile = computed(() => {
    const [, month, day] = String(props.notice.published_on).split('-').map(Number);

    return { day: bnDigits(String(day).padStart(2, '0')), month: MONTHS_SHORT[month - 1] };
});
</script>

<template>
    <router-link :to="`/notices/${notice.id}`" class="notice-row">
        <div class="date-tile">
            <b>{{ tile.day }}</b>
            <small>{{ tile.month }}</small>
        </div>
        <div class="info">
            <div class="title">{{ notice.title }}</div>
            <div class="sub">
                <span>{{ bnDate(notice.published_on) }}</span>
                <span v-if="notice.category" class="tag">{{ notice.category.name }}</span>
                <span v-if="notice.is_pinned" class="tag tag-gold">গুরুত্বপূর্ণ</span>
                <span v-if="notice.attachment" class="tag tag-red">সংযুক্তি</span>
            </div>
        </div>
        <span style="color: var(--faint)">→</span>
    </router-link>
</template>
