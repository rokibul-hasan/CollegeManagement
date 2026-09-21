<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { site } from '@/stores/site';
import { youtubeEmbed, youtubeId, youtubeThumbnail } from '@/utils/youtube';

defineProps({
    // True while the notice popup covers the screen; the card would sit behind it.
    hold: { type: Boolean, default: false },
});

const videoId = computed(() => (site.settings.video_popup_enabled === '1' ? youtubeId(site.settings.video_popup_url) : ''));
const title = computed(() => site.settings.video_popup_title || 'ভিডিও দেখুন');
const visible = ref(false);
const playing = ref(false);
let timer = null;

/**
 * Closing only hides the card for this page view — a reload brings it back.
 */
function dismiss() {
    visible.value = false;
}

function onKeydown(event) {
    if (event.key === 'Escape' && playing.value) {
        playing.value = false;
    }
}

onMounted(() => {
    window.addEventListener('keydown', onKeydown);

    if (videoId.value) {
        timer = setTimeout(() => {
            visible.value = true;
        }, 1500);
    }
});

onBeforeUnmount(() => {
    clearTimeout(timer);
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div v-if="visible && !hold && videoId && !playing" class="video-float">
        <button type="button" class="video-float-close" aria-label="ভিডিও বন্ধ করুন" @click="dismiss">✕</button>
        <button type="button" class="video-float-body" :aria-label="`${title} — চালু করুন`" @click="playing = true">
            <span class="video-float-title">{{ title }}</span>
            <span class="video-float-thumb">
                <img :src="youtubeThumbnail(videoId)" :alt="title" loading="lazy">
                <span class="video-play" aria-hidden="true">▶</span>
            </span>
        </button>
    </div>

    <div v-if="playing" class="modal-backdrop video-backdrop" @click.self="playing = false">
        <div class="video-modal" role="dialog" aria-modal="true" :aria-label="title">
            <div class="video-modal-head">
                <span>{{ title }}</span>
                <button type="button" class="modal-close" aria-label="বন্ধ করুন" @click="playing = false">✕</button>
            </div>
            <div class="video-frame">
                <iframe
                    :src="youtubeEmbed(videoId)"
                    :title="title"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    referrerpolicy="strict-origin-when-cross-origin"
                />
            </div>
        </div>
    </div>
</template>
