<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { site } from '@/stores/site';
import { bnDate } from '@/utils/bn';

const STORAGE_KEY = 'notice-popup-seen';
const open = ref(false);
let timer = null;

function alreadySeen() {
    try {
        return sessionStorage.getItem(STORAGE_KEY) === '1';
    } catch {
        return false;
    }
}

function close() {
    open.value = false;

    try {
        sessionStorage.setItem(STORAGE_KEY, '1');
    } catch {
        // Storage can be unavailable in private mode; the popup simply shows again.
    }
}

function onKeydown(event) {
    if (event.key === 'Escape' && open.value) {
        close();
    }
}

onMounted(() => {
    window.addEventListener('keydown', onKeydown);

    if (site.settings.notice_popup_enabled === '1' && site.popupNotices.length && !alreadySeen()) {
        timer = setTimeout(() => {
            open.value = true;
        }, 900);
    }
});

onBeforeUnmount(() => {
    clearTimeout(timer);
    window.removeEventListener('keydown', onKeydown);
});

defineExpose({ show: () => (open.value = true) });
</script>

<template>
    <div v-if="open" class="modal-backdrop" @click.self="close">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="popup-title">
            <div class="modal-head">
                <div>
                    <div class="kicker">জরুরি বিজ্ঞপ্তি</div>
                    <div id="popup-title" class="title">সাম্প্রতিক নোটিশ</div>
                </div>
                <span class="spacer" />
                <button type="button" class="modal-close" aria-label="বন্ধ করুন" @click="close">×</button>
            </div>
            <div class="modal-body">
                <router-link
                    v-for="notice in (site.popupNotices.length ? site.popupNotices : site.notices.slice(0, 4))"
                    :key="notice.id"
                    :to="`/notices/${notice.id}`"
                    class="modal-item"
                    @click="close"
                >
                    <span class="dot" />
                    <span style="min-width: 0">
                        <span class="t">{{ notice.title }}</span>
                        <span class="d">{{ bnDate(notice.published_on) }}</span>
                    </span>
                </router-link>
            </div>
            <div class="modal-foot">
                <router-link to="/notices" class="btn btn-primary" @click="close">সকল নোটিশ দেখুন</router-link>
                <button type="button" class="btn btn-outline" @click="close">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</template>
