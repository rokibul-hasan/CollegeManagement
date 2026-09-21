<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Full-size, clickable preview of the public site in a template, with device widths.
 */
const props = defineProps({
    template: { type: Object, required: true },
    version: { type: Number, default: 0 },
});

const emit = defineEmits(['close']);

const DEVICES = [
    ['desktop', 'ডেস্কটপ', '100%'],
    ['tablet', 'ট্যাবলেট', '820px'],
    ['mobile', 'মোবাইল', '390px'],
];

const device = ref('desktop');

function onKeydown(event) {
    if (event.key === 'Escape') {
        emit('close');
    }
}

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));

const frameWidth = () => DEVICES.find(([key]) => key === device.value)[2];
const src = `/?template=${props.template.value}&embed=1&v=${props.version}`;
</script>

<template>
    <div class="preview-backdrop" @click.self="emit('close')">
        <div class="preview-window" role="dialog" aria-modal="true" :aria-label="`${template.label} টেমপ্লেট প্রিভিউ`">
            <div class="preview-bar">
                <span class="template-swatch" :data-swatch="template.value"><i /><i /><i /></span>
                <b>{{ template.label }}</b>
                <small class="hide-sm">{{ template.description }}</small>
                <span class="spacer" />
                <div class="tabs">
                    <button
                        v-for="[key, label] in DEVICES"
                        :key="key"
                        type="button"
                        class="tab"
                        :class="{ 'is-active': device === key }"
                        @click="device = key"
                    >
                        {{ label }}
                    </button>
                </div>
                <slot name="actions" />
                <button type="button" class="btn-icon" aria-label="বন্ধ করুন" @click="emit('close')">✕</button>
            </div>
            <div class="preview-stage">
                <iframe :src="src" :title="`${template.label} টেমপ্লেট`" :style="{ width: frameWidth() }" />
            </div>
        </div>
    </div>
</template>
