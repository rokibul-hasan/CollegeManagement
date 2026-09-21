<script setup>
import { computed } from 'vue';
import ResultCard from '@/components/sections/ResultCard.vue';
import StudentLoginCard from '@/components/sections/StudentLoginCard.vue';
import NoticeRow from '@/components/site/NoticeRow.vue';
import { site } from '@/stores/site';

const props = defineProps({
    data: { type: Object, required: true },
});

const notices = computed(() => site.notices.slice(0, Number(props.data.limit) || 6));
</script>

<template>
    <ResultCard v-if="data.widget === 'result_form'" :data="data" />
    <StudentLoginCard v-else-if="data.widget === 'student_login'" :data="data" />
    <div v-else-if="data.widget === 'contact_info'" class="card">
        <h3 class="bold-title" style="font-size: 21px; margin-bottom: 10px">{{ data.heading || site.settings.site_name }}</h3>
        <div class="contact-line"><b>ঠিকানা</b><span>{{ site.settings.address }}</span></div>
        <div class="contact-line"><b>ফোন</b><a :href="`tel:${site.settings.phone}`">{{ site.settings.phone }}</a></div>
        <div class="contact-line"><b>ইমেইল</b><a :href="`mailto:${site.settings.email}`">{{ site.settings.email }}</a></div>
        <div class="contact-line" style="border-bottom: 0"><b>ইআইআইএন</b><span>{{ site.settings.eiin }}</span></div>
    </div>
    <div v-else-if="data.widget === 'recent_notices'">
        <h3 class="bold-title">{{ data.heading || site.settings.recent_notices_title }}</h3>
        <div class="notice-list">
            <NoticeRow v-for="notice in notices" :key="notice.id" :notice="notice" />
        </div>
    </div>
</template>
