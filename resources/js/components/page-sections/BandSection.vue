<script setup>
import SmartLink from '@/components/site/SmartLink.vue';
import { safeUrl } from '@/utils/html';

defineProps({
    data: { type: Object, required: true },
});
</script>

<template>
    <div class="admission-band ps-band">
        <div class="ps-band-inner">
            <div>
                <h3 v-if="data.heading">{{ data.heading }}</h3>
                <p v-if="data.text" style="white-space: pre-line">{{ data.text }}</p>
            </div>
            <div v-if="(data.links || []).length" style="display: grid; gap: 10px">
                <template v-for="(link, index) in data.links" :key="index">
                    <SmartLink v-if="safeUrl(link.url)" :to="safeUrl(link.url)" :new-tab="/^https?:/i.test(link.url)" class="band-link">
                        {{ link.label }} <span>→</span>
                    </SmartLink>
                </template>
            </div>
        </div>
    </div>
</template>
