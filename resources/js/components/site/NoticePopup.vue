<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { site } from '@/stores/site';
import { bnDate } from '@/utils/bn';

const emit = defineEmits(['toggle']);

const open = ref(false);
let timer = null;

// The layout keeps other floating cards out of the way while this covers the screen.
watch(open, (value) => emit('toggle', value));

/**
 * Closing only hides the popup for this page view — a reload brings it back.
 */
function close() {
    open.value = false;
}

function onKeydown(event) {
    if (event.key === 'Escape' && open.value) {
        close();
    }
}

onMounted(() => {
    window.addEventListener('keydown', onKeydown);

    if (site.settings.notice_popup_enabled === '1' && site.popupNotices.length) {
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
                    <div class="kicker">{{ site.settings.notice_popup_kicker }}</div>
                    <div id="popup-title" class="title">{{ site.settings.notice_popup_title }}</div>
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
